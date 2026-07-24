<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait slugGenerator
{
    protected function generateSlug(array $data): string
    {
        $slugSource = $data['name']['en'] ?? $data['title']['en'] ?? uniqid();
        return $data['slug'] ?? Str::slug($slugSource);
    }
}
