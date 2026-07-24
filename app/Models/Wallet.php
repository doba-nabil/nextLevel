<?php

namespace App\Models;

use MannikJ\Laravel\Wallet\Models\Wallet as PluginWallet;

class Wallet extends PluginWallet
{
    public function user()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

}
