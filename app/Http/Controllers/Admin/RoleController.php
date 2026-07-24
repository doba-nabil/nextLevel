<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\CategoryDataTable;
use App\DataTables\RoleDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Http\Requests\Admin\RoleRequest;
use App\Services\CategoryService;
use App\Services\RoleService;
use Illuminate\Http\Request;
use App\Models\Permission;

class RoleController extends Controller
{
    public function __construct(private RoleService $roleService) {}


    public function index(RoleDataTable $dataTable)
    {
        return $dataTable->render('dashboard.roles.index');
    }

    public function create()
    {
        $permissions = Permission::all()->groupBy('group');
        return view('dashboard.roles.create', compact('permissions'));
    }

    public function store(RoleRequest $request)
    {
        $this->roleService->create(
            $request->validated(),
        );
        return redirect()->route('roles.index')->with('success', __('admin.save_success'));
    }

    public function edit($id)
    {
        $role = $this->roleService->getById($id);
        if($role->name == 'super-admin'){
            return redirect()->back();
        }
        $permissions = Permission::all()->groupBy('group');
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        return view('dashboard.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(RoleRequest $request, $id)
    {
        $role = $this->roleService->getById($id);
        $this->roleService->update($role, $request->validated());
        return redirect()->route('roles.index')->with('success', __('admin.update_success'));
    }

    public function destroy($id)
    {
        try {
            $this->roleService->delete($id);

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
