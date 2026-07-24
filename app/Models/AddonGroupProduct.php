<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddonGroupProduct extends Model
{
    protected $fillable = ['product_id', 'addon_id', 'addon_group_id', 'type', 'order', 'box_id'];
}
