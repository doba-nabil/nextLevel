<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class SetWebsiteLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get locale from URL segment
        $locale = $request->segment(1);
        
        // Check if it's a valid locale
        $supportedLocales = array_keys(LaravelLocalization::getSupportedLocales());
        
        if (in_array($locale, $supportedLocales)) {
            // Store website locale separately
            session(['website_locale' => $locale]);
        }
        
        // Always use website locale for website routes (restore from website_locale session)
        $websiteLocale = session('website_locale', $locale ?? config('app.locale'));
        
        // Set the locale for this request (LaravelLocalization will also use this)
        session(['locale' => $websiteLocale]);
        app()->setLocale($websiteLocale);
        
        return $next($request);
    }
}

