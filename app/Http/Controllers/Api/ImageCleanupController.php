<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ImageCleanupController extends Controller
{
    /**
     * Delete all images except categories and countries/locations
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cleanup(Request $request)
    {
        try {
            $deletedCount = 0;
            $deletedFiles = [];
            $errors = [];

            // Get all media records
            $allMedia = Media::all();

            // Get IDs of categories and locations to keep
            $categoryIds = Category::pluck('id')->toArray();
            $locationIds = Location::pluck('id')->toArray();

            foreach ($allMedia as $media) {
                $shouldKeep = false;

                // Keep if it's a category image
                if ($media->model_type === Category::class && in_array($media->model_id, $categoryIds)) {
                    if ($media->collection_name === 'categories') {
                        $shouldKeep = true;
                    }
                }

                // Keep if it's a location/country image
                if ($media->model_type === Location::class && in_array($media->model_id, $locationIds)) {
                    $shouldKeep = true;
                }

                // If we should not keep this media, delete it
                if (!$shouldKeep) {
                    try {
                        // Use Spatie's delete method which handles all files and conversions
                        $media->delete();
                        $deletedCount++;
                        $deletedFiles[] = "Media ID: {$media->id}";

                    } catch (\Exception $e) {
                        $errors[] = "Error deleting media ID {$media->id}: " . $e->getMessage();
                    }
                }
            }

            // Also clean up orphaned files in storage/app/public and public/storage
            $this->cleanupOrphanedFiles($categoryIds, $locationIds, $deletedFiles, $errors);

            return response()->json([
                'success' => true,
                'message' => "Cleanup completed successfully",
                'deleted_count' => $deletedCount,
                'errors' => $errors,
                'errors_count' => count($errors)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error during cleanup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clean up orphaned files in storage directories
     */
    private function cleanupOrphanedFiles($categoryIds, $locationIds, &$deletedFiles, &$errors)
    {
        try {
            // Get all media IDs that should be kept
            $keptMediaIds = Media::where(function($query) use ($categoryIds, $locationIds) {
                $query->where(function($q) use ($categoryIds) {
                    $q->where('model_type', Category::class)
                      ->whereIn('model_id', $categoryIds)
                      ->where('collection_name', 'categories');
                })->orWhere(function($q) use ($locationIds) {
                    $q->where('model_type', Location::class)
                      ->whereIn('model_id', $locationIds);
                });
            })->pluck('id')->toArray();

            // Clean storage/app/public
            $storagePath = storage_path('app/public');
            if (File::isDirectory($storagePath)) {
                $this->deleteOrphanedDirectories($storagePath, $keptMediaIds, $deletedFiles, $errors);
            }

            // Clean public/storage (symlink - same files, but check both to be safe)
            $publicStoragePath = public_path('storage');
            if (File::isDirectory($publicStoragePath) && !is_link($publicStoragePath)) {
                // Only clean if it's not a symlink (in case symlink is broken)
                $this->deleteOrphanedDirectories($publicStoragePath, $keptMediaIds, $deletedFiles, $errors);
            }

        } catch (\Exception $e) {
            $errors[] = "Error cleaning orphaned files: " . $e->getMessage();
        }
    }

    /**
     * Delete orphaned directories
     */
    private function deleteOrphanedDirectories($basePath, $keptMediaIds, &$deletedFiles, &$errors)
    {
        if (!File::isDirectory($basePath)) {
            return;
        }

        $directories = File::directories($basePath);

        foreach ($directories as $dir) {
            $dirName = basename($dir);

            // Check if directory name is a numeric ID (Spatie Media Library uses media ID as folder name)
            if (is_numeric($dirName)) {
                $mediaId = (int) $dirName;

                // If this media ID is not in the kept list, delete it
                if (!in_array($mediaId, $keptMediaIds)) {
                    try {
                        File::deleteDirectory($dir);
                        $deletedFiles[] = $dir;
                    } catch (\Exception $e) {
                        $errors[] = "Error deleting directory {$dir}: " . $e->getMessage();
                    }
                }
            }
        }
    }
}
