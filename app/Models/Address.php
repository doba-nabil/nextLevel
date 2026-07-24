<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'address',
        'country',
        'state',
        'city',
        'area',
        'block',
        'street',
        'avenue',
        'building',
        'floor',
        'apartment',
        'additional_directions',
        'latitude',
        'longitude',
        'is_main',
        'active',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_main' => 'boolean',
        'active' => 'boolean',
    ];

    /**
     * Get the user that owns the address.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Set as main address and unset others
     */
    public function setAsMain(): void
    {
        // Unset other main addresses for this user
        static::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->update(['is_main' => false]);

        // Set this as main
        $this->update(['is_main' => true]);
    }

    /**
     * Scope to get main address
     */
    public function scopeMain($query)
    {
        return $query->where('is_main', true);
    }

    /**
     * Scope to get active addresses
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Get full address string
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->area,
            $this->block,
            $this->street,
            $this->avenue,
            $this->building,
            $this->floor ? 'Floor: ' . $this->floor : null,
            $this->apartment ? 'Apt: ' . $this->apartment : null,
            $this->city,
            $this->state,
            $this->country,
        ]);

        return implode(', ', $parts);
    }
}

