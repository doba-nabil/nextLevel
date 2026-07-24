<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\CategoryDataTable;
use App\DataTables\OrdersDataTable;
use App\DataTables\UserDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Http\Requests\Admin\UserRequest;
use App\Models\Address;
use App\Services\CategoryService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function __construct(private UserService $userService) {}


    public function index(UserDataTable $dataTable)
    {
        return $dataTable->render('dashboard.users.index');
    }

    public function create()
    {
        return view('dashboard.users.create');
    }

    public function store(UserRequest $request)
    {
        $this->userService->create(
            $request->validated(),
            $request->file('image')
        );
        return redirect()->route('users.index')->with('success', __('admin.save_success'));
    }

    public function edit($id)
    {
        $user = $this->userService->getById($id);
        return view('dashboard.users.edit', compact('user'));
    }

    public function update(UserRequest $request, $id)
    {
        $user = $this->userService->getById($id);
        $this->userService->update($user, $request->validated(), $request->file('image'));
        return redirect()->route('users.index')->with('success', __('admin.update_success'));
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

    public function toggleActive(Request $request, $id)
    {
        try {
            $user = $this->userService->getById($id);
            $isActive = $request->input('active', $user->status !== 'active');
            
            // Toggle status: if active is true, set status to 'active', otherwise set to 'deactive'
            $user->status = $isActive ? 'active' : 'deactive';
            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => __('admin.update_success'),
                'active' => $user->status === 'active'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('admin.update_error')
            ], 500);
        }
    }

    public function addresses($id)
    {
        $user = $this->userService->getById($id);
        $addresses = Address::where('user_id', $user->id)
            ->orderBy('is_main', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboard.users.addresses', compact('user', 'addresses'));
    }

    public function orders(Request $request, $id, OrdersDataTable $dataTable)
    {
        $user = $this->userService->getById($id);
        return $dataTable->with('user_id', $user->id)->render('dashboard.users.orders', compact('user'));
    }

    public function storeAddress(Request $request, $id)
    {
        $user = $this->userService->getById($id);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'address' => 'nullable|string',
            'country' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'state_name' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'city_name' => 'nullable|string|max:255',
            'area' => 'nullable|string|max:255',
            'block' => 'nullable|string|max:255',
            'street' => 'nullable|string|max:255',
            'avenue' => 'nullable|string|max:255',
            'building' => 'nullable|string|max:255',
            'floor' => 'nullable|string|max:255',
            'apartment' => 'nullable|string|max:255',
            'additional_directions' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_main' => 'nullable|boolean',
        ]);

        // Build address string from components if address is not provided
        if (empty($validated['address'])) {
            $addressParts = [];
            if (!empty($validated['block'])) $addressParts[] = __('admin.block') . ': ' . $validated['block'];
            if (!empty($validated['street'])) $addressParts[] = __('admin.street') . ': ' . $validated['street'];
            if (!empty($validated['avenue'])) $addressParts[] = __('admin.avenue') . ': ' . $validated['avenue'];
            if (!empty($validated['building'])) $addressParts[] = __('admin.building') . ': ' . $validated['building'];
            if (!empty($validated['floor'])) $addressParts[] = __('admin.floor') . ': ' . $validated['floor'];
            if (!empty($validated['apartment'])) $addressParts[] = __('admin.apartment') . ': ' . $validated['apartment'];
            $validated['address'] = implode(', ', $addressParts);
        }

        // Use state_name and city_name if state and city are IDs
        if (!empty($validated['state_name'])) {
            $validated['state'] = $validated['state_name'];
        }
        if (!empty($validated['city_name'])) {
            $validated['city'] = $validated['city_name'];
        }

        $validated['user_id'] = $user->id;
        $validated['active'] = true;

        // If this is set as main, unset other main addresses
        if ($request->has('is_main') && $request->is_main) {
            Address::where('user_id', $user->id)->update(['is_main' => false]);
            $validated['is_main'] = true;
        } else {
            $validated['is_main'] = false;
        }

        Address::create($validated);

        return redirect()->route('users.addresses', $user->id)->with('success', __('admin.add_success'));
    }

    public function showAddress($userId, $addressId)
    {
        $user = $this->userService->getById($userId);
        $address = Address::where('user_id', $user->id)->findOrFail($addressId);

        return response()->json([
            'success' => true,
            'address' => $address
        ]);
    }

    public function updateAddress(Request $request, $userId, $addressId)
    {
        $user = $this->userService->getById($userId);
        $address = Address::where('user_id', $user->id)->findOrFail($addressId);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'address' => 'nullable|string',
            'country' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'state_name' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'city_name' => 'nullable|string|max:255',
            'area' => 'nullable|string|max:255',
            'block' => 'nullable|string|max:255',
            'street' => 'nullable|string|max:255',
            'avenue' => 'nullable|string|max:255',
            'building' => 'nullable|string|max:255',
            'floor' => 'nullable|string|max:255',
            'apartment' => 'nullable|string|max:255',
            'additional_directions' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_main' => 'nullable|boolean',
        ]);

        // Build address string from components if address is not provided
        if (empty($validated['address'])) {
            $addressParts = [];
            if (!empty($validated['block'])) $addressParts[] = __('admin.block') . ': ' . $validated['block'];
            if (!empty($validated['street'])) $addressParts[] = __('admin.street') . ': ' . $validated['street'];
            if (!empty($validated['avenue'])) $addressParts[] = __('admin.avenue') . ': ' . $validated['avenue'];
            if (!empty($validated['building'])) $addressParts[] = __('admin.building') . ': ' . $validated['building'];
            if (!empty($validated['floor'])) $addressParts[] = __('admin.floor') . ': ' . $validated['floor'];
            if (!empty($validated['apartment'])) $addressParts[] = __('admin.apartment') . ': ' . $validated['apartment'];
            $validated['address'] = implode(', ', $addressParts);
        }

        // Use state_name and city_name if state and city are IDs
        if (!empty($validated['state_name'])) {
            $validated['state'] = $validated['state_name'];
        }
        if (!empty($validated['city_name'])) {
            $validated['city'] = $validated['city_name'];
        }

        // If this is set as main, unset other main addresses
        if ($request->has('is_main') && $request->is_main) {
            Address::where('user_id', $user->id)
                ->where('id', '!=', $address->id)
                ->update(['is_main' => false]);
            $validated['is_main'] = true;
        } else {
            // If unchecking main, keep current value if it was main
            if (!$address->is_main) {
                $validated['is_main'] = false;
            }
        }

        $address->update($validated);

        return redirect()->route('users.addresses', $user->id)->with('success', __('admin.update_success'));
    }

    public function destroyAddress($userId, $addressId)
    {
        $user = $this->userService->getById($userId);
        $address = Address::where('user_id', $user->id)->findOrFail($addressId);
        $address->delete();

        return response()->json([
            'status' => 'success',
            'message' => __('admin.delete_success')
        ]);
    }

    public function setMainAddress($userId, $addressId)
    {
        $user = $this->userService->getById($userId);
        $address = Address::where('user_id', $user->id)->findOrFail($addressId);
        $address->setAsMain();

        return response()->json([
            'status' => 'success',
            'message' => __('admin.update_success')
        ]);
    }

    /**
     * Convert user points to wallet (Admin function)
     */
    public function convertPointsToWallet(Request $request, $id)
    {
        $user = $this->userService->getById($id);
        
        // Get settings
        $pointsPerKd = (float) \App\Models\Setting::getValue('points_per_kd', null, 100);
        $minimumPointsToConvert = (int) \App\Models\Setting::getValue('minimum_points_to_convert', null, 100);
        
        // Validate settings
        if ($pointsPerKd <= 0) {
            return response()->json([
                'success' => false,
                'message' => __('admin.points_conversion_not_configured')
            ], 400);
        }
        
        // Check user has points
        $userPoints = $user->points ?? 0;
        if ($userPoints <= 0) {
            return response()->json([
                'success' => false,
                'message' => __('admin.user_has_no_points')
            ], 400);
        }
        
        try {
            DB::beginTransaction();
            
            // Calculate amount in KD
            $amountInKd = $userPoints / $pointsPerKd;
            
            // Ensure wallet exists
            $wallet = $user->wallet;
            if (!$wallet) {
                $wallet = $user->createWallet();
            }
            
            // Add money to wallet
            $transaction = $wallet->deposit($amountInKd, [
                'notes' => __('admin.points_converted_to_wallet_by_admin', [
                    'points' => $userPoints,
                    'amount' => number_format($amountInKd, 3)
                ])
            ]);
            
            // Deduct points from user
            $user->points = 0;
            $user->save();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => __('admin.points_converted_successfully', [
                    'points' => $userPoints,
                    'amount' => number_format($amountInKd, 3)
                ]),
                'new_balance' => number_format($wallet->balance, 3),
                'new_points' => 0
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Admin Points Conversion Error', [
                'user_id' => $user->id,
                'admin_id' => auth('admin')->id(),
                'points' => $userPoints,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => __('admin.points_conversion_failed') . ': ' . $e->getMessage()
            ], 500);
        }
    }

}
