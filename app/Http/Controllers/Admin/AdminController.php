<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\AdminDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminRequest;
use App\Models\Role;
use App\Services\AdminService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct(private AdminService $userService) {}


    public function index(AdminDataTable $dataTable)
    {
        return $dataTable->render('dashboard.admins.index');
    }

    public function create()
    {
        $roles = Role::all();
        return view('dashboard.admins.create', compact('roles'));
    }

    public function store(AdminRequest $request)
    {
        $this->userService->create(
            $request->validated(),
            $request->file('image')
        );
        return redirect()->route('admins.index')->with('success', __('admin.save_success'));
    }

    public function edit($id)
    {
        $user = $this->userService->getById($id);
        $roles = Role::all();
        $adminRoles = $user->roles->pluck('id')->toArray();
        return view('dashboard.admins.edit', compact('user', 'roles', 'adminRoles'));
    }

    public function update(AdminRequest $request, $id)
    {
        $user = $this->userService->getById($id);
        $this->userService->update($user, $request->validated(), $request->file('image'));
        return redirect()->route('admins.index')->with('success', __('admin.upadte_success'));
    }

    public function destroy($id)
    {
        try {
            $this->userService->delete($id);

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
