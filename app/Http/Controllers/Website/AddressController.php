<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    /**
     * Display a listing of addresses for the authenticated user.
     */
    public function index()
    {
        $user = auth()->user();
        $addresses = Address::where('user_id', $user->id)
            ->active()
            ->orderBy('is_main', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'addresses' => $addresses->map(function($address) {
                // Find state and city IDs by matching names
                $stateId = null;
                $cityId = null;
                
                if ($address->state) {
                    $state = Location::where('type', 'state')
                        ->whereRaw('JSON_UNQUOTE(JSON_EXTRACT(name, "$.' . app()->getLocale() . '")) = ?', [$address->state])
                        ->first();
                    $stateId = $state ? $state->id : null;
                }
                
                if ($address->city && $stateId) {
                    $city = Location::where('type', 'city')
                        ->where('parent_id', $stateId)
                        ->whereRaw('JSON_UNQUOTE(JSON_EXTRACT(name, "$.' . app()->getLocale() . '")) = ?', [$address->city])
                        ->first();
                    $cityId = $city ? $city->id : null;
                }
                
                return [
                    'id' => $address->id,
                    'title' => $address->title,
                    'address' => $address->address,
                    'full_address' => $address->full_address,
                    'country' => $address->country ?? '',
                    'state' => $address->state ?? '',
                    'city' => $address->city ?? '',
                    'state_id' => $stateId,
                    'city_id' => $cityId,
                    'area' => $address->area ?? '',
                    'block' => $address->block ?? '',
                    'street' => $address->street ?? '',
                    'building' => $address->building ?? '',
                    'floor' => $address->floor ?? '',
                    'apartment' => $address->apartment ?? '',
                    'latitude' => $address->latitude,
                    'longitude' => $address->longitude,
                    'is_main' => $address->is_main,
                    'additional_directions' => $address->additional_directions ?? '',
                ];
            })
        ]);
    }

    /**
     * Store a newly created address.
     */
    public function store(Request $request)
    {
        // Normalize is_main to boolean
        $request->merge([
            'is_main' => filter_var($request->input('is_main', false), FILTER_VALIDATE_BOOLEAN)
        ]);
        
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255|in:home,work,other',
            'address' => 'nullable|string',
            'country' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'area' => 'nullable|string|max:255',
            'block' => 'required|string|max:255',
            'street' => 'required|string|max:255',
            'avenue' => 'nullable|string|max:255',
            'building' => 'required|string|max:255',
            'floor' => 'required|string|max:255',
            'apartment' => 'required|string|max:255',
            'additional_directions' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_main' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();

        // Check if user has any main address
        $hasMainAddress = Address::where('user_id', $user->id)
            ->where('is_main', true)
            ->exists();

        // If this is set as main, unset other main addresses
        if ($request->input('is_main', false)) {
            Address::where('user_id', $user->id)
                ->update(['is_main' => false]);
        }

        // If this is the first address or user has no main address, make it main
        $isFirstAddress = Address::where('user_id', $user->id)->count() === 0;
        $isMain = $request->input('is_main', $isFirstAddress || !$hasMainAddress);

        // Get default country (first active country) if country is not provided
        $defaultCountry = Location::where('type', 'country')
            ->where('active', true)
            ->orderBy('id')
            ->first();
        $countryName = $request->input('country') ?: ($defaultCountry ? $defaultCountry->getTranslation('name', app()->getLocale()) : '');

        // Get city and state names from hidden inputs if available, otherwise use the select values
        $cityName = $request->input('city_name') ?: $request->input('city');
        $stateName = $request->input('state_name') ?: $request->input('state');
        
        // If city_name is empty but we have city_id, get the city name from database
        if (empty($cityName) && $request->input('city')) {
            $cityId = $request->input('city');
            if (is_numeric($cityId)) {
                $city = Location::where('type', 'city')->where('id', $cityId)->first();
                if ($city) {
                    $cityName = $city->getTranslation('name', app()->getLocale());
                }
            }
        }
        
        // If state_name is empty but we have state_id, get the state name from database
        if (empty($stateName) && $request->input('state')) {
            $stateId = $request->input('state');
            if (is_numeric($stateId)) {
                $state = Location::where('type', 'state')->where('id', $stateId)->first();
                if ($state) {
                    $stateName = $state->getTranslation('name', app()->getLocale());
                }
            }
        }

        $address = Address::create([
            'user_id' => $user->id,
            'title' => $request->input('title'),
            'address' => $request->input('address'),
            'country' => $countryName,
            'state' => $stateName,
            'city' => $cityName,
            'area' => $request->input('area'),
            'block' => $request->input('block'),
            'street' => $request->input('street'),
            'avenue' => $request->input('avenue'),
            'building' => $request->input('building'),
            'floor' => $request->input('floor'),
            'apartment' => $request->input('apartment'),
            'additional_directions' => $request->input('additional_directions'),
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
            'is_main' => $isMain,
            'active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => __('website.address_added_successfully'),
            'address' => $address
        ]);
    }

    /**
     * Update the specified address.
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $address = Address::where('user_id', $user->id)->findOrFail($id);

        // Normalize is_main to boolean
        $request->merge([
            'is_main' => filter_var($request->input('is_main', false), FILTER_VALIDATE_BOOLEAN)
        ]);

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255|in:home,work,other',
            'address' => 'nullable|string',
            'country' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'area' => 'nullable|string|max:255',
            'block' => 'required|string|max:255',
            'street' => 'required|string|max:255',
            'avenue' => 'nullable|string|max:255',
            'building' => 'required|string|max:255',
            'floor' => 'required|string|max:255',
            'apartment' => 'required|string|max:255',
            'additional_directions' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_main' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // If this is set as main, unset other main addresses
        if ($request->input('is_main', false)) {
            Address::where('user_id', $user->id)
                ->where('id', '!=', $address->id)
                ->update(['is_main' => false]);
        }

        // Get default country (first active country) if country is not provided
        $defaultCountry = Location::where('type', 'country')
            ->where('active', true)
            ->orderBy('id')
            ->first();
        $countryName = $request->input('country') ?: ($defaultCountry ? $defaultCountry->getTranslation('name', app()->getLocale()) : '');

        $updateData = $request->only([
            'title',
            'address',
            'state',
            'city',
            'area',
            'block',
            'street',
            'building',
            'floor',
            'apartment',
            'additional_directions',
            'latitude',
            'longitude',
            'is_main',
        ]);
        
        // Set country to default if not provided
        $updateData['country'] = $countryName;

        $address->update($updateData);

        return response()->json([
            'success' => true,
            'message' => __('website.address_updated_successfully'),
            'address' => $address->fresh()
        ]);
    }

    /**
     * Remove the specified address.
     */
    public function destroy($id)
    {
        $user = auth()->user();
        $address = Address::where('user_id', $user->id)->findOrFail($id);

        // If this is the main address and there are other addresses, set another one as main
        if ($address->is_main) {
            $otherAddress = Address::where('user_id', $user->id)
                ->where('id', '!=', $address->id)
                ->active()
                ->first();

            if ($otherAddress) {
                $otherAddress->setAsMain();
            }
        }

        $address->delete();

        return response()->json([
            'success' => true,
            'message' => __('website.address_deleted_successfully')
        ]);
    }

    /**
     * Set address as main
     */
    public function setMain($id)
    {
        $user = auth()->user();
        $address = Address::where('user_id', $user->id)->findOrFail($id);

        $address->setAsMain();

        return response()->json([
            'success' => true,
            'message' => __('website.address_set_as_main'),
            'address' => $address->fresh()
        ]);
    }
}

