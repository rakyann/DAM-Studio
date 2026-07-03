<?php

namespace App\Jobs;

use App\Enums\AssetStatus;
use App\Models\Asset;
use App\Services\ConversionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ConvertAssetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Retry maksimal 3x kalau gagal
    public int $tries = 3;

    // Timeout 10 menit — konversi bisa lama
    public int $timeout = 600;

    public function __construct(
        public Asset $asset,
        public string $tempFilePath,
    ) {}

    public function handle(ConversionService $conversionService): void
    {
        Log::info('Starting conversion job', ['asset_id' => $this->asset->id]);

        // Update status jadi processing
        $this->asset->update(['status' => AssetStatus::PROCESSING->value]);

        // Path S3 untuk asset ini
        $s3BasePath = "assets/{$this->asset->slug}/v{$this->asset->version}";

        try {
            $result = $conversionService->convert(
                localFilePath: $this->tempFilePath,
                extension: $this->asset->original_extension,
                s3BasePath: $s3BasePath,
            );

            // Update asset dengan hasil konversi
            $this->asset->update([
                'viewer_glb_path'     => $result['converted_file_path'],
                'thumbnail_path'      => $result['thumbnail_path'],
                'file_size'           => $result['file_size'],
                'status'              => AssetStatus::COMPLETED->value,
            ]);

            Log::info('Conversion completed', ['asset_id' => $this->asset->id]);

        } catch (Throwable $e) {
            Log::error('Conversion failed', [
                'asset_id' => $this->asset->id,
                'error'    => $e->getMessage(),
            ]);

            $this->asset->update(['status' => AssetStatus::FAILED->value]);

            // Re-throw agar queue tahu job ini gagal
            throw $e;
        }
    }

    public function failed(Throwable $e): void
    {
        Log::error('Job permanently failed', [
            'asset_id' => $this->asset->id,
            'error'    => $e->getMessage(),
        ]);

        $this->asset->update(['status' => AssetStatus::FAILED->value]);
    }
}