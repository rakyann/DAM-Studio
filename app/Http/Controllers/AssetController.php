<?php

namespace App\Http\Controllers;

use App\Enums\AssetStatus;
use App\Enums\AssetVisibility;
use App\Jobs\ConvertAssetJob;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AssetController extends Controller
{
    public function guestIndex(Request $request)
    {
        $query = Asset::public()->completed()->latest();

        // If tag is selected, filter by tag
        if ($request->has('tag') && !empty($request->tag)) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('name', $request->tag)->orWhere('slug', $request->tag);
            });
        }

        $assets = $query->paginate(12);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('partials.discovery_grid', compact('assets'))->render()
            ]);
        }

        $featuredAsset = Asset::public()->completed()->latest()->staffPicked()->first();
        if (!$featuredAsset) {
            $featuredAsset = Asset::public()->completed()->latest()->first();
        }

        $popularTags = \App\Models\Tag::withCount('assets')
            ->orderByDesc('assets_count')
            ->limit(10)
            ->get();

        return view('landing', compact('assets', 'featuredAsset', 'popularTags'));
    }

    public function index(Request $request)
    {
        $query = Asset::where('user_id', Auth::id())->latest();

        if ($request->has('category') && !empty($request->category)) {
            $query->where('category', $request->category);
        }

        $assets = $query->paginate(12);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('assets.partials.grid', compact('assets'))->render(),
                'next_page' => $assets->nextPageUrl()
            ]);
        }

        // Fetch top 3 featured assets (is_staff_pick first, then fallback to newest)
        $featuredAssets = Asset::where('user_id', Auth::id())
            ->orderByDesc('is_staff_pick')
            ->latest()
            ->take(3)
            ->get();

        return view('assets.index', compact('assets', 'featuredAssets'));
    }

    public function searchTags(Request $request)
    {
        $query = $request->get('q', '');
        $tags = \App\Models\Tag::where('name', 'like', "%{$query}%")
            ->limit(10)
            ->pluck('name');
        return response()->json($tags);
    }

    public function create()
    {
        return view('assets.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'     => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'tags'     => 'nullable|string',
            'version'  => 'required|integer|min:1',
            'file'     => 'required|file|extensions:blend,fbx,obj|max:512000',
        ]);

        $file         = $request->file('file');
        $extension    = strtolower($file->getClientOriginalExtension());
        
        // Simpan file original secara permanen di disk local (secure_assets) sesuai PRD
        $originalPath = $file->store('secure_assets', 'local');
        
        // Buat temporary copy untuk proses konversi karena job/service akan mengubah nama dan menghapusnya
        $tempPath     = 'temp/' . basename($originalPath);
        Storage::disk('local')->put($tempPath, Storage::disk('local')->readStream($originalPath));
        $fullTempPath = Storage::disk('local')->path($tempPath);

        $tags = $request->tags
            ? array_map('trim', explode(',', $request->tags))
            : [];

        $asset = Asset::create([
            'user_id'            => Auth::id(),
            'title'              => $request->title,
            'category'           => $request->category,
            'version'            => $request->version,
            'original_extension' => $extension,
            'original_file_path' => $originalPath,
            'master_zip_path'    => $originalPath,
            'status'             => AssetStatus::QUEUED->value,
        ]);

        if (!empty($tags)) {
            $tagIds = [];
            foreach ($tags as $tagName) {
                if (empty($tagName)) continue;
                $slug = \Illuminate\Support\Str::slug($tagName);
                $tag = \App\Models\Tag::firstOrCreate(
                    ['slug' => $slug],
                    ['name' => $tagName]
                );
                $tagIds[] = $tag->id;
            }
            $asset->tags()->sync($tagIds);
        }

        ConvertAssetJob::dispatchSync($asset, $fullTempPath);

        return redirect()->route('assets.show', $asset)
            ->with('success', 'Asset berhasil diupload dan sedang diproses!');
    }

    public function show(Asset $asset)
    {
        // Enforce visibility
        if ($asset->visibility === AssetVisibility::PRIVATE && $asset->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to private asset.');
        }

        $viewerUrl    = null;
        $thumbnailUrl = null;

        if ($asset->isReady()) {
            $viewerUrl    = asset('storage/' . $asset->viewer_glb_path);
            $thumbnailUrl = asset('storage/' . $asset->thumbnail_path);
        }

        return view('assets.show', compact('asset', 'viewerUrl', 'thumbnailUrl'));
    }

    public function download(Asset $asset)
    {
        // Enforce visibility (Guests should not be able to download at all)
        if (!Auth::check()) {
            abort(403, 'Anda harus login untuk mengunduh asset ini.');
        }

        if ($asset->visibility === AssetVisibility::PRIVATE && $asset->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to private asset.');
        }

        // Backward compatibility: use original_file_path if master_zip_path is empty
        $filePath = $asset->master_zip_path ?: $asset->original_file_path;

        // Check which disk contains the file (local for new secure_assets, public for older assets)
        $disk = 'local';
        if (!Storage::disk('local')->exists($filePath)) {
            if (Storage::disk('public')->exists($filePath)) {
                $disk = 'public';
            } else {
                abort(404, 'File original tidak ditemukan.');
            }
        }

        $filename = "{$asset->title}.{$asset->original_extension}";

        \Illuminate\Support\Facades\Log::info('Downloading asset', [
            'asset_id' => $asset->id,
            'disk' => $disk,
            'path' => $filePath,
        ]);

        return Storage::disk($disk)->download($filePath, $filename);
    }

    public function destroy(Asset $asset)
    {
        if ($asset->master_zip_path) {
            Storage::disk('local')->exists($asset->master_zip_path) 
                ? Storage::disk('local')->delete($asset->master_zip_path)
                : Storage::disk('public')->delete($asset->master_zip_path);
        }
        if ($asset->original_file_path && $asset->original_file_path !== $asset->master_zip_path) {
            Storage::disk('local')->exists($asset->original_file_path)
                ? Storage::disk('local')->delete($asset->original_file_path)
                : Storage::disk('public')->delete($asset->original_file_path);
        }
        if ($asset->viewer_glb_path) {
            Storage::disk('public')->delete($asset->viewer_glb_path);
        }
        if ($asset->thumbnail_path) {
            Storage::disk('public')->delete($asset->thumbnail_path);
        }

        $asset->delete();

        return redirect()->route('assets.index')
            ->with('success', 'Asset berhasil dihapus.');
    }
}
