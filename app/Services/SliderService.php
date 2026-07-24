<?php

namespace App\Services;

use App\Models\Slider;
use App\Traits\mediaUploader;
use App\Traits\slugGenerator;

use Illuminate\Support\Facades\Cache;

class SliderService
{
    use mediaUploader, slugGenerator;

    public function getAll()
    {
        return Slider::all();
    }

    public function getById($id)
    {
        return Slider::findOrFail($id);
    }

    public function create(array $data, array $images = [])
    {
        // Ensure nullable fields are strings if empty
        foreach (['small_title', 'big_title', 'content'] as $field) {
            if (!isset($data[$field])) {
                $data[$field] = ['en' => '', 'ar' => ''];
            }
        }
        
        $model = Slider::create($data);
        
        if (isset($images['image_ar'])) {
            $this->handleImage($model, $images['image_ar'], false, 'slider_image_ar');
        }
        
        if (isset($images['image_en'])) {
            $this->handleImage($model, $images['image_en'], false, 'slider_image_en');
        }
        
        // Handle legacy image if present
        if (isset($images['image'])) {
            $this->handleImage($model, $images['image'], false, 'sliders');
        }
        
        Cache::forget('website_sliders');
        Cache::forget('website_sliders_v2');
        
        return $model;
    }

    public function update(Slider $model, array $data, array $images = [])
    {
         // Ensure nullable fields are strings if empty
        foreach (['small_title', 'big_title', 'content'] as $field) {
            if (!isset($data[$field])) {
                $data[$field] = ['en' => '', 'ar' => ''];
            }
        }
        
        $model->update($data);
        
        if (isset($images['image_ar'])) {
            $this->handleImage($model, $images['image_ar'], true, 'slider_image_ar');
        }
        
        if (isset($images['image_en'])) {
            $this->handleImage($model, $images['image_en'], true, 'slider_image_en');
        }
        
        // Handle legacy image if present
        if (isset($images['image'])) {
            $this->handleImage($model, $images['image'], true, 'sliders');
        }
        
        Cache::forget('website_sliders');
        Cache::forget('website_sliders_v2');
        
        return $model;
    }


    public function delete($id)
    {
        $model = Slider::findOrFail($id);
        $result = $model->delete();
        Cache::forget('website_sliders');
        Cache::forget('website_sliders_v2');
        return $result;
    }
}
