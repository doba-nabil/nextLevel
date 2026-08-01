<?php

namespace App\Listeners;

use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAdded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class CompressUploadedImage
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(MediaHasBeenAdded $event): void
    {
        try {
            $media = $event->media;
            $filePath = $media->getPath();

            if (!file_exists($filePath)) {
                return;
            }

            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($extension, $allowedExtensions)) {
                return;
            }

            $type = exif_imagetype($filePath);
            $image = match ($type) {
                IMAGETYPE_JPEG => @imagecreatefromjpeg($filePath),
                IMAGETYPE_PNG  => @imagecreatefrompng($filePath),
                IMAGETYPE_WEBP => @imagecreatefromwebp($filePath),
                default        => null,
            };

            if (!$image) {
                return;
            }

            $quality = 75; // Same quality as the compress command

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
            
            // Update the file size in the media table since we modified it directly
            if ($saved && file_exists($filePath)) {
                $media->size = filesize($filePath);
                $media->saveQuietly(); // saveQuietly to avoid triggering events again
            }

        } catch (\Exception $e) {
            Log::error('Image Compression Listener Error: ' . $e->getMessage());
        }
    }
}
