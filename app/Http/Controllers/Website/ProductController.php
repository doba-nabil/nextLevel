<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\AddonGroup;
use App\Models\Product;
use App\Models\Favourite;
use App\Models\ProductPrice;
use App\Models\Currency;
use App\Models\Setting;
use App\Models\ProductNote;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function product($slug, Request $request)
    {
        $product = Product::where('slug', $slug)
            ->where('active', true)
            ->with([
                'addons.group' => function ($query) {
                    $query->where('active', 1);
                },
                'addons' => function ($query) {
                    $query->where('active', 1);
                },
                'products.boxAddons', // Eager load box products with their addons
                'products.addons' => function($q) {
                    $q->where('active', 1);
                },
                'products.definitions',
                'category', // Eager load category
                'definitions' // Eager load definitions
            ])
            ->firstOrFail();

        // Check if product is available in current location
        $menuType = session('menu_type', 'delivery');
        $isAvailable = false;
        $availabilityMessage = '';

        if ($menuType === 'pickup') {
            $pickupBranchId = session('pickup_branch_id');
            if ($pickupBranchId) {
                $isAvailable = $product->branches()
                    ->where('branches.id', $pickupBranchId)
                    ->where('branches.active', true)
                    ->where('product_branches.status', 'available')
                    ->exists();
                
                if (!$isAvailable) {
                    $availabilityMessage = __('website.product_not_available_in_branch') ?? 'هذا المنتج غير متاح في الفرع المختار حالياً';
                }
            }
        } else {
            $userLocation = session('user_location');
            if ($userLocation && isset($userLocation['city_id']) && $userLocation['city_id']) {
                $cityId = (int) $userLocation['city_id'];
                $isAvailable = $product->branches()
                    ->whereHas('cities', function($q) use ($cityId) {
                        $q->where('locations.id', $cityId);
                    })
                    ->where('branches.active', true)
                    ->where('product_branches.status', 'available')
                    ->exists();
                
                if (!$isAvailable) {
                    $availabilityMessage = __('website.product_not_available_in_selected_city') ?? 'هذا المنتج غير متاح في المدينة المختارة حالياً';
                }
            } else {
                // If no city selected, allow viewing but show message
                $isAvailable = true;
            }
        }
        // Group addons by group name and sort by order
        $groupedAddons = $product->addons->groupBy(fn($addon) => $addon->group->name ?? 'Other')
            ->map(function($addons) {
                $firstAddon = $addons->first();
                $group = $firstAddon->group ?? null;
                
                return [
                    'addons' => $addons->sortBy(function($addon) {
                        return [$addon->pivot->order ?? 999, $addon->pivot->type === 'mandatory' ? 1 : 0];
                    })->values(),
                    'group' => $group ? [
                        'id' => $group->id,
                        'name' => $group->name,
                        'is_selection_mandatory' => (bool)($group->is_selection_mandatory ?? false),
                        'max_selections' => $group->max_selections,
                        'min_selections' => $group->min_selections,
                    ] : null
                ];
            })
            ->sortBy(function($groupData) {
                // Sort groups by the minimum order of addons in each group
                return $groupData['addons']->min(function($addon) {
                    return $addon->pivot->order ?? 999;
                });
            });
        // Get related products from the same category only
        // Ensure category_id exists and is not null
        $related_products = collect();
        if ($product->category_id) {
            $related_products = Product::where('active', true)
                ->whereNotNull('category_id')
                ->where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->where('is_box', 0) // Exclude boxes, only show regular products
                ->with(['definitions', 'addonGroups' => function($q) {
                    $q->where('active', 1);
                }, 'addons' => function($q) {
                    $q->where('active', 1);
                }])
                ->inRandomOrder()
                ->take(4)
                ->get();
        }
        $favoriteProductIds = collect();
        if (auth('web')->check()) {
            $favoriteProductIds = Favourite::where('user_id', auth('web')->id())
                ->pluck('product_id');
        }
        $fromMenu = $request->has('from_menu') && $request->get('from_menu') == '1';
        
        // Get cart products with quantities
        $cart = session('cart', []);
        $cartProductIds = collect($cart)->pluck('product_id', 'product_id')->toArray();
        $cartQuantities = collect($cart)->mapWithKeys(function ($item) {
            return [$item['product_id'] => $item['quantity']];
        })->toArray();

        $isBox = $product->is_box ? true : false;
        $boxProducts = collect();
        $boxProductsByTitle = collect();
        if ($isBox) {
            // Products already loaded via eager loading, use them
            $boxProducts = $product->products;
            
            // Group products by title
            $boxProductsByTitle = $boxProducts->groupBy(function($boxProduct) {
                $title = $boxProduct->pivot->title ?? null;
                if (is_string($title) && !empty($title)) {
                    $decoded = json_decode($title, true);
                    $title = $decoded !== null ? $decoded : [];
                } elseif (empty($title)) {
                    $title = [];
                }
                // Use a key based on title content
                if (!empty($title) && is_array($title)) {
                    return json_encode($title, JSON_UNESCAPED_UNICODE);
                }
                return 'no_title';
            })->map(function($products, $titleKey) {
                $firstProduct = $products->first();
                $title = $firstProduct->pivot->title ?? null;
                if (is_string($title) && !empty($title)) {
                    $decoded = json_decode($title, true);
                    $title = $decoded !== null ? $decoded : [];
                } elseif (empty($title)) {
                    $title = [];
                }
                
                return [
                    'title' => $title,
                    'is_required' => (bool)($firstProduct->pivot->is_required ?? false),
                    'max_count' => (int)($firstProduct->pivot->max_count ?? 1),
                    'min_count' => (int)($firstProduct->pivot->min_count ?? 0),
                    'products' => $products
                ];
            })->values();
        }
        
        // Get logo URL from database
        $settingModel = Setting::getSettingModel();
        $logoUrl = $settingModel?->getFirstMediaUrl('logo') ?: asset('website/assets/img/logo.png');
        
        return view('website.products.product', compact('product', 'groupedAddons', 'related_products', 'favoriteProductIds', 'fromMenu', 'isBox', 'boxProducts', 'boxProductsByTitle', 'cartProductIds', 'cartQuantities', 'isAvailable', 'availabilityMessage', 'logoUrl'));
    }

    public function getPrices($productId)
    {
        $product = Product::findOrFail($productId);
        $prices = $product->prices()->with('currency')->get();
        
        $priceData = $prices->map(function($price) {
            return [
                'currency_id' => $price->currency_id,
                'currency_name' => $price->currency->name,
                'currency_sign' => $price->currency->sign,
                'price' => $price->price,
                'discount_price' => $price->discount_price,
                'discount_type' => $price->discount_type ?? 'none',
            ];
        });

        return response()->json([
            'prices' => $priceData,
            'translations' => [
                'price' => __('admin.price'),
                'before_discount' => __('admin.before_discount'),
                'after_discount' => __('admin.after_discount'),
                'no_discount' => __('admin.no_discount'),
                'percent' => __('admin.percent'),
                'fixed' => __('admin.fixed'),
                'no_prices' => 'No prices available',
            ]
        ]);
    }

    public function updatePrice(Request $request, $productId)
    {
        $user = auth('web')->user();
        if (!$user || !$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $product = Product::findOrFail($productId);
        $prices = $request->input('prices', []);

        foreach ($prices as $currencyId => $priceData) {
            $price = ProductPrice::where('product_id', $product->id)
                ->where('currency_id', $currencyId)
                ->first();

            $priceBefore = (float) ($priceData['before'] ?? 0);
            $priceAfter = isset($priceData['after']) && $priceData['after'] !== '' ? (float) $priceData['after'] : null;
            $discountType = $priceData['discount_type'] ?? 'none';
            
            // Only set discount if after price is provided and is less than before price
            $hasDiscount = $priceAfter !== null && $priceAfter < $priceBefore && $priceAfter > 0;

            $data = [
                'price' => $priceBefore,
                'discount_type' => $hasDiscount && $discountType !== 'none' ? $discountType : null,
                'discount_price' => $hasDiscount ? $priceAfter : $priceBefore,
            ];

            if ($price) {
                $price->update($data);
            } else {
                $data['product_id'] = $product->id;
                $data['currency_id'] = $currencyId;
                ProductPrice::create($data);
            }
        }

        return response()->json([
            'success' => true,
            'message' => __('admin.update_success')
        ]);
    }

    public function storeNote(Request $request, $productId)
    {
        // Check if product notes are enabled
        $isEnabled = Setting::getValue('enable_product_notes', null, '0') == '1';
        if (!$isEnabled) {
            return response()->json([
                'success' => false,
                'message' => 'Product notes are disabled'
            ], 403);
        }

        $request->validate([
            'note' => 'required|string|max:1000'
        ]);

        $product = Product::findOrFail($productId);
        $user = auth('web')->user();

        $note = ProductNote::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'note' => $request->input('note')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Note added successfully',
            'note' => [
                'id' => $note->id,
                'note' => $note->note,
                'created_at' => $note->created_at->format('Y-m-d H:i:s')
            ]
        ]);
    }

    public function getNotes($productId)
    {
        $product = Product::findOrFail($productId);
        $user = auth('web')->user();

        $notes = ProductNote::where('product_id', $product->id)
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'notes' => $notes->map(function($note) {
                return [
                    'id' => $note->id,
                    'note' => $note->note,
                    'created_at' => $note->created_at->format('Y-m-d H:i:s')
                ];
            })
        ]);
    }
}
