<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetVersion extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'asset_id',
        'version_number',
        'master_zip_path',
        'viewer_glb_path',
        'thumbnail_path',
        'file_size',
        'polygon_count',
        'vertex_count',
        'status',
        'error_log',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'polygon_count' => 'integer',
        'vertex_count' => 'integer',
        'version_number' => 'integer',
        'created_at' => 'datetime',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }
}