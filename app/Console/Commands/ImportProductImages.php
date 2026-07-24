<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Models\Product;
use App\Traits\mediaUploader;

class ImportProductImages extends Command
{
    use mediaUploader;

    protected $signature = 'import:product-images {folder}';
    protected $description = 'Import all product images from a folder and link to products';

    public function handle()
    {
        $folderPath = $this->argument('folder');

        if (!File::exists($folderPath)) {
            $this->error("Folder does not exist: $folderPath");
            return 1;
        }

        $images = File::files($folderPath);

        foreach ($images as $image) {
            $nameWithoutExt = pathinfo($image->getFilename(), PATHINFO_FILENAME);

            $product = Product::where('name->en', $nameWithoutExt)->first();

            if ($product) {
                $this->handleImage($product, $image->getPathname(), true, 'products');

                $productName = $product->name;
                if (is_string($productName)) {
                    $productName = json_decode($productName, true) ?? ['en' => $productName];
                }

                $this->info("Image uploaded for product: {$productName['en']}");
            } else {
                $this->warn("No product found for image: {$image->getFilename()}");
            }
        }

        $this->info("Import completed!");
        return 0;
    }
}
