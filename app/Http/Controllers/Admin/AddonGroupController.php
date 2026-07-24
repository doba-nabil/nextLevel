<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\AddonTypeDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AddonGroupRequest;
use App\Services\AddonGroupService;
use Illuminate\Http\Request;

class AddonGroupController extends Controller
{
    public function __construct(private AddonGroupService $addonGroupService) {}


    public function index(AddonTypeDataTable $dataTable)
    {
        return $dataTable->render('dashboard.addon_groups.index');
    }

    public function create()
    {
        return view('dashboard.addon_groups.create');
    }

    public function store(AddonGroupRequest $request)
    {
        $this->addonGroupService->create(
            $request->validated()
        );
        return redirect()->route('addon_groups.index')->with('success', __('admin.save_success'));
    }

    public function edit($id)
    {
        $group = $this->addonGroupService->getById($id);
        return view('dashboard.addon_groups.edit', compact('group'));
    }

    public function update(AddonGroupRequest $request, $id)
    {
        $group = $this->addonGroupService->getById($id);
        $this->addonGroupService->update($group, $request->validated());
        return redirect()->route('addon_groups.index')->with('success', __('admin.update_success'));
    }

    public function destroy($id)
    {
        try {
            $this->addonGroupService->delete($id);

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

}
