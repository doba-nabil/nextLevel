<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use MannikJ\Laravel\Wallet\Traits\HasWallet;
use App\Notifications\VerifyEmailNotification;

class User extends Authenticatable implements HasMedia, Auditable, MustVerifyEmail
{

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, InteractsWithMedia,AuditableTrait, HasWallet;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'country_id',
        'points',
        'is_admin', 'status', 'lat',
        'long',
        'location_id',
        'address',
        'google_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function city()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function favorites()
    {
        return $this->hasMany(Favourite::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function mainAddress()
    {
        return $this->hasOne(Address::class)->where('is_main', true);
    }

    public function adminNotifications()
    {
        return $this->hasMany(AdminNotification::class, 'admin_id');
    }

    public function unreadNotifications()
    {
        return $this->adminNotifications()->where('is_read', false);
    }

    public function country()
    {
        return $this->belongsTo(Location::class, 'country_id')->where('type', 'country');
    }

    /**
     * Determine if the user has verified their email address.
     * For admin users, always return true to bypass email verification.
     *
     * @return bool
     */
    public function hasVerifiedEmail(): bool
    {
        // Admin users don't need email verification
        if ($this->is_admin == 1) {
            return true;
        }

        return ! is_null($this->email_verified_at);
    }

    /**
     * Send the email verification notification.
     * For website users, we use phone OTP verification instead of email.
     * Only send email verification for admin users if needed.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        // Skip email verification for website users - we use phone OTP instead
        // Only send email verification if explicitly needed (e.g., for admin users)
        if ($this->is_admin == 1) {
            // Admin users don't need verification
            return;
        }

        // For website users, phone verification is used instead
        // Email verification is disabled
        return;
    }
}
