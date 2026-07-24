<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Traits\mediaUploader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class ProductImageUploadController extends Controller
{
    use mediaUploader;

    public function linkImagesFromFolder(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'folder' => 'sometimes|string|max:255', // Optional: folder path in public directory
                'replace' => 'sometimes|boolean' // Optional: replace existing images
            ]);

            // Default folder is 'product-images' in public
            $folderName = $request->input('folder', 'product-images');
            $replace = $request->input('replace', true);

            // Full path to the folder
            $folderPath = public_path($folderName);

            // Check if folder exists
            if (!File::isDirectory($folderPath)) {
                return response()->json([
                    'success' => false,
                    'message' => "Folder not found: {$folderName}. Please create the folder in public directory."
                ], 404);
            }

            // Get all image files from the folder
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            $imageFiles = File::files($folderPath);

            // Filter only image files
            $images = array_filter($imageFiles, function($file) use ($allowedExtensions) {
                $extension = strtolower($file->getExtension());
                return in_array($extension, $allowedExtensions);
            });

            if (empty($images)) {
                return response()->json([
                    'success' => false,
                    'message' => "No image files found in folder: {$folderName}"
                ], 404);
            }

            $results = [];
            $errors = [];
            $notFound = [];

            foreach ($images as $imageFile) {
                try {
                    // Get image name without extension
                    $imageName = pathinfo($imageFile->getFilename(), PATHINFO_FILENAME);

                    // Try to find product by English name (exact match)
                    $product = Product::where('name->en', $imageName)->first();

                    if (!$product) {
                        // Try case-insensitive match
                        $product = Product::whereRaw("LOWER(JSON_EXTRACT(name, '$.en')) = LOWER(?)", [$imageName])->first();
                    }

                    if (!$product) {
                        $notFound[] = [
                            'image_name' => $imageFile->getFilename(),
                            'searched_name' => $imageName
                        ];
                        continue;
                    }

                    // Get product name for logging
                    $productNameArray = $product->name;
                    if (is_string($productNameArray)) {
                        $productNameArray = json_decode($productNameArray, true) ?? ['en' => $productNameArray];
                    }
                    $displayName = $productNameArray['en'] ?? $imageName;

                    // Upload image using the same method as product page
                    $this->handleImage($product, $imageFile->getPathname(), $replace, 'products');

                    $results[] = [
                        'image_name' => $imageFile->getFilename(),
                        'product_id' => $product->id,
                        'product_name' => $displayName,
                        'image_url' => $product->getFirstMediaUrl('products'),
                        'status' => 'success'
                    ];

                    Log::info('Product image linked from folder', [
                        'product_id' => $product->id,
                        'product_name' => $displayName,
                        'image_name' => $imageFile->getFilename(),
                        'folder' => $folderName
                    ]);

                } catch (\Exception $e) {
                    $errors[] = [
                        'image_name' => $imageFile->getFilename(),
                        'error' => $e->getMessage()
                    ];

                    Log::error('Error linking product image from folder', [
                        'image_name' => $imageFile->getFilename(),
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return response()->json([
                'success' => count($results) > 0,
                'message' => "Linked " . count($results) . " images successfully" .
                            (count($errors) > 0 ? ", " . count($errors) . " errors" : "") .
                            (count($notFound) > 0 ? ", " . count($notFound) . " products not found" : ""),
                'folder' => $folderName,
                'results' => $results,
                'errors' => $errors,
                'not_found' => $notFound,
                'summary' => [
                    'total_images' => count($images),
                    'successful' => count($results),
                    'errors' => count($errors),
                    'products_not_found' => count($notFound)
                ]
            ], count($results) > 0 ? 200 : 500);

        } catch (\Exception $e) {
            Log::error('Error linking images from folder', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error linking images: ' . $e->getMessage()
            ], 500);
        }
    }
}
