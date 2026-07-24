<!doctype html>

<html
    lang="{{app()->getLocale()}}"
    class="layout-navbar-fixed layout-menu-fixed layout-compact"
    dir="{{app()->getLocale() == 'ar' ? 'rtl' : 'ltr'}}"
    data-skin="default"
    data-bs-theme="{{ auth('admin')->user()->theme_mode }}"
    data-assets-path="{{ asset('dashboard') }}/assets/"
    data-template="vertical-menu-template">
<head>
    @include('dashboard.layout.head')
</head>

<body>
<input type="hidden" value="{{URL::to('/')}}" id="base_url">

<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <!-- Menu -->

        @include('dashboard.layout.sidebar')

        <div class="menu-mobile-toggler d-xl-none rounded-1">
            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large text-bg-secondary p-2 rounded-1">
                <i class="ti tabler-menu icon-base"></i>
                <i class="ti tabler-chevron-right icon-base"></i>
            </a>
        </div>
        <!-- / Menu -->
        <!-- Layout container -->
        <div class="layout-page">
            <!-- Navbar -->
            @include('dashboard.layout.navbar')
            <!-- / Navbar -->
            <!-- Content wrapper -->
            <div class="content-wrapper">
                <!-- Content -->
                @section('dashboard-main')

                @show
                <!-- / Content -->
                <!-- Footer -->
                <footer class="content-footer footer bg-footer-theme">
                    <div class="container-xxl">
                        <div
                            class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
                            <div class="text-body">
                                &#169;
                                <script>
                                    document.write(new Date().getFullYear());
                                </script>
                                , {{ __('admin.made_with') }} ❤️
                            </div>
                        </div>
                    </div>
                </footer>
                <!-- / Footer -->

                <div class="content-backdrop fade"></div>
            </div>
            <!-- Content wrapper -->
        </div>
        <!-- / Layout page -->
    </div>

    <!-- Overlay -->
    <div class="layout-overlay layout-menu-toggle"></div>

    <!-- Drag Target Area To SlideIn Menu On Small Screens -->
    <div class="drag-target"></div>
</div>
<!-- / Layout wrapper -->

@include('dashboard.partials.image-cropper-modal')

<!-- Core JS -->
<!-- build:js assets/vendor/js/theme.js  -->
@include('dashboard.layout.footer')
</body>
</html>
