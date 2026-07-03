<?php

namespace App\Models;

use App\Enums\AssetStatus;
use App\Enums\AssetVisibility;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Asset extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'category',
        'original_extension',
        'original_file_path',
        'master_zip_path',
        'viewer_glb_path',
        'thumbnail_path',
        'version',
        'file_size',
        'polygon_count',
        'vertex_count',
        'status',
        'visibility',
        'is_staff_pick',
        'error_log',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'polygon_count' => 'integer',
        'vertex_count' => 'integer',
        'version' => 'integer',
        'status' => AssetStatus::class,
        'visibility' => AssetVisibility::class,
    ];

    // Auto-generate slug dari name
    protected static function booted(): void
    {
        static::creating(function (Asset $asset) {
            $asset->slug = Str::slug($asset->title) . '-' . Str::random(6);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(AssetVersion::class);
    }

    // Scopes
    public function scopePublic(Builder $query): void
    {
        $query->where('visibility', AssetVisibility::PUBLIC->value);
    }

    public function scopeStaffPicked(Builder $query): void
    {
        $query->where('is_staff_pick', true);
    }

    // Sync from latest completed version
    public function syncFromLatestVersion(): void
    {
        $latest = $this->versions()->where('status', AssetStatus::COMPLETED->value)->orderByDesc('version_number')->first();
        if ($latest) {
            $this->update([
                'master_zip_path' => $latest->master_zip_path,
                'viewer_glb_path' => $latest->viewer_glb_path,
                'thumbnail_path' => $latest->thumbnail_path,
                'polygon_count' => $latest->polygon_count,
                'vertex_count' => $latest->vertex_count,
                'file_size' => $latest->file_size,
            ]);
        }
    }

    // Helper: cek apakah asset sudah selesai dikonversi
    public function isReady(): bool
    {
        return $this->status === AssetStatus::COMPLETED;
    }

    // Helper: format file size ke KB/MB
    public function formattedFileSize(): string
    {
        if ($this->file_size < 1024 * 1024) {
            return round($this->file_size / 1024, 1) . ' KB';
        }
        return round($this->file_size / (1024 * 1024), 2) . ' MB';
    }
}