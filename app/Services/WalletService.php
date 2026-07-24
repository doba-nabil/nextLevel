<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;

class WalletService
{
    public function getAll()
    {
        return Transaction::all();
    }

    public function getById($id)
    {
        return Transaction::findOrFail($id);
    }

    public function create(array $data)
    {
        $user = User::findOrFail($data['user_id']);
        $method = $data['type'];
        if (! method_exists($user->wallet, $method)) {
            throw new \Exception("Invalid wallet method: {$method}");
        }
        if (!$user->wallet) {
            return [
                'status' => 'error',
                'message' => __('admin.This user does not have a wallet'),
            ];
        }
        try {
            $transaction = $user->wallet->{$method}(
                (float) $data['amount'],
                ['notes' => $data['notes']]
            );
            return [
                'status' => 'success',
                'message' => __('admin.save_success'),
                'transaction' => $transaction,
                'balance' => $user->wallet->balance,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => __('admin.cannot_withraw_negative'),
                'balance' => $user->wallet->balance,
            ];
        }
    }
}
