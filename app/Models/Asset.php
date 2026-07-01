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
        'name',
        'slug',
        'category',
        'original_extension',
        'original_file_path',
        'converted_file_path',
        'thumbnail_path',
        'version',
        'file_size',
        'poly_count',
        'tags',
        'status',
    ];

    protected $casts = [
        'tags' => 'array',
        'file_size' => 'integer',
        'poly_count' => 'integer',
        'version' => 'integer',
    ];

    // Auto-generate slug dari name
    protected static function booted(): void
    {
        static::creating(function (Asset $asset) {
            $asset->slug = Str::slug($asset->name) . '-' . Str::random(6);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(AssetVersion::class);
    }

    // Helper: cek apakah asset sudah selesai dikonversi
    public function isReady(): bool
    {
        return $this->status === 'done';
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