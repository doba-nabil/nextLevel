<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductNote extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'note'
    ];

    /**
     * Get the product that owns the note.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user that created the note.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
