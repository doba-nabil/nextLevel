<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // For admin login, use email field
        if ($this->isAdminLogin()) {
            return [
                'email' => ['required', 'string', 'email'],
                'password' => ['required', 'string'],
            ];
        }
        
        // For website login, use login field (can be email or phone)
        return [
            'login' => ['required', 'string'], // Can be email or phone
            'password' => ['required', 'string'],
            'country_id' => ['nullable', 'exists:locations,id'], // Required if login is phone
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // For admin login, use email only
        if ($this->isAdminLogin()) {
            $guard = 'admin';
            $isAdmin = 1;
            
            // Get user first to check if admin and bypass email verification
            $user = \App\Models\User::where('email', $this->input('email'))
                ->where('is_admin', $isAdmin)
                ->first();
            
            if (!$user || !\Illuminate\Support\Facades\Hash::check($this->input('password'), $user->password)) {
                RateLimiter::hit($this->throttleKey());
                throw ValidationException::withMessages([
                    'email' => trans('auth.failed'),
                ]);
            }
            
            // Login admin user directly (bypass email verification for admins)
            Auth::guard($guard)->login($user, $this->boolean('remember'));
        } else {
            // For website login, handled in AuthController::login_post
            // This method is not used for website login anymore
            return;
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Check if current login request is for admin panel
     */
    protected function isAdminLogin(): bool
    {
        return $this->is('admin/*') || $this->routeIs('admin.*');
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        $loginField = $this->string('login') ?? $this->string('email') ?? 'unknown';
        return Str::transliterate(Str::lower($loginField).'|'.$this->ip());
    }
}
