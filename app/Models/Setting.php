<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Translatable\HasTranslations;
use Spatie\MediaLibrary\InteractsWithMedia;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Setting extends Model implements HasMedia, Auditable
{
    use HasTranslations, InteractsWithMedia,AuditableTrait;

    protected $fillable = ['key', 'value', 'translatable_value'];

    public $translatable = ['translatable_value'];

    public static function getValue($key, $locale = null, $default = null)
    {
        $locale = $locale ?? app()->getLocale();
        $cacheKey = "setting_{$key}_{$locale}";
        
        return Cache::remember($cacheKey, 3600, function () use ($key, $locale, $default) {
            $setting = self::where('key', $key)->first();
            if (!$setting) return $default;

            if ($setting->translatable_value) {
                if (method_exists($setting, 'getTranslation')) {
                    return $setting->getTranslation('translatable_value', $locale);
                } else {
                    $decoded = json_decode($setting->translatable_value, true);
                    return $decoded[$locale] ?? $default;
                }
            }

            return $setting->value ?? $default;
        });
    }
    
    /**
     * Get setting model with caching
     */
    public static function getSettingModel()
    {
        return Cache::remember('setting_model', 3600, function () {
            return self::first();
        });
    }

    public static function setValue($key, $value, $isTranslatable = false)
    {
        $result = null;
        if ($isTranslatable) {
            $result = self::updateOrCreate(
                ['key' => $key],
                ['translatable_value' => $value]
            );
        } else {
            $result = self::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
        
        // Clear cache for this setting
        $locales = ['ar', 'en'];
        foreach ($locales as $locale) {
            Cache::forget("setting_{$key}_{$locale}");
        }
        Cache::forget('setting_model');
        
        return $result;
    }
    
    protected static function booted()
    {
        // Clear cache when settings are updated
        static::saved(function ($setting) {
            $locales = ['ar', 'en'];
            foreach ($locales as $locale) {
                Cache::forget("setting_{$setting->key}_{$locale}");
            }
            Cache::forget('setting_model');
        });
        
        static::deleted(function ($setting) {
            $locales = ['ar', 'en'];
            foreach ($locales as $locale) {
                Cache::forget("setting_{$setting->key}_{$locale}");
            }
            Cache::forget('setting_model');
        });
    }
}

