<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        Setting::setValue('site_name', ['en' => 'My Website', 'ar' => 'موقعي'], true);
        Setting::setValue('meta_title', ['en' => 'My Website', 'ar' => 'عنوان موقعي'], true);
        Setting::setValue('meta_description', ['en' => 'Website description', 'ar' => 'وصف الموقع'], true);
        Setting::setValue('contact_email', 'info@example.com');
        Setting::setValue('contact_phone', '+20123456789');
        Setting::setValue('ios_url', '#');
        Setting::setValue('android_url', '#');
        Setting::setValue('dowonload_title', ['en' => 'Download our app now', 'ar' => 'Download our app now '], true);
        Setting::setValue('dowonload_content', ['en' => 'With our app, ordering is faster, smoother, and more convenient than ever. Save your favorite items, track your delivery in real-time, and enjoy exclusive app-only offers keeping everything at your fingertips. Download now and experience instant satisfaction.', 'ar' => 'With our app, ordering is faster, smoother, and more convenient than ever. Save your favorite items, track your delivery in real-time, and enjoy exclusive app-only offers keeping everything at your fingertips. Download now and experience instant satisfaction.'], true);
        Setting::setValue('pick_up_content', ['en' => 'Your Pickup Menu offers a curated selection of freshly prepared meals ready for same-day collection.
Choose your favourite dishes online, select a convenient time slot, and swing by to pick them up—no dine-in.', 'ar' => 'Your Pickup Menu offers a curated selection of freshly prepared meals ready for same-day collection. Choose your favourite dishes online, select a convenient time slot, and swing by to pick them up—no dine-in.'], true);

        Setting::setValue('home_banner_title', ['en' => 'Fresh Energy', 'ar' => 'Fresh Energy'], true);
        Setting::setValue('home_banner_subtitle', ['en' => 'Bold Flavor', 'ar' => 'Bold Flavor'], true);
        Setting::setValue('home_banner_content', ['en' => 'Try the Passion Fruit Slush from Run2Diet, packed with 30g of protein 💪, bursting with tropical vibes 🍍❄, and best of all, zero sugar 👌', 'ar' => 'Try the Passion Fruit Slush from Run2Diet, packed with 30g of protein 💪, bursting with tropical vibes 🍍❄, and best of all, zero sugar 👌'], true);

        Setting::setValue('home_banner_title', ['en' => 'Every burger on this menu has its own unique flavor 😋🔥
So, what\'s your style? Are you a classic lover, or do you crave a bit of adventure?', 'ar' => 'Every burger on this menu has its own unique flavor 😋🔥
So, what\'s your style? Are you a classic lover, or do you crave a bit of adventure?'], true);


    }
}
