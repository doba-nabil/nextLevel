<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\LocationDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LocationRequest;
use App\Models\Location;
use App\Services\LocationService;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function __construct(private LocationService $locationService) {}
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if(request()->is('*states*')){
            $type = 'state';
            $typee = 'states';
            $url = 'states';
        }
        if(request()->is('*countries*')){
            $type = 'country';
            $url = 'countries';
            $typee = 'countries';
        }
        if(request()->is('*cities*')){
            $type = 'city';
            $url = 'cities';
            $typee = 'cities';
        }
        $dataTable = new LocationDataTable($type);
        return $dataTable->with('type', $type)->render('dashboard.locations.index', compact('type', 'url', 'typee'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if(request()->is('*states*')){
            $type = 'states';
            $typee = 'state';
            $type_request = 'state';
        }
        if(request()->is('*countries*')){
            $type = 'countries';
            $type_request = 'country';
            $typee = 'country';
        }
        if(request()->is('*cities*')){
            $type = 'cities';
            $type_request = 'city';
            $typee = 'city';
        }
        $countries = Location::whereNull('parent_id')->get();
        return view('dashboard.locations.create',compact('type','type_request', 'countries', 'typee'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LocationRequest $request)
    {
        if(request()->is('*countries*')){
            $route = 'countries';
        }
        if(request()->is('*states*')){
            $route = 'states';
        }
        if(request()->is('*cities*')){
            $route = 'cities';
        }
        $this->locationService->create($request->validated());
        return redirect()->route($route.'.index')->with('success', __('admin.add_success'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Location $location)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $location = $this->locationService->getById($id);
        if($location->type == 'state'){
            $type = 'states';
        }
        if($location->type == 'country'){
            $type = 'countries';
        }
        if($location->type == 'city'){
            $type = 'cities';
        }
        $countries = Location::whereNull('parent_id')->get();
        return view('dashboard.locations.edit', compact('location', 'type', 'countries'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LocationRequest $request, $id)
    {
        $location = $this->locationService->getById($id);
        $this->locationService->update($location, $request->validated());
        if($location->type == 'country'){
            $route = 'countries';
        }
        if($location->type == 'state'){
            $route = 'states';
        }
        if($location->type == 'city'){
            $route = 'cities';
        }
        return redirect()->route($route.'.index')->with('success', __('admin.update_success'));
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * Unify cities settings for a state
     */
    public function unifyCities($id, Request $request)
    {
        $state = $this->locationService->getById($id);
        
        if ($state->type !== 'state') {
            return response()->json([
                'success' => false,
                'message' => __('admin.invalid_state') ?? 'Invalid state'
            ], 422);
        }

        $request->validate([
            'shipping_fee_near' => ['nullable', 'numeric', 'min:0'],
            'shipping_fee_far' => ['nullable', 'numeric', 'min:0'],
            'min_order_near' => ['nullable', 'numeric', 'min:0'],
            'min_order_far' => ['nullable', 'numeric', 'min:0'],
        ]);

        $cities = Location::where('type', 'city')
            ->where('parent_id', $state->id)
            ->get();

        $updatedCount = 0;
        foreach ($cities as $city) {
            $updateData = [];
            
            if ($request->has('shipping_fee_near') && $request->shipping_fee_near !== null) {
                $updateData['shipping_fee_near'] = $request->shipping_fee_near;
            }
            if ($request->has('shipping_fee_far') && $request->shipping_fee_far !== null) {
                $updateData['shipping_fee_far'] = $request->shipping_fee_far;
            }
            if ($request->has('min_order_near') && $request->min_order_near !== null) {
                $updateData['min_order_near'] = $request->min_order_near;
            }
            if ($request->has('min_order_far') && $request->min_order_far !== null) {
                $updateData['min_order_far'] = $request->min_order_far;
            }
            
            if (!empty($updateData)) {
                $city->update($updateData);
                $updatedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => __('admin.cities_unified_success') ?? "Successfully updated {$updatedCount} cities",
            'updated_count' => $updatedCount
        ]);
    }

    public function destroy($id)
    {
        try {
            $this->locationService->delete($id);
            return response()->json([
                'status' => 'success',
                'message' => __('admin.delete_success')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('admin.delete_error')
            ], 500);
        }
    }


    public function getParents(Request $request)
    {
        $type = $request->get('type');
        $countryId = $request->get('country_id');
        $stateId = $request->get('state_id');
        $locale = app()->getLocale();

        if ($type == 'state' && $countryId) {
            $parents = Location::where('type', 'state')
                ->where('parent_id', $countryId)
                ->get(['id', 'name'])
                ->map(function ($state) use ($locale) {
                    return [
                        'id' => $state->id,
                        'name' => $state->getTranslation('name', $locale)
                    ];
                });
        } elseif ($type == 'city' && $stateId) {
            $parents = Location::where('type', 'city')
                ->where('parent_id', $stateId)
                ->get(['id', 'name'])
                ->map(function ($city) use ($locale) {
                    return [
                        'id' => $city->id,
                        'name' => $city->getTranslation('name', $locale)
                    ];
                });
        } else {
            $parents = [];
        }

        return response()->json($parents);
    }
}
