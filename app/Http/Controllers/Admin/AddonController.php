<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\AddonDataTable;
use App\DataTables\AddonTypeDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AddonGroupRequest;
use App\Http\Requests\Admin\AddonRequest;
use App\Models\AddonGroup;
use App\Models\Currency;
use App\Services\AddonGroupService;
use App\Services\AddonService;
use Illuminate\Http\Request;
use App\Imports\AddonsImport;
use Maatwebsite\Excel\Facades\Excel;

class AddonController extends Controller
{
    public function __construct(private AddonService $addonService) {}


    public function index(AddonDataTable $dataTable)
    {
        return $dataTable->render('dashboard.addons.index');
    }

    public function create()
    {
        $addon_groups = AddonGroup::get();
        $currencies = Currency::whereHas('location')->get();
        return view('dashboard.addons.create', compact('addon_groups', 'currencies'));
    }

    public function store(AddonRequest $request)
    {
        $this->addonService->create(
            $request->validated()
        );
        return redirect()->route('addons.index')->with('success', __('admin.save_success'));
    }

    public function edit($id)
    {
        $addon = $this->addonService->getById($id);
        $addon_groups = AddonGroup::get();
        $currencies = Currency::whereHas('location')->get();
        return view('dashboard.addons.edit', compact('addon', 'addon_groups', 'currencies'));
    }

    public function update(AddonRequest $request, $id)
    {
        $addon = $this->addonService->getById($id);
        $this->addonService->update($addon, $request->validated());
        return redirect()->route('addons.index')->with('success', __('admin.update_success'));
    }

    public function destroy($id)
    {
        try {
            $this->addonService->delete($id);

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

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        Excel::import(new AddonsImport, $request->file('file'));
        return back()->with('success', '✅');
    }

    public function toggleActive(Request $request, $id)
    {
        try {
            $addon = $this->addonService->getById($id);
            $addon->active = $request->input('active', !$addon->active);
            $addon->save();

            return response()->json([
                'status' => 'success',
                'message' => __('admin.update_success'),
                'active' => $addon->active
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('admin.update_error')
            ], 500);
        }
    }

}
