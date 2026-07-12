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
        $query = Auth::user()->isAdmin() 
            ? Asset::latest() 
            : Asset::where('user_id', Auth::id())->latest();

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
        $featuredAssets = Auth::user()->isAdmin()
            ? Asset::orderByDesc('is_staff_pick')->latest()->take(3)->get()
            : Asset::where('user_id', Auth::id())->orderByDesc('is_staff_pick')->latest()->take(3)->get();

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
                'title' => 'required|string|max:255',
                'category' => 'nullable|string',
                'version' => 'required|integer|min:1',
                'tags' => 'nullable|string',
                'file' => 'required|file|max:512000', // 500MB max
                'thumbnail' => 'nullable|image|max:10240', // Max 10MB
            ]);

            $file = $request->file('file');
            $ext = strtolower($file->getClientOriginalExtension());
            $validExt = ['blend', 'fbx', 'obj'];

            if (!in_array($ext, $validExt)) {
                return back()->withInput()->with('error', 'Hanya file .blend, .fbx, atau .obj yang diizinkan!');
            }

            // Simpan dengan extension asli agar Blender bisa mengenali format file
            $path = $file->storeAs('secure_assets', \Illuminate\Support\Str::random(40) . '.' . $ext);

            $thumbnailPath = null;
            if ($request->hasFile('thumbnail')) {
                $thumbnailPath = $request->file('thumbnail')->store('public/thumbnails');
                // Remove 'public/' prefix for easy asset() rendering later
                $thumbnailPath = str_replace('public/', '', $thumbnailPath);
            }

            $asset = Asset::create([
                'user_id' => Auth::id(),
                'title' => $request->title,
                'category' => $request->category ?? 'other',
                'original_extension' => $ext,
                'original_file_path' => $path,
                'master_zip_path' => $path, // sementara sama
                'version' => $request->version,
                'status' => \App\Enums\AssetStatus::QUEUED,
                'visibility' => \App\Enums\AssetVisibility::PUBLIC,
                'thumbnail_path' => $thumbnailPath,
            ]);
            
            $tags = $request->tags
                ? array_map('trim', explode(',', $request->tags))
                : [];

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

            // Dispatch async — biarkan queue worker yang menjalankan konversi
            ConvertAssetJob::dispatch($asset);

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
