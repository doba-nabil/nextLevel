<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\CategoryDataTable;
use App\DataTables\UserDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Http\Requests\Admin\UserRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Services\CategoryService;
use App\Services\UserService;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(private UserService $userService) {}

    public function profile_page()
    {
        $user = $this->userService->getById(auth('admin')->id());
        return view('dashboard.profile', compact('user'));
    }

    public function profile_page_post(ProfileUpdateRequest $request)
    {
        $user = $this->userService->getById(auth('admin')->id());
        $this->userService->update($user, $request->validated(), $request->file('image'));
        return redirect()->back()->with('success', __('admin.update_success'));
    }

}
