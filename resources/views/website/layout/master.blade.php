<!doctype html>

<html lang="{{app()->getLocale()}}" dir="{{app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    @include('website.layout.head')
</head>

<body class="@yield('body')">
<input type="hidden" value="{{URL::to('/')}}" id="base_url">

<!-- header (home page) -->
@include('website.layout.header')
<!-- / header -->

<!-- Page Content -->
@section('website-main') @show
<!-- / Page Content -->

<!-- Footer + Core JS -->
@include('website.layout.footer')
</body>
</html>
