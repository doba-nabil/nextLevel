<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\WalletDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\WalletRequest;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function __construct(private WalletService $walletService)
    {
        $this->middleware('permission:users.index')->only('index');
        $this->middleware('permission:users.create')->only(['create', 'store']);
    }


    public function index(WalletDataTable $dataTable)
    {
        $userId = request('user_id', null);
        return $dataTable->with('user_id', $userId)
            ->render('dashboard.wallets.index');
    }

    public function create()
    {
        $users = User::where('is_admin', 0)->get();
        return view('dashboard.wallets.create', compact('users'));
    }

    public function store(WalletRequest $request)
    {
        $result = $this->walletService->create($request->validated());

        if ($result['status'] === 'error') {
            return redirect()->back()->with('error', $result['message']);
        }
        return redirect()->route('wallets.index')->with('success', $result['message']);
    }
}
