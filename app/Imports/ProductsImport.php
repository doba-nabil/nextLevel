<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductPrice;
use App\Models\Currency;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;

class ProductsImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    public function model(array $row)
    {

        $category = Category::where('name->en', $row['category_name'])
            ->orWhere('name->ar', $row['category_name'])
            ->first();

        if (!$category) {
            $category = Category::create([
                'name' => [
                    'ar' => $row['category_name'],
                    'en' => $row['category_name'],
                ],
                'slug' => Str::slug($row['category_name']),
                'active' => 1,
            ]);
        }

        $slug = Str::slug($row['name_en']);

        $product = Product::find($row['id']) ?? new Product();
        $product->id = $row['id'];
        $product->category_id = $category->id;
        $product->name = [
            'ar' => $row['name_ar'],
            'en' => $row['name_en'],
        ];
        $product->description = [
            'ar' => $row['description_ar'],
            'en' => $row['description_en'],
        ];
        $product->slug = $slug;
        $product->type = $row['type'];
        $product->is_box = $row['is_box'];
        $product->ingrediant_text = [
            'ar' => $row['ingreduant_text_ar'],
            'en' => $row['ingrediant_text_en'],
        ];
        $product->product_type = $row['product_type'];
        $product->save();

        $currencyId = Currency::first()?->id ?? 1;
        $price = $row['price'] ?? 0;
        $newPrice = $row['new_price'] ?? null;
        $hasDiscount = !empty($newPrice) && $newPrice < $price;

        ProductPrice::updateOrCreate(
            [
                'product_id' => $product->id,
                'currency_id' => $currencyId,
            ],
            [
                'price' => $price,
                'discount_price' => $hasDiscount ? $newPrice : null,
                'discount_percentage' => $hasDiscount
                    ? round((($price - $newPrice) / $price) * 100, 2)
                    : null,
                'has_discount' => $hasDiscount ? 1 : 0,
                'discount_type' => $hasDiscount ? 'fixed' : null,
            ]
        );

        return $product;
    }

    public function rules(): array
    {
        return [
            'category_name' => 'required|string',
            'name_ar' => 'required|string',
            'name_en' => 'required|string',
            'type' => 'required|in:delivery,pickup',
            'is_box' => 'required|boolean',
            'product_type' => 'required|in:meal,product,box',
            'price' => 'required|numeric|min:0',
            'new_price' => 'nullable|numeric|min:0',
        ];
    }
}
