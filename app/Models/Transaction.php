<?php

namespace App\Models;

use MannikJ\Laravel\Wallet\Models\Transaction as PluginTransaction;

class Transaction extends PluginTransaction
{
    public function user()
    {
        return $this->hasOneThrough(
            User::class,
            Wallet::class,
            'id',
            'id',
            'wallet_id',
            'owner_id',
        )->where('owner_type',User::class);
    }
}
