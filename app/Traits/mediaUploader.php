<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait mediaUploader
{
    protected function handleImage($model, $image = null, bool $replace = false, string $collection = 'default'): void
    {
        if (!$image) {
            return;
        }

        if ($replace) {
            $model->clearMediaCollection($collection);
        }

        $model->addMedia($image)->toMediaCollection($collection);
    }
}
