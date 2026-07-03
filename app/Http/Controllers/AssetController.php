<?php

namespace App\Http\Controllers;

use App\Enums\AssetStatus;
use App\Jobs\ConvertAssetJob;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AssetController extends Controller
{
    public function index()
    {
        $assets = Asset::where('user_id', Auth::id())
            ->latest()
            ->paginate(12);

        return view('assets.index', compact('assets'));
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
        
        // Simpan file original secara permanen di disk public
        $originalPath = $file->store('assets/originals', 'public');
        
        // Buat temporary copy untuk proses konversi karena job/service akan mengubah nama dan menghapusnya
        $tempPath     = 'temp/' . basename($originalPath);
        Storage::disk('local')->put($tempPath, Storage::disk('public')->readStream($originalPath));
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
        if (!$asset->original_file_path || !Storage::disk('public')->exists($asset->original_file_path)) {
            abort(404, 'File original tidak ditemukan.');
        }

        $filename = "{$asset->title}.{$asset->original_extension}";

        return Storage::disk('public')->download($asset->original_file_path, $filename);
    }

    public function destroy(Asset $asset)
    {
        if ($asset->original_file_path) {
            Storage::disk('public')->delete($asset->original_file_path);
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