<?php

namespace App\Console\Commands;

use App\Models\Location;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception;

class ImportCitiesFromExcel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:cities';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import cities from public/cities.xlsx file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = public_path('cities.xlsx');

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        $this->info("Reading file: {$filePath}");

        try {
            $reader = IOFactory::createReader('Xlsx');
            $spreadsheet = $reader->load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Get header row
            $headerRow = array_shift($rows);
            
            // Find column indices
            $arabicCityNameIndex = $this->findColumnIndex($headerRow, ['arabic city name', 'arabic_city_name', 'city_ar', 'city name ar']);
            $englishCityNameIndex = $this->findColumnIndex($headerRow, ['english city name', 'english_city_name', 'city_en', 'city name en']);
            $deliveryIndex = $this->findColumnIndex($headerRow, ['delivery', 'delivery_fee', 'shipping']);
            $governorateIndex = $this->findColumnIndex($headerRow, ['governorate', 'state', 'parent']);

            if ($arabicCityNameIndex === false || $englishCityNameIndex === false) {
                $this->error("Required columns not found. Found columns: " . implode(', ', $headerRow));
                return 1;
            }

            $this->info("Found columns:");
            $this->info("  - Arabic City Name: Column " . ($arabicCityNameIndex + 1));
            $this->info("  - English City Name: Column " . ($englishCityNameIndex + 1));
            $this->info("  - Delivery: " . ($deliveryIndex !== false ? "Column " . ($deliveryIndex + 1) : "Not found"));
            $this->info("  - Governorate: " . ($governorateIndex !== false ? "Column " . ($governorateIndex + 1) : "Not found"));

            $imported = 0;
            $skipped = 0;
            $errors = [];

            foreach ($rows as $rowIndex => $row) {
                $rowNumber = $rowIndex + 2; // +2 because we removed header and Excel is 1-indexed

                // Skip empty rows
                if (empty($row[$arabicCityNameIndex]) && empty($row[$englishCityNameIndex])) {
                    continue;
                }

                $arabicName = trim($row[$arabicCityNameIndex] ?? '');
                $englishName = trim($row[$englishCityNameIndex] ?? '');

                if (empty($arabicName) && empty($englishName)) {
                    $skipped++;
                    continue;
                }

                // Get delivery fee (if available)
                $deliveryFee = null;
                if ($deliveryIndex !== false && isset($row[$deliveryIndex])) {
                    $deliveryValue = trim($row[$deliveryIndex] ?? '');
                    if (!empty($deliveryValue)) {
                        // Try to parse as number
                        $deliveryFee = is_numeric($deliveryValue) ? (float)$deliveryValue : null;
                    }
                }

                // If delivery fee is a single value, use it for both near and far
                $shippingFeeNear = $deliveryFee ?? 0;
                $shippingFeeFar = $deliveryFee ?? 0;

                // Get parent_id from Governorate
                $parentId = null;
                if ($governorateIndex !== false && isset($row[$governorateIndex])) {
                    $governorateName = trim($row[$governorateIndex] ?? '');
                    if (!empty($governorateName)) {
                        // Try to find location by name (check both ar and en)
                        $parent = Location::where('type', 'state')
                            ->where(function($query) use ($governorateName) {
                                $query->whereRaw("JSON_EXTRACT(name, '$.ar') = ?", [$governorateName])
                                      ->orWhereRaw("JSON_EXTRACT(name, '$.en') = ?", [$governorateName]);
                            })
                            ->first();

                        if ($parent) {
                            $parentId = $parent->id;
                        } else {
                            $errors[] = "Row {$rowNumber}: Governorate '{$governorateName}' not found. City will be created without parent.";
                        }
                    }
                }

                try {
                    // Check if city already exists
                    $existingCity = Location::where('type', 'city')
                        ->where(function($query) use ($arabicName, $englishName) {
                            if (!empty($arabicName)) {
                                $query->whereRaw("JSON_EXTRACT(name, '$.ar') = ?", [$arabicName]);
                            }
                            if (!empty($englishName)) {
                                $query->orWhereRaw("JSON_EXTRACT(name, '$.en') = ?", [$englishName]);
                            }
                        })
                        ->first();

                    if ($existingCity) {
                        // Update existing city
                        $existingCity->update([
                            'name' => [
                                'ar' => $arabicName ?: $existingCity->getTranslation('name', 'ar'),
                                'en' => $englishName ?: $existingCity->getTranslation('name', 'en'),
                            ],
                            'shipping_fee_near' => $shippingFeeNear,
                            'shipping_fee_far' => $shippingFeeFar,
                            'parent_id' => $parentId ?? $existingCity->parent_id,
                        ]);
                        $this->line("Updated: {$englishName} ({$arabicName})");
                    } else {
                        // Create new city
                        Location::create([
                            'name' => [
                                'ar' => $arabicName ?: $englishName,
                                'en' => $englishName ?: $arabicName,
                            ],
                            'type' => 'city',
                            'shipping_fee_near' => $shippingFeeNear,
                            'shipping_fee_far' => $shippingFeeFar,
                            'parent_id' => $parentId,
                            'active' => true,
                        ]);
                        $this->line("Created: {$englishName} ({$arabicName})");
                    }

                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Row {$rowNumber}: Error - " . $e->getMessage();
                    $this->error("Row {$rowNumber}: " . $e->getMessage());
                }
            }

            $this->newLine();
            $this->info("Import completed!");
            $this->info("  - Imported/Updated: {$imported}");
            $this->info("  - Skipped: {$skipped}");

            if (!empty($errors)) {
                $this->newLine();
                $this->warn("Warnings/Errors:");
                foreach ($errors as $error) {
                    $this->warn("  - {$error}");
                }
            }

            return 0;
        } catch (Exception $e) {
            $this->error("Error reading file: " . $e->getMessage());
            return 1;
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Find column index by searching for possible column names
     */
    private function findColumnIndex(array $headerRow, array $possibleNames): int|false
    {
        $headerRowLower = array_map('strtolower', array_map('trim', $headerRow));
        
        foreach ($possibleNames as $name) {
            $nameLower = strtolower(trim($name));
            $index = array_search($nameLower, $headerRowLower);
            if ($index !== false) {
                return $index;
            }
        }
        
        return false;
    }
}
