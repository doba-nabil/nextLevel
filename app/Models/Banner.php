<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Banner extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'title',
        'subtitle',
        'content',
        'link',
        'order',
        'active',
    ];

    protected $casts = [
        'title' => 'array',
        'subtitle' => 'array',
        'content' => 'array',
        'active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Register media collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('banner_image_ar')
            ->singleFile();
        $this->addMediaCollection('banner_image_en')
            ->singleFile();
    }
    
    /**
     * Get localized banner image URL
     */
    public function getLocalizedImageUrlAttribute()
    {
        $locale = app()->getLocale();
        $collection = $locale === 'ar' ? 'banner_image_ar' : 'banner_image_en';
        return $this->getFirstMediaUrl($collection);
    }

    /**
     * Scope to get active banners
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope to order by order field
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc')->orderBy('created_at', 'desc');
    }

    /**
     * Get localized title
     */
    public function getLocalizedTitleAttribute()
    {
        $locale = app()->getLocale();
        $title = $this->title;
        
        if (is_array($title)) {
            return $title[$locale] ?? $title['en'] ?? $title['ar'] ?? '';
        }
        
        return $title ?? '';
    }

    /**
     * Get localized subtitle
     */
    public function getLocalizedSubtitleAttribute()
    {
        $locale = app()->getLocale();
        $subtitle = $this->subtitle;
        
        if (is_array($subtitle)) {
            return $subtitle[$locale] ?? $subtitle['en'] ?? $subtitle['ar'] ?? '';
        }
        
        return $subtitle ?? '';
    }

    /**
     * Get localized content
     */
    public function getLocalizedContentAttribute()
    {
        $locale = app()->getLocale();
        $content = $this->content;
        
        if (is_array($content)) {
            return $content[$locale] ?? $content['en'] ?? $content['ar'] ?? '';
        }
        
        return $content ?? '';
    }
}

