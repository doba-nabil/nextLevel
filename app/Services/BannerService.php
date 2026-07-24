<?php

namespace App\Services;

use App\Models\Banner;
use App\Traits\mediaUploader;

class BannerService
{
    use mediaUploader;

    public function getAll()
    {
        return Banner::all();
    }

    public function getById($id)
    {
        return Banner::findOrFail($id);
    }

    public function create(array $data, $imageAr = null, $imageEn = null)
    {
        $model = Banner::create($data);
        if ($imageAr) {
            $this->handleImage($model, $imageAr, false, 'banner_image_ar');
        }
        if ($imageEn) {
            $this->handleImage($model, $imageEn, false, 'banner_image_en');
        }
        return $model;
    }

    public function update(Banner $model, array $data, $imageAr = null, $imageEn = null)
    {
        $model->update($data);
        if ($imageAr) {
            $this->handleImage($model, $imageAr, true, 'banner_image_ar');
        }
        if ($imageEn) {
            $this->handleImage($model, $imageEn, true, 'banner_image_en');
        }
        return $model;
    }

    public function delete($id)
    {
        $model = Banner::findOrFail($id);
        return $model->delete();
    }
}

