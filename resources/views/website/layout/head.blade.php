<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
@php
    // Optimize: Use cached setting model
    $settingModel = \App\Models\Setting::getSettingModel();
    $siteName = \App\Models\Setting::getValue('site_name', app()->getLocale(), 'Run2Diet');
    $metaTitle = \App\Models\Setting::getValue('meta_title', app()->getLocale(), '');
    $metaDescription = \App\Models\Setting::getValue('meta_description', app()->getLocale(), '');
    $logoUrl = $settingModel?->getFirstMediaUrl('logo') ?: asset('website/assets/img/logo.png');
    $faviconUrl = $settingModel?->getFirstMediaUrl('favicon') ?: asset('website/assets/img/logo.png');
@endphp
<title>{{ $siteName }}@hasSection('title') | @yield('title')@endif</title>
@if($metaTitle)
    <meta name="title" content="{{ $metaTitle }}">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta name="twitter:title" content="{{ $metaTitle }}">
@else
    <meta property="og:title" content="{{ $siteName }}@hasSection('title') | @yield('title')@endif">
    <meta name="twitter:title" content="{{ $siteName }}@hasSection('title') | @yield('title')@endif">
@endif
@if($metaDescription)
    <meta name="description" content="{{ $metaDescription }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
@endif
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:type" content="website">
<link rel="icon" href="{{ $faviconUrl }}" type="image/png" sizes="16x16">
<link rel="shortcut icon" href="{{ $faviconUrl }}" type="image/png">
<link rel="stylesheet" href="{{ asset('website') }}/assets/css/animate.min.css"  />
<link rel="stylesheet" href="{{ asset('website') }}/assets/css/hover.css" >
<link rel="stylesheet" href="{{ asset('website') }}/assets/css/slick.css" >
<link rel="stylesheet" href="{{ asset('website') }}/assets/css/slick-theme.css" >

@php
    // Get social media pixel code from settings
    $socialMediaPixel = \App\Models\Setting::getValue('social_media_pixel', null, '');
@endphp
@if($socialMediaPixel)
    {!! $socialMediaPixel !!}
@endif
<link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" >
<link rel="stylesheet" href="{{ asset('website') }}/assets/css/jquery.fancybox.min.css" >
<link rel="stylesheet" href="{{ asset('website') }}/assets/css/bootstrap.min.css" >
<link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
<link rel="stylesheet" href="{{ asset('website') }}/assets/css/mobile_menu.css" >
<link rel="stylesheet" href="{{ asset('website') }}/assets/css/main.css?ver=4.6" >
<link rel="stylesheet" href="{{ asset('website') }}/assets/css/website-custom.css?ver=3.6" >
<link rel="stylesheet" href="{{ asset('website') }}/assets/css/payment-icons.css?ver=1.1" >

@section('website-head')
@show
