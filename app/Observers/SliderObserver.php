<?php

namespace App\Observers;

use App\Models\Slider;
use Illuminate\Support\Facades\Cache;

class SliderObserver
{
    public function saved(Slider $slider)
    {
        Cache::forget('website_sliders');
        Cache::forget('website_sliders_v2');
    }

    public function deleted(Slider $slider)
    {
        Cache::forget('website_sliders');
        Cache::forget('website_sliders_v2');
    }
}

