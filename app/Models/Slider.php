<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class Slider extends Model implements HasMedia, Auditable
{
    use HasFactory, HasTranslations, AuditableTrait, InteractsWithMedia;

    public $translatable = ['big_title', 'small_title', 'content'];

    protected $fillable = [
        'big_title',
        'small_title','content', 'active', 'url'
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('slider_image_ar')->singleFile();
        $this->addMediaCollection('slider_image_en')->singleFile();
    }

    public function getLocalizedImageUrlAttribute()
    {
        $locale = app()->getLocale();
        $collection = $locale === 'ar' ? 'slider_image_ar' : 'slider_image_en';
        $url = $this->getFirstMediaUrl($collection);
        
        // Fallback to the other language if current is missing, or legacy 'sliders' collection
        if (!$url) {
            $url = $this->getFirstMediaUrl($locale === 'ar' ? 'slider_image_en' : 'slider_image_ar');
        }
        
        if (!$url) {
            $url = $this->getFirstMediaUrl('sliders');
        }
        
        return $url;
    }
}
