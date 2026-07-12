<?php

use App\Http\Controllers\AssetController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AssetController::class, 'guestIndex'])->name('landing');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route('assets.index');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('assets', AssetController::class)
        ->only(['index', 'create', 'store', 'destroy']);

    // Admin Routes
    Route::middleware([\App\Http\Middleware\IsAdmin::class])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [\App\Http\Controllers\AdminController::class, 'dashboard'])->name('dashboard');
        Route::patch('/assets/{asset}/toggle-visibility', [\App\Http\Controllers\AdminController::class, 'toggleVisibility'])->name('assets.toggle-visibility');
        Route::delete('/assets/{asset}', [\App\Http\Controllers\AdminController::class, 'destroyAsset'])->name('assets.destroy');
    });
});

// Accessible by anyone (or maybe just auth? Tags might be public, but usually public is fine)
Route::get('api/tags', [AssetController::class, 'searchTags'])->name('api.tags');

// Guest can view public assets, but we must enforce visibility in controller
Route::get('assets/{asset}', [AssetController::class, 'show'])->name('assets.show');
Route::get('assets/{asset}/download', [AssetController::class, 'download'])->name('assets.download');

require __DIR__.'/auth.php';