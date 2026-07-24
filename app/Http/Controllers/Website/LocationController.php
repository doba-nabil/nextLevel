<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function getChildren($parentId)
    {
        $locations = Location::where('parent_id', $parentId)->get();
        $lang = app()->getLocale();
        $locations = $locations->map(function($loc) use ($lang) {
            return [
                'id' => $loc->id,
                'name' => $loc->name ?? ''
            ];
        });
        return response()->json($locations);
    }

    public function getStates(Request $request)
    {
        $locale = app()->getLocale();

        // Get country ID from request, session, or user
        $countryId = $request->get('country_id');

        // If not in request, check session
        if (!$countryId) {
            $userLocation = session('user_location');
            if ($userLocation && isset($userLocation['country'])) {
                $countryId = $userLocation['country'];
            }
        }

        // If still not found, check logged-in user's country
        if (!$countryId && auth()->check()) {
            $user = auth()->user();
            if ($user->country_id) {
                $countryId = $user->country_id;
            }
        }

        // If still not found, get default country (first active country)
        if (!$countryId) {
            $defaultCountry = Location::where('type', 'country')
                ->where('active', true)
                ->orderBy('id')
                ->first();
            if ($defaultCountry) {
                $countryId = $defaultCountry->id;
            }
        }

        $query = Location::where('type', 'state')
            ->where('active', true);

        // Filter by country if we have one
        if ($countryId) {
            $query->where('parent_id', $countryId);
        }

        $states = $query->get(['id', 'name'])
            ->map(function ($state) use ($locale) {
                return [
                    'id' => $state->id,
                    'name' => $state->getTranslation('name', $locale)
                ];
            });
        return response()->json($states);
    }

    public function getCities(Request $request)
    {
        $stateId = $request->get('state_id');
        $locale = app()->getLocale();

        if (!$stateId) {
            return response()->json([]);
        }

        $cities = Location::where('type', 'city')
            ->where('parent_id', $stateId)
            ->where('active', true)
            ->get(['id', 'name', 'delivery_time'])
            ->map(function ($city) use ($locale) {
                return [
                    'id' => $city->id,
                    'name' => $city->getTranslation('name', $locale),
                    'delivery_time' => $city->delivery_time
                ];
            });
        return response()->json($cities);
    }

    public function getDeliveryTime(Request $request)
    {
        $cityId = $request->get('city_id');

        if (!$cityId) {
            return response()->json(['delivery_time' => null]);
        }

        $city = Location::where('type', 'city')
            ->where('id', $cityId)
            ->where('active', true)
            ->first(['id', 'delivery_time', 'name', 'shipping_fee_near']);

        if (!$city) {
            return response()->json(['delivery_time' => null]);
        }

        return response()->json([
            'delivery_time' => $city->delivery_time,
            'delivery_cost' => $city->shipping_fee_near,
            'currency' => \App\Models\Currency::getCurrentCurrencySign(),
            'city_name' => $city->getTranslation('name', app()->getLocale())
        ]);
    }

    public function saveLocation(Request $request)
    {
        // New format: state_id/city_id
        // Check if both state_id and city_id are present and not empty
        if ($request->has('state_id') && $request->has('city_id') &&
            !empty($request->state_id) && !empty($request->city_id) &&
            is_numeric($request->state_id) && is_numeric($request->city_id)) {

            // Validate and cast to integers
            $stateId = (int) $request->state_id;
            $cityId = (int) $request->city_id;
            $stateName = $request->state ?? '';
            $cityName = $request->city ?? '';

            // Validate that IDs are valid integers
            if ($stateId <= 0 || $cityId <= 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid state or city ID'
                ], 422);
            }

            // Verify the city exists and is actually a city
            $city = Location::where('id', $cityId)
                ->where('type', 'city')
                ->first();

            if (!$city) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'City not found'
                ], 404);
            }

            // Get default country
            $defaultCountry = Location::where('type', 'country')
                ->where('active', true)
                ->orderBy('id')
                ->first();

            if(auth()->check()) {
                $user = auth()->user();
                $user->update([
                    'location_id' => $cityId, // Now guaranteed to be an integer
                ]);
            }

            // Get old city ID from session to check if city changed
            $oldUserLocation = session('user_location');
            $oldCityId = $oldUserLocation['city_id'] ?? null;

            session(['user_location' => [
                'country' => $defaultCountry ? $defaultCountry->id : null,
                'country_name' => $defaultCountry ? $defaultCountry->getTranslation('name', app()->getLocale()) : null,
                'state_id' => $stateId,
                'state' => $stateName,
                'city_id' => $cityId,
                'city' => $cityName,
                'delivery_time' => $city ? $city->delivery_time : null,
            ]]);

            // If pickup is selected, save governorate and city for pickup branch selection
            $menuType = session('menu_type', 'delivery');
            if ($menuType === 'pickup') {
                session([
                    'pickup_governorate_id' => $stateId,
                    'pickup_city_id' => $cityId,
                ]);
                
                // Save pickup branch if provided
                if ($request->has('pickup_branch_id') && $request->pickup_branch_id) {
                    session(['pickup_branch_id' => $request->pickup_branch_id]);
                }
            }

            // Clear cart if city changed
            if ($oldCityId && $oldCityId != $cityId) {
                session()->forget(['cart', 'applied_voucher']);
                // Clear pickup branch if city changed
                if (session('menu_type') === 'pickup') {
                    session()->forget(['pickup_branch_id']);
                }
            } else {
                // Even if city didn't change, validate cart items against current city
                $this->validateCartForCity($cityId);
            }

            return response()->json(['status' => 'success']);
        } elseif ($request->has('lat') && $request->has('lng')) {
            // Old format: save lat and lng (for backward compatibility)
            $lat = $request->input('lat');
            $lng = $request->input('lng');

            if(auth()->check()) {
                $user = auth()->user();
                $user->update([
                    'lat' => $lat,
                    'long' => $lng,
                ]);
            }

            session([
                'lat' => $lat,
                'longitude' => $lng,
            ]);

            return response()->json(['status' => 'success']);
        } else {
            // Old format: country/state/city (for backward compatibility)
            // Try to cast to integer if possible, otherwise validate
            $cityId = is_numeric($request->city) ? (int) $request->city : null;

            if (!$cityId || $cityId <= 0) {
                // If city is not a valid ID, try to find it by name
                $city = Location::where('type', 'city')
                    ->whereRaw('JSON_UNQUOTE(JSON_EXTRACT(name, "$.' . app()->getLocale() . '")) = ?', [$request->city])
                    ->first();

                if ($city) {
                    $cityId = $city->id;
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Invalid city. Please use the new location format with state_id and city_id.'
                    ], 422);
                }
            }

            // Verify the city exists
            $city = Location::where('id', $cityId)
                ->where('type', 'city')
                ->first();

            if (!$city) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'City not found'
                ], 404);
            }

            if(auth()->check()) {
                $user = auth()->user();
                $user->update([
                    'location_id' => $cityId, // Now guaranteed to be an integer
                ]);
            }

            session(['user_location' => [
                'country' => $request->country ?? null,
                'state' => $request->state ?? null,
                'city' => $city->getTranslation('name', app()->getLocale()),
                'city_id' => $cityId,
            ]]);

            return response()->json(['status' => 'success']);
        }
    }

    /**
     * Validate cart items against current city and remove unavailable products
     */
    private function validateCartForCity($cityId)
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return;
        }

        $validCart = [];
        foreach ($cart as $item) {
            $product = \App\Models\Product::where('active', true)
                ->find($item['product_id'] ?? null);

            if (!$product) {
                continue; // Skip invalid products
            }

            // Check if product is available in selected city
            $isAvailableInCity = $product->branches()
                ->whereHas('cities', function($q) use ($cityId) {
                    $q->where('locations.id', $cityId);
                })
                ->where('branches.active', true)
                ->where('product_branches.status', 'available')
                ->exists();

            if ($isAvailableInCity) {
                $validCart[] = $item;
            }
        }

        // Update cart with only valid items
        if (count($validCart) !== count($cart)) {
            session()->put('cart', $validCart);
            // Clear voucher if cart becomes empty or significantly changed
            if (empty($validCart)) {
                session()->forget('applied_voucher');
            }
        }
    }
}
