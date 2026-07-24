<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureProfileIsCompleted
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth('web')->user();
        if (!$user) {
            return redirect()->route('website.login');
        }
        if ($user->status == 'pending') {
            return redirect()->route('website.otp.verify')->with('error', 'عضويتك غير مفعلة يرجى تفعيلها عبر البريد');
        }
        return $next($request);
    }
}
