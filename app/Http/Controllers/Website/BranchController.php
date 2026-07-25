<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Location;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function branches($slug = null)
    {
        $sessionLocation = session()->get('user_location');

        $branchesQuery = Branch::where('active', 1)->with('location.parent');

        if ($sessionLocation && isset($sessionLocation['city'])) {
            $city = Location::where('id', $sessionLocation['city'])->first();
            if ($city) {
                $country = $city->type === 'country'
                    ? $city
                    : ($city->type === 'state' ? $city->parent : $city->parent->parent ?? null);

                if ($country) {
                    $cityIds = Location::where('type', 'city')
                        ->whereHas('parent', function($q) use ($country) {
                            $q->where('parent_id', $country->id)->orWhere('id', $country->id);
                        })->pluck('id');

                    $branchesQuery->whereIn('location_id', $cityIds);
                }
            }
        }

        $allBranches = $branchesQuery->orderBy('id','desc')->get();
        if ($slug) {
            $activeBranch = $allBranches->where('slug', $slug)->first();
            abort_if(!$activeBranch, 404);
        } else {
            $activeBranch = $allBranches->first();
        }

        return view('website.branches.branches', compact('allBranches', 'activeBranch'));
    }

    /**
     * Get branches filtered by city for pickup selection
     */
    public function getBranchesByCity(Request $request)
    {
        $cityId = $request->get('city_id');
        
        if (!$cityId) {
            return response()->json([
                'status' => false,
                'message' => __('website.please_select_city'),
                'branches' => []
            ]);
        }

        $city = Location::where('type', 'city')
            ->where('id', $cityId)
            ->where('active', true)
            ->first();

        if (!$city) {
            return response()->json([
                'status' => false,
                'message' => __('website.invalid_city'),
                'branches' => []
            ]);
        }

        // Get branches that serve this city (through branch_cities pivot table)
        $branches = Branch::where('active', 1)
            ->whereHas('cities', function($q) use ($cityId) {
                $q->where('locations.id', $cityId);
            })
            ->with('location')
            ->orderBy('name')
            ->get()
            ->map(function($branch) {
                return [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'address' => $branch->address,
                    'phone' => $branch->phone,
                    'whatsapp' => $branch->whatsapp,
                ];
            });

        return response()->json([
            'status' => true,
            'branches' => $branches
        ]);
    }

    /**
     * Save selected pickup branch to session
     */
    public function savePickupBranch(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id'
        ]);

        $branch = Branch::where('active', 1)->findOrFail($request->branch_id);
        
        session(['pickup_branch_id' => $branch->id]);

        return response()->json([
            'status' => true,
            'message' => __('website.branch_selected_successfully'),
            'branch' => [
                'id' => $branch->id,
                'name' => $branch->name,
                'address' => $branch->address,
            ]
        ]);
    }

    /**
     * Handle QR code scan to select branch and set order type to pickup
     */
    public function scanQr($identifier)
    {
        $branch = Branch::where('active', 1)->where(function($query) use ($identifier) {
            $query->where('slug', $identifier)->orWhere('id', $identifier);
        })->firstOrFail();
        $city = Location::where('id', $branch->location_id)->where('type', 'city')->first();
        if (!$city) {
            $city = $branch->cities()->first();
        }

        $state = $city ? $city->parent : null;
        $country = $state ? $state->parent : null;

        session([
            'pickup_branch_id' => $branch->id,
            'menu_type' => 'pickup',
            'order_type' => 'pickup',
            
            'user_location' => [
                'country' => $country ? $country->id : null,
                'country_name' => $country ? $country->getTranslation('name', app()->getLocale()) : null,
                'state_id' => $state ? $state->id : null,
                'state' => $state ? $state->getTranslation('name', app()->getLocale()) : null,
                'city_id' => $city ? $city->id : null,
                'city' => $city ? $city->getTranslation('name', app()->getLocale()) : null,
                'delivery_time' => $city ? $city->delivery_time : null,
            ],
            
            'pickup_governorate_id' => $state ? $state->id : null,
            'pickup_city_id' => $city ? $city->id : null,
        ]);

        return redirect()->route('website.home')->with('success', __('website.branch_selected_successfully') ?? 'تم اختيار الفرع بنجاح');
    }

}
