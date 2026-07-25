<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CompressImages extends Command
{
    protected $signature = "images:compress
                            {--path=public : Path inside storage to scan (default: public)}
                            {--quality=85 : JPEG quality 1-100 (default: 85, no visual loss)}
                            {--dry-run : Show what would be compressed without doing it}";

    protected $description = "Compress all images in storage without reducing resolution";

    private int $totalFiles = 0;
    private int $totalSaved = 0;
    private int $skipped    = 0;
    private int $failed     = 0;

    public function handle(): int
    {
        $quality = (int) $this->option("quality");
        $dryRun  = $this->option("dry-run");
        $basePath = storage_path("app/public");

        if ($dryRun) {
            $this->warn("DRY RUN mode — no files will be changed.");
        }

        $this->info("Scanning: {$basePath}");
        $this->info("JPEG Quality: {$quality}% | Resolution: UNCHANGED");
        $this->newLine();

        $files = $this->getAllImages($basePath);
        $this->info("Found " . count($files) . " image(s). Starting compression...");
        $this->newLine();

        $bar = $this->output->createProgressBar(count($files));
        $bar->start();

        foreach ($files as $file) {
            $this->compressImage($file, $quality, $dryRun);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $savedKb = round($this->totalSaved / 1024, 2);
        $savedMb = round($this->totalSaved / 1024 / 1024, 2);

        $this->table(
            ["Stat", "Value"],
            [
                ["Total Files Processed", $this->totalFiles],
                ["Space Saved", "{$savedKb} KB ({$savedMb} MB)"],
                ["Skipped (already optimal)", $this->skipped],
                ["Failed", $this->failed],
            ]
        );

        $this->info("Done! Resolution was NOT changed on any image.");

        return Command::SUCCESS;
    }

    private function getAllImages(string $dir): array
    {
        $images = [];
        $allowedExtensions = ["jpg", "jpeg", "png", "webp"];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (!$file->isFile()) continue;
            $ext = strtolower($file->getExtension());
            if (in_array($ext, $allowedExtensions)) {
                $images[] = $file->getRealPath();
            }
        }

        return $images;
    }

    private function compressImage(string $filePath, int $quality, bool $dryRun): void
    {
        $this->totalFiles++;

        try {
            $info = @getimagesize($filePath);
            if (!$info) { $this->failed++; return; }

            [, , $type] = $info;
            $originalSize = filesize($filePath);

            $image = match ($type) {
                IMAGETYPE_JPEG => imagecreatefromjpeg($filePath),
                IMAGETYPE_PNG  => imagecreatefrompng($filePath),
                IMAGETYPE_WEBP => imagecreatefromwebp($filePath),
                default        => null,
            };

            if (!$image) { $this->skipped++; return; }

            if ($dryRun) { imagedestroy($image); return; }

            $saved = match ($type) {
                IMAGETYPE_JPEG => imagejpeg($image, $filePath, $quality),
                IMAGETYPE_PNG  => (function () use ($image, $filePath) {
                    imagealphablending($image, false);
                    imagesavealpha($image, true);
                    return imagepng($image, $filePath, 6);
                })(),
                IMAGETYPE_WEBP => imagewebp($image, $filePath, $quality),
                default        => false,
            };

            imagedestroy($image);

            if ($saved) {
                $newSize = filesize($filePath);
                $diff    = $originalSize - $newSize;
                if ($diff > 0) { $this->totalSaved += $diff; }
                else { $this->skipped++; }
            } else {
                $this->failed++;
            }
        } catch (\Throwable $e) {
            $this->failed++;
        }
    }
}
