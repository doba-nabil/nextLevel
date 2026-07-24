<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Page;

class PagesTableSeeder extends Seeder
{
    public function run(): void
    {
        Page::create([
            'title' => [
                'en' => 'About Us',
                'ar' => 'من نحن',
            ],
            'content' => [
                'en' => '<p>This is the English content for About Us.</p>',
                'ar' => '<p>هذا هو المحتوى العربي لصفحة من نحن.</p>',
            ],
        ]);
    }
}
