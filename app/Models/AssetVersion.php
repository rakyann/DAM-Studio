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
        'file_path',
        'file_size',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'version_number' => 'integer',
        'created_at' => 'datetime',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }
}