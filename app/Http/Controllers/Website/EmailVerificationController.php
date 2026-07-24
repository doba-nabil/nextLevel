<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class EmailVerificationController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('website.home'))->with('success', __('website.email_already_verified'));
        }

        if ($request->user()->markEmailAsVerified()) {
            // Update user status to active
            $request->user()->update(['status' => 'active']);
            event(new Verified($request->user()));
        }

        return redirect()->intended(route('website.home'))->with('success', __('website.email_verified_successfully'));
    }
}
