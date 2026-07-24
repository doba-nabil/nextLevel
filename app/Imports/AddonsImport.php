<?php

namespace App\Imports;

use App\Models\Addon;
use App\Models\AddonGroup;
use App\Models\AddonPrice;
use App\Models\Currency;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;

class AddonsImport implements ToCollection, WithHeadingRow, WithValidation
{
    use Importable;

    public function collection(Collection $rows)
    {
        $currency = Currency::first();

        foreach ($rows as $row) {
            if (empty($row['name_ar']) && empty($row['name_en'])) {
                continue;
            }

            $addonGroup = null;
            if (!empty($row['addon_group_name'])) {
                $addonGroup = AddonGroup::firstOrCreate(
                    [
                        'name->en' => $row['addon_group_name'],
                    ],
                    [
                        'name' => [
                            'ar' => $row['addon_group_name'],
                            'en' => $row['addon_group_name'],
                        ],
                        'type' => $row['addon_group_name'],
                        'max_items' => 1,
                    ]
                );
            }

            $addon = Addon::updateOrCreate(
                [
                    'name->ar' => $row['name_ar'],
                    'name->en' => $row['name_en'],
                ],
                [
                    'name' => [
                        'ar' => $row['name_ar'],
                        'en' => $row['name_en'],
                    ],
                    'addon_group_id' => $addonGroup?->id,
                ]
            );

            if ($currency && isset($row['price'])) {
                AddonPrice::updateOrCreate(
                    [
                        'addon_id' => $addon->id,
                        'currency_id' => $currency->id,
                    ],
                    [
                        'price' => $row['price'],
                    ]
                );
            }
        }
    }


    public function rules(): array
    {
        return [
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'addon_group_name' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
        ];
    }
}
