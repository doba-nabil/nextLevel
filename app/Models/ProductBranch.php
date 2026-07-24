<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductBranch extends Model
{
    protected $fillable = [
        'product_id', 'branch_id', 'status'
    ];
    
    protected $casts = [
        'status' => 'string',
    ];
}
