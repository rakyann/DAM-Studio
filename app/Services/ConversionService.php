<?php

namespace App\Services;

use App\Enums\AssetStatus;
use App\Models\Asset;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ConversionService
{
    protected string $blenderPath;

    public function __construct()
    {
        $this->blenderPath = config('app.blender_path', '/usr/bin/blender');
    }

    public function convert(int $assetId, string $s3BasePath): array
    {
        $this->ensureBlenderExists();

        $asset = Asset::findOrFail($assetId);
        
        try {
            $asset->update(['status' => AssetStatus::PROCESSING]);

            $localFilePath = Storage::disk('local')->path($asset->original_file_path);
            $extension = $asset->original_extension;
            
            $tempGlb = storage_path('app/private/temp/' . Str::random(40) . '.glb');
            $tempThumb = storage_path('app/private/temp/' . Str::random(40) . '.jpg');

            $this->runBlenderConversion($localFilePath, $extension, $tempGlb);
            
            $glbPath   = $s3BasePath . '/preview.glb';
            Storage::disk('public')->put($glbPath, file_get_contents($tempGlb));
            
            $result = [
                'converted_file_path' => $glbPath,
                'file_size'           => filesize($tempGlb),
            ];

            // Generate thumbnail only if user didn't upload a custom one
            if (empty($asset->thumbnail_path)) {
                $this->generateThumbnail($tempGlb, $tempThumb);
                $thumbPath = $s3BasePath . '/thumbnail.jpg';
                Storage::disk('public')->put($thumbPath, file_get_contents($tempThumb));
                $result['thumbnail_path'] = $thumbPath;
            }

            return $result;

        } finally {
            $this->cleanup($localFilePath, $tempGlb, $tempThumb);
        }
    }

    protected function runBlenderConversion(string $inputPath, string $extension, string $outputPath): void
    {
        $scriptPath = base_path('scripts/blender_convert.py');

        $command = sprintf(
            '"%s" --background --python "%s" -- --input "%s" --output "%s" 2>&1',
            $this->blenderPath,
            $scriptPath,
            $inputPath,
            $outputPath
        );

        Log::info('Running Blender conversion', ['command' => $command]);

        $output     = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        if ($returnCode !== 0 || !file_exists($outputPath)) {
            Log::error('Blender conversion failed', [
                'return_code' => $returnCode,
                'output'      => implode("\n", $output),
            ]);
            throw new RuntimeException('Blender conversion failed: ' . implode("\n", $output));
        }

        Log::info('Blender conversion success', ['output' => $outputPath]);
    }

    protected function generateThumbnail(string $glbPath, string $outputPath): void
    {
        $scriptPath = base_path('scripts/blender_thumbnail.py');

        $command = sprintf(
            '"%s" --background --python "%s" -- --input "%s" --output "%s" 2>&1',
            $this->blenderPath,
            $scriptPath,
            $glbPath,
            $outputPath
        );

        $output     = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        if ($returnCode !== 0 || !file_exists($outputPath)) {
            Log::warning('Thumbnail generation failed, using placeholder', [
                'output' => implode("\n", $output),
            ]);
            $this->copyPlaceholderThumbnail($outputPath);
        }
    }

    protected function copyPlaceholderThumbnail(string $outputPath): void
    {
        $placeholder = public_path('images/placeholder-3d.jpg');
        if (file_exists($placeholder)) {
            copy($placeholder, $outputPath);
        } else {
            $img = imagecreatetruecolor(400, 300);
            $bg  = imagecolorallocate($img, 30, 30, 30);
            imagefill($img, 0, 0, $bg);
            imagejpeg($img, $outputPath, 85);
            imagedestroy($img);
        }
    }

    protected function ensureBlenderExists(): void
    {
        if (!file_exists($this->blenderPath)) {
            throw new RuntimeException("Blender not found at: {$this->blenderPath}");
        }
    }

    protected function cleanup(string ...$paths): void
    {
        foreach ($paths as $path) {
            if ($path && file_exists($path)) {
                unlink($path);
                Log::info('Cleaned up temp file', ['path' => $path]);
            }
        }
    }
}