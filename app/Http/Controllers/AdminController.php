<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\User;
use App\Enums\AssetStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        $totalUsers = User::count();
        $totalAssets = Asset::count();
        $failedConversions = Asset::where('status', AssetStatus::FAILED->value)->count();

        $query = Asset::with('user')->latest();
        
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        $assets = $query->paginate(15);

        return view('admin.dashboard', compact('totalUsers', 'totalAssets', 'failedConversions', 'assets'));
    }

    public function destroyAsset(Asset $asset)
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

        return redirect()->route('admin.dashboard')
            ->with('success', 'Asset successfully deleted by Admin.');
    }

    public function toggleVisibility(Asset $asset)
    {
        $newVisibility = $asset->visibility->value === \App\Enums\AssetVisibility::PUBLIC->value 
            ? \App\Enums\AssetVisibility::PRIVATE->value 
            : \App\Enums\AssetVisibility::PUBLIC->value;

        $asset->update(['visibility' => $newVisibility]);

        $status = $newVisibility === \App\Enums\AssetVisibility::PUBLIC->value ? 'Public (Ditampilkan di Landing Page)' : 'Private (Disembunyikan)';
        
        return back()->with('success', "Visibility aset '{$asset->title}' berhasil diubah menjadi {$status}.");
    }
}
