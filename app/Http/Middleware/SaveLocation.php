<?php

namespace App\Http\Middleware;

use App\Models\Currency;
use Closure;
use Illuminate\Http\Request;
use App\Models\Location;
use Symfony\Component\HttpFoundation\Response;

class SaveLocation
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->has(['lat', 'long'])) {
            $lat = $request->lat;
            $lng = $request->long;
            session(['lat' => $lat, 'longitude' => $lng]);
            if (auth('web')->check()) {
                $user = auth('web')->user();
                if ($user->latitude !== $lat || $user->long !== $lng) {
                    $user->update([
                        'lat' => $lat,
                        'long' => $lng,
                    ]);
                }
            }
        } elseif (auth('web')->check() && !session()->has('lat')) {
            $user = auth('web')->user();
            if ($user->lat && $user->long) {
                session([
                    'lat' => $user->lat,
                    'long' => $user->long,
                ]);
            }
        }
        if (!$request->session()->has('currency')) {

            $countryId = session('location.country');
            $currency = null;

            if ($countryId) {
                $country = Location::where('id', $countryId)
                    ->where('active', 1)
                    ->with('currency')
                    ->first();

                $currency = $country?->currency?->key;
            }
            if (!$currency) {
                $currency = Currency::whereHas('location', function($q){
                    $q->where('active', 1);
                })
                    ->first()
                    ?->key;
            }

            session()->put('currency', $currency);
        } else {
            $sessionCurrency = session('currency');
            $validCurrency = Currency::where('key', $sessionCurrency)
                ->whereHas('location', function($q){
                    $q->where('active', 1);
                })
                ->first();

            if (!$validCurrency) {
                $fallback = Currency::whereHas('location', function($q){
                    $q->where('active', 1);
                })
                    ->first()
                    ?->key;

                session()->put('currency', $fallback);
            }
        }


        if (auth('web')->check()) {
            $user = auth('web')->user();
            $sessionLocation = $request->session()->get('user_location');
            if ($sessionLocation && (isset($sessionLocation['city_id']) || isset($sessionLocation['city']))) {
                // Prefer city_id if available, otherwise try to find city by name
                $cityId = null;
                
                if (isset($sessionLocation['city_id']) && is_numeric($sessionLocation['city_id'])) {
                    $cityId = (int) $sessionLocation['city_id'];
                } elseif (isset($sessionLocation['city'])) {
                    // If city is numeric, use it as ID
                    if (is_numeric($sessionLocation['city'])) {
                        $cityId = (int) $sessionLocation['city'];
                    } else {
                        // If city is a name, look it up
                        $city = Location::where('type', 'city')
                            ->whereRaw('JSON_UNQUOTE(JSON_EXTRACT(name, "$.' . app()->getLocale() . '")) = ?', [$sessionLocation['city']])
                            ->first();
                        if ($city) {
                            $cityId = $city->id;
                        }
                    }
                }
                
                // Only update if we have a valid city ID and it's different
                if ($cityId && $cityId > 0 && $user->location_id != $cityId) {
                    $user->location_id = $cityId;
                    $user->save();
                }
            } else {
                $request->session()->put('user_location', [
                    'country' => $user->country_id,
                    'state'   => $user->state_id,
                    'city_id' => $user->location_id,
                ]);
            }
        }

        return $next($request);
    }
}
