<?php

namespace App\Providers;

use App\Models\Addon;
use App\Models\Location;
use App\Models\Product;
use App\Models\Slider;
use App\Observers\SliderObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (!session()->has('menu_type')) {
            session(['menu_type' => 'delivery']);
        }

        View::composer('website.*', function ($view) {
            $countries = Location::whereNull('parent_id')->where('active',1)->get();
            $userLocation = session('user_location') ?? null;

            $view->with([
                'countries' => $countries,
                'userLocation' => $userLocation,
            ]);
        });
        Slider::observe(SliderObserver::class);
        View::composer('website.*', function ($view) {
            $cart = session('cart', []);
            $cartProducts = collect($cart)->map(function ($item) {
                $product = Product::find($item['product_id']);
                if (!$product) return null;
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'image' => $product->getFirstMediaUrl('products'),
                    'quantity' => $item['quantity'],
                    'addons' => Addon::whereIn('id', $item['addons'])->get(['id', 'name']),
                    'price' => number_format($item['price'], 3),
                ];
            })->filter();

            $cartCount = $cartProducts->count();
            $cartTotal = $cartProducts->sum(function ($p) {
                return floatval(str_replace(',', '', $p['price']));
            });

            $view->with(compact('cartProducts', 'cartCount', 'cartTotal'));
        });
         $storagePath = storage_path('app/public');
    $publicPath = public_path('storage');

    if (!File::exists($publicPath)) {
        File::makeDirectory($publicPath, 0755, true);
    }

    // فقط انسخ المجلدات الجديدة أو الملفات غير الموجودة
    $files = File::allFiles($storagePath);
    foreach ($files as $file) {
        $target = $publicPath . '/' . $file->getRelativePathname();
        $targetDir = dirname($target);
        if (!File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }
        if (!File::exists($target)) {
            File::copy($file->getRealPath(), $target);
        }
    }
    }
}
