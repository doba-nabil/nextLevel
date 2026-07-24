<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class SetAdminLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get locale from URL segment (admin routes have locale as first segment, then 'admin')
        $locale = $request->segment(1);
        
        // Check if it's a valid locale
        $supportedLocales = array_keys(LaravelLocalization::getSupportedLocales());
        
        if (in_array($locale, $supportedLocales)) {
            // Store admin locale separately
            session(['admin_locale' => $locale]);
        }
        
        // Always use admin locale for admin routes (restore from admin_locale session)
        $adminLocale = session('admin_locale', $locale ?? config('app.locale'));
        
        // Set the locale for this request (LaravelLocalization will also use this)
        session(['locale' => $adminLocale]);
        app()->setLocale($adminLocale);
        
        return $next($request);
    }
}

