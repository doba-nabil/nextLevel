<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\CurrencyDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Http\Requests\Admin\CurrencyRequest;
use App\Models\Location;
use App\Services\CurrencyService;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function __construct(private CurrencyService $currencyService) {}


    public function index(CurrencyDataTable $dataTable)
    {
        return $dataTable->render('dashboard.currencies.index');
    }

    public function create()
    {
        $countries = Location::where('type', 'country')->get();
        return view('dashboard.currencies.create',compact('countries'));
    }

    public function store(CurrencyRequest $request)
    {
        $this->currencyService->create(
            $request->validated()
        );
        return redirect()->route('currencies.index')->with('success', 'تم إضافة العملة بنجاح');
    }

    public function edit($id)
    {
        $currency = $this->currencyService->getById($id);
        $countries = Location::where('type', 'country')->get();
        return view('dashboard.currencies.edit', compact('currency', 'countries'));
    }

    public function update(CurrencyRequest $request, $id)
    {
        $currency = $this->currencyService->getById($id);
        $this->currencyService->update($currency, $request->validated());
        return redirect()->route('currencies.index')->with('success', 'تم تحديث العملة');
    }

    public function destroy($id)
    {
        try {
            $this->currencyService->delete($id);

            return response()->json([
                'status' => 'success',
                'message' => 'تم حذف العنصر بنجاح.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ أثناء الحذف.'
            ], 500);
        }
    }

}
