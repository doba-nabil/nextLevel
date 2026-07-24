<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;

class SettingController extends Controller
{
    public function changeTheme(Request $request)
    {
        $theme = $request->input('theme');
        if (!in_array($theme, ['light', 'dark'])) {
            return response()->json(['success' => false, 'message' => 'Invalid theme'], 400);
        }
        if (auth()->check()) {
            $user = auth()->user();
            $user->theme_mode = $theme;
            $user->save();
        } else {
            session(['theme' => $theme]);
        }
        return response()->json(['success' => true, 'theme' => $theme]);
    }

    public function get_settings()
    {
        $settingsRaw = Setting::all();

        $settings = [];
        foreach ($settingsRaw as $setting) {
            $decoded = json_decode($setting->translatable_value, true);
            if ($decoded) {
                $settings[$setting->key] = $decoded;
            } else {
                $settings[$setting->key] = $setting->value ?? '';
            }
        }

        $settings_socials = Setting::where('key', 'socials')->get()->pluck('value', 'key')->toArray();
        $socialsJson = $settings_socials['socials'] ?? '[]';
        $socials = json_decode($socialsJson, true);

        $settingModel = Setting::first();
        $logoUrl = $settingModel?->getFirstMediaUrl('logo');
        $faviconUrl = $settingModel?->getFirstMediaUrl('favicon');

        // Get social media pixel value and add it to settings array
        $socialMediaPixel = Setting::getValue('social_media_pixel', null, '');
        $settings['social_media_pixel'] = $socialMediaPixel;

        // Get WhatsApp API settings directly from database to ensure they're loaded
        $whatsappApiKey = Setting::where('key', 'whatsapp_api_key')->first();
        $whatsappPhoneNumberId = Setting::where('key', 'whatsapp_phone_number_id')->first();

        $settings['whatsapp_api_key'] = $whatsappApiKey ? ($whatsappApiKey->value ?? '') : '';
        $settings['whatsapp_phone_number_id'] = $whatsappPhoneNumberId ? ($whatsappPhoneNumberId->value ?? '') : '';

        return view('dashboard.settings.settings', compact(
            'settings', 'logoUrl', 'faviconUrl', 'socials'
        ));
    }

    public function update(Request $request)
    {
        foreach (['site_name','meta_title','meta_description'] as $key) {
            Setting::setValue($key, $request->input($key), true);
        }
        foreach (['contact_email','contact_phone'] as $key) {
            Setting::setValue($key, $request->input($key));
        }
        $settingModel = Setting::firstOrCreate(['key' => 'media']);
        if ($request->hasFile('logo')) {
            $settingModel->clearMediaCollection('logo');
            $settingModel->addMedia($request->file('logo'))->toMediaCollection('logo');
        }
        if ($request->hasFile('favicon')) {
            $settingModel->clearMediaCollection('favicon');
            $settingModel->addMedia($request->file('favicon'))->toMediaCollection('favicon');
        }
        Setting::setValue('socials', json_encode($request->input('socials', [])));

        // Handle download section settings
        if ($request->has('dowonload_title')) {
            Setting::setValue('dowonload_title', $request->input('dowonload_title'), true);
        }
        if ($request->has('dowonload_content')) {
            Setting::setValue('dowonload_content', $request->input('dowonload_content'), true);
        }
        if ($request->has('ios_url')) {
            Setting::setValue('ios_url', $request->input('ios_url'));
        }
        if ($request->has('android_url')) {
            Setting::setValue('android_url', $request->input('android_url'));
        }

        // Handle social media pixel
        Setting::setValue('social_media_pixel', $request->input('social_media_pixel', ''));

        // Handle WhatsApp API settings (Facebook WhatsApp Business API)
        Setting::setValue('whatsapp_api_key', $request->input('whatsapp_api_key', ''));
        Setting::setValue('whatsapp_phone_number_id', $request->input('whatsapp_phone_number_id', ''));

        // Handle product notes setting
        Setting::setValue('enable_product_notes', $request->has('enable_product_notes') ? '1' : '0');

        // Handle points settings
        if ($request->has('points_per_order_value')) {
            Setting::setValue('points_per_order_value', $request->input('points_per_order_value'));
        }

        // Handle points to wallet conversion settings
        if ($request->has('points_per_kd')) {
            Setting::setValue('points_per_kd', $request->input('points_per_kd'));
        }
        if ($request->has('minimum_points_to_convert')) {
            Setting::setValue('minimum_points_to_convert', $request->input('minimum_points_to_convert'));
        }

        // Clear all cache after saving settings
        Cache::flush();
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');

        return redirect()->back()->with('success',__('admin.update_success'));
    }

    public function getHomePageSettings()
    {
        // Get banner settings
        $bannerSettingModel = Setting::where('key', 'home_banner')->first();
        $bannerImageUrl = $bannerSettingModel?->getFirstMediaUrl('banner_image');
        $showBannerSection = Setting::getValue('show_banner_section', null, '1') == '1';

        // Get all section visibility settings (desktop and mobile)
        $showSliderSectionDesktop = Setting::getValue('show_slider_section_desktop', null, '1') == '1';
        $showSliderSectionMobile = Setting::getValue('show_slider_section_mobile', null, '1') == '1';
        $showCategoriesSectionDesktop = Setting::getValue('show_categories_section_desktop', null, '1') == '1';
        $showCategoriesSectionMobile = Setting::getValue('show_categories_section_mobile', null, '1') == '1';
        $showMenuSectionDesktop = Setting::getValue('show_menu_section_desktop', null, '1') == '1';
        $showMenuSectionMobile = Setting::getValue('show_menu_section_mobile', null, '1') == '1';
        $showPickupSectionDesktop = Setting::getValue('show_pickup_section_desktop', null, '1') == '1';
        $showPickupSectionMobile = Setting::getValue('show_pickup_section_mobile', null, '1') == '1';
        $showNewPlatesSectionDesktop = Setting::getValue('show_new_plates_section_desktop', null, '1') == '1';
        $showNewPlatesSectionMobile = Setting::getValue('show_new_plates_section_mobile', null, '1') == '1';
        $showBoxesSectionDesktop = Setting::getValue('show_boxes_section_desktop', null, '1') == '1';
        $showBoxesSectionMobile = Setting::getValue('show_boxes_section_mobile', null, '1') == '1';
        $showOffersSectionDesktop = Setting::getValue('show_offers_section_desktop', null, '1') == '1';
        $showOffersSectionMobile = Setting::getValue('show_offers_section_mobile', null, '1') == '1';
        $showTrendingSectionDesktop = Setting::getValue('show_trending_section_desktop', null, '1') == '1';
        $showTrendingSectionMobile = Setting::getValue('show_trending_section_mobile', null, '1') == '1';
        $showBurgerSectionDesktop = Setting::getValue('show_burger_section_desktop', null, '1') == '1';
        $showBurgerSectionMobile = Setting::getValue('show_burger_section_mobile', null, '1') == '1';

        return view('dashboard.settings.home-page', compact(
            'bannerImageUrl', 'showBannerSection',
            'showSliderSectionDesktop', 'showSliderSectionMobile',
            'showCategoriesSectionDesktop', 'showCategoriesSectionMobile',
            'showMenuSectionDesktop', 'showMenuSectionMobile',
            'showPickupSectionDesktop', 'showPickupSectionMobile',
            'showNewPlatesSectionDesktop', 'showNewPlatesSectionMobile',
            'showBoxesSectionDesktop', 'showBoxesSectionMobile',
            'showOffersSectionDesktop', 'showOffersSectionMobile',
            'showTrendingSectionDesktop', 'showTrendingSectionMobile',
            'showBurgerSectionDesktop', 'showBurgerSectionMobile'
        ));
    }

    public function updateHomePageSettings(Request $request)
    {
        // Handle banner settings
        if ($request->has('banner_title')) {
            Setting::setValue('banner_title', $request->input('banner_title'), true);
        }
        if ($request->has('banner_subtitle')) {
            Setting::setValue('banner_subtitle', $request->input('banner_subtitle'), true);
        }
        if ($request->has('banner_content')) {
            Setting::setValue('banner_content', $request->input('banner_content'), true);
        }
        if ($request->has('banner_link')) {
            Setting::setValue('banner_link', $request->input('banner_link'));
        }

        $bannerSettingModel = Setting::firstOrCreate(['key' => 'home_banner']);
        if ($request->hasFile('banner_image')) {
            $bannerSettingModel->clearMediaCollection('banner_image');
            $bannerSettingModel->addMedia($request->file('banner_image'))->toMediaCollection('banner_image');
        }

        // Handle banner section visibility (checkbox sends '1' when checked, nothing when unchecked)
        $showBannerSection = $request->has('show_banner_section') ? '1' : '0';
        Setting::setValue('show_banner_section', $showBannerSection);

        // Handle all section visibility settings (desktop and mobile)
        $showSliderSectionDesktop = $request->has('show_slider_section_desktop') ? '1' : '0';
        Setting::setValue('show_slider_section_desktop', $showSliderSectionDesktop);
        $showSliderSectionMobile = $request->has('show_slider_section_mobile') ? '1' : '0';
        Setting::setValue('show_slider_section_mobile', $showSliderSectionMobile);

        $showCategoriesSectionDesktop = $request->has('show_categories_section_desktop') ? '1' : '0';
        Setting::setValue('show_categories_section_desktop', $showCategoriesSectionDesktop);
        $showCategoriesSectionMobile = $request->has('show_categories_section_mobile') ? '1' : '0';
        Setting::setValue('show_categories_section_mobile', $showCategoriesSectionMobile);

        $showMenuSectionDesktop = $request->has('show_menu_section_desktop') ? '1' : '0';
        Setting::setValue('show_menu_section_desktop', $showMenuSectionDesktop);
        $showMenuSectionMobile = $request->has('show_menu_section_mobile') ? '1' : '0';
        Setting::setValue('show_menu_section_mobile', $showMenuSectionMobile);

        $showPickupSectionDesktop = $request->has('show_pickup_section_desktop') ? '1' : '0';
        Setting::setValue('show_pickup_section_desktop', $showPickupSectionDesktop);
        $showPickupSectionMobile = $request->has('show_pickup_section_mobile') ? '1' : '0';
        Setting::setValue('show_pickup_section_mobile', $showPickupSectionMobile);

        $showNewPlatesSectionDesktop = $request->has('show_new_plates_section_desktop') ? '1' : '0';
        Setting::setValue('show_new_plates_section_desktop', $showNewPlatesSectionDesktop);
        $showNewPlatesSectionMobile = $request->has('show_new_plates_section_mobile') ? '1' : '0';
        Setting::setValue('show_new_plates_section_mobile', $showNewPlatesSectionMobile);

        $showBoxesSectionDesktop = $request->has('show_boxes_section_desktop') ? '1' : '0';
        Setting::setValue('show_boxes_section_desktop', $showBoxesSectionDesktop);
        $showBoxesSectionMobile = $request->has('show_boxes_section_mobile') ? '1' : '0';
        Setting::setValue('show_boxes_section_mobile', $showBoxesSectionMobile);

        $showOffersSectionDesktop = $request->has('show_offers_section_desktop') ? '1' : '0';
        Setting::setValue('show_offers_section_desktop', $showOffersSectionDesktop);
        $showOffersSectionMobile = $request->has('show_offers_section_mobile') ? '1' : '0';
        Setting::setValue('show_offers_section_mobile', $showOffersSectionMobile);

        $showTrendingSectionDesktop = $request->has('show_trending_section_desktop') ? '1' : '0';
        Setting::setValue('show_trending_section_desktop', $showTrendingSectionDesktop);
        $showTrendingSectionMobile = $request->has('show_trending_section_mobile') ? '1' : '0';
        Setting::setValue('show_trending_section_mobile', $showTrendingSectionMobile);

        $showBurgerSectionDesktop = $request->has('show_burger_section_desktop') ? '1' : '0';
        Setting::setValue('show_burger_section_desktop', $showBurgerSectionDesktop);
        $showBurgerSectionMobile = $request->has('show_burger_section_mobile') ? '1' : '0';
        Setting::setValue('show_burger_section_mobile', $showBurgerSectionMobile);

        // Clear cache after saving settings
        Cache::flush();
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');

        return redirect()->back()->with('success', __('admin.update_success'));
    }

}
