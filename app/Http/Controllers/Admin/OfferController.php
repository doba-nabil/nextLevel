<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\OfferDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OfferRequest;
use App\Models\Product;
use App\Models\Offer;
use App\Services\OfferService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OfferController extends Controller
{
    public function __construct(private OfferService $offerService) {}

    public function index(OfferDataTable $dataTable)
    {
        return $dataTable->render('dashboard.offers.index');
    }

    public function create()
    {
        // Get products that:
        // 1. Don't have discounts in product_price table
        // 2. Are not in any active (not expired) offers
        $products = Product::where('active', true)
            ->whereDoesntHave('prices', function($query) {
                $query->where('has_discount', true);
            })
            ->whereDoesntHave('offers', function($query) {
                $now = now();
                $query->where('is_active', true)
                    ->whereDate('start_date', '<=', $now)
                    ->whereDate('end_date', '>=', $now);
            })
            ->get();
        return view('dashboard.offers.create', compact('products'));
    }

    public function store(OfferRequest $request)
    {
        $validated = $request->validated();
        
        // Log the data being saved
        Log::info('Creating offer', [
            'discount_type' => $validated['discount_type'] ?? null,
            'discount_value' => $validated['discount_value'] ?? null,
            'products_count' => count($validated['products'] ?? [])
        ]);
        
        $offer = $this->offerService->create($validated);
        
        // Log the created offer
        Log::info('Offer created', [
            'offer_id' => $offer->id,
            'discount_type' => $offer->discount_type,
            'discount_value' => $offer->discount_value,
        ]);
        
        return redirect()->route('offers.index')->with('success', __('admin.save_success'));
    }

    public function edit($id)
    {
        $offer = $this->offerService->getById($id);
        $offerProductIds = $offer->products->pluck('id')->toArray();
        
        // Get products that:
        // 1. Don't have discounts in product_price table
        // 2. Are not in any active (not expired) offers
        // 3. BUT include products that are already in this offer (so they remain selectable)
        $products = Product::where('active', true)
            ->where(function($query) use ($offer, $offerProductIds) {
                $query->where(function($q) use ($offerProductIds) {
                    // Products already in this offer
                    $q->whereIn('products.id', $offerProductIds);
                })->orWhere(function($q) use ($offer, $offerProductIds) {
                    // Products NOT in this offer but meet other criteria
                    $q->whereDoesntHave('prices', function($priceQuery) {
                        $priceQuery->where('has_discount', true);
                    })
                    ->whereDoesntHave('offers', function($offerQuery) use ($offer) {
                        $now = now();
                        $offerQuery->where('offers.id', '!=', $offer->id)
                            ->where('offers.is_active', true)
                            ->whereDate('offers.start_date', '<=', $now)
                            ->whereDate('offers.end_date', '>=', $now);
                    });
                });
            })
            ->get();
        return view('dashboard.offers.edit', compact('offer', 'products'));
    }

    public function update(OfferRequest $request, $id)
    {
        $offer = $this->offerService->getById($id);
        $validated = $request->validated();
        
        // Log the data being updated
        Log::info('Updating offer', [
            'offer_id' => $id,
            'discount_type' => $validated['discount_type'] ?? null,
            'discount_value' => $validated['discount_value'] ?? null,
            'is_active' => $validated['is_active'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'products_count' => count($validated['products'] ?? [])
        ]);
        
        $this->offerService->update($offer, $validated);
        
        // Refresh the offer to get updated values
        $offer->refresh();
        
        // Log the updated offer
        Log::info('Offer updated', [
            'offer_id' => $offer->id,
            'discount_type' => $offer->discount_type,
            'discount_value' => $offer->discount_value,
            'is_active' => $offer->is_active,
        ]);
        
        return redirect()->route('offers.index')->with('success', __('admin.update_success'));
    }

    public function destroy($id)
    {
        try {
            $this->offerService->delete($id);
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

    public function toggleActive(Request $request, $id)
    {
        try {
            $offer = $this->offerService->getById($id);
            $offer->is_active = $request->input('active', !$offer->is_active);
            $offer->save();

            return response()->json([
                'status' => 'success',
                'message' => __('admin.update_success'),
                'active' => $offer->is_active
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('admin.update_error')
            ], 500);
        }
    }
}
