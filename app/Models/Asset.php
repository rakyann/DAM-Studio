<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(AssetVersion::class);
    }

    // Helper: cek apakah asset sudah selesai dikonversi
    public function isReady(): bool
    {
        return $this->status === 'completed';
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