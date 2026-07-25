<!doctype html>

<html
    lang="en"
    class="layout-wide customizer-hide"
    dir="ltr"
    data-skin="default"
    data-bs-theme="light"
    data-assets-path="{{ asset('dashboard') }}/assets/"
    data-template="vertical-menu-template">
<head>
    <meta charset="utf-8" />
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="robots" content="noindex, nofollow" />
    <title>Next Level - Branch Panel</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('dashboard') }}/assets/img/favicon/fav-icon.png" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&ampdisplay=swap"
        rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('dashboard') }}/assets/vendor/fonts/iconify-icons.css" />

    <!-- Core CSS -->
    <!-- build:css assets/vendor/css/theme.css  -->

    <link rel="stylesheet" href="{{ asset('dashboard') }}/assets/vendor/libs/node-waves/node-waves.css" />

    <link rel="stylesheet" href="{{ asset('dashboard') }}/assets/vendor/libs/pickr/pickr-themes.css" />

    <link rel="stylesheet" href="{{ asset('dashboard') }}/assets/vendor/css/core.css" />
    <link rel="stylesheet" href="{{ asset('dashboard') }}/assets/css/demo.css" />

    <!-- Vendors CSS -->

    <link rel="stylesheet" href="{{ asset('dashboard') }}/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="{{ asset('dashboard') }}/assets/vendor/libs/sweetalert2/sweetalert2.css" />
    <!-- endbuild -->

    <!-- Vendor -->
    <link rel="stylesheet" href="{{ asset('dashboard') }}/assets/vendor/libs/@form-validation/form-validation.css" />

    <!-- Page CSS -->
    <!-- Page -->
    <link rel="stylesheet" href="{{ asset('dashboard') }}/assets/vendor/css/pages/page-auth.css" />

    <!-- Helpers -->
    <script src="{{ asset('dashboard') }}/assets/vendor/js/helpers.js"></script>
    <script src="{{ asset('dashboard') }}/assets/vendor/js/template-customizer.js"></script>
    <script src="{{ asset('dashboard') }}/assets/js/config.js"></script>
</head>

<body>
<!-- Content -->

<div class="authentication-wrapper authentication-cover">
    <!-- Logo -->
    <a href="#" class="app-brand auth-cover-brand">
        <span class="app-brand-logo demo">
          <span class="text-primary">
                        <img src="{{ asset('dashboard') }}/assets/img/favicon/fav-icon.png" width="50" >
          </span>
        </span>
        <span class="app-brand-text demo text-heading fw-bold">Next Level Branch</span>
    </a>
    <!-- /Logo -->
    <div class="authentication-inner row m-0">
        <!-- /Left Text -->
        <div class="d-none d-xl-flex col-xl-8 p-0">
            <div class="auth-cover-bg d-flex justify-content-center align-items-center">
                <img
                    src="{{ asset('dashboard') }}/assets/img/illustrations/auth-login-illustration-light.png"
                    alt="auth-login-cover"
                    class="my-5 auth-illustration"
                    data-app-light-img="illustrations/auth-login-illustration-light.png"
                    data-app-dark-img="illustrations/auth-login-illustration-dark.png" />
                <img
                    src="{{ asset('dashboard') }}/assets/img/illustrations/bg-shape-image-light.png"
                    alt="auth-login-cover"
                    class="platform-bg"
                    data-app-light-img="illustrations/bg-shape-image-light.png"
                    data-app-dark-img="illustrations/bg-shape-image-dark.png" />
            </div>
        </div>
        <!-- /Left Text -->

        <!-- Login -->
        <div class="d-flex col-12 col-xl-4 align-items-center authentication-bg p-sm-12 p-6">
            <div class="w-px-400 mx-auto mt-12 pt-5">
                <h4 class="mb-1">{{ __('admin.welcome_to') ?? 'Welcome To' }} Next Level (Branch)</h4>
                <p class="mb-6">{{ __('admin.please_sign_in') }}</p>

                <form id="formAuthentication" class="mb-6" action="{{ route('branch.login') }}" method="POST">
                    @csrf
                    <div class="mb-6 form-control-validation">
                        <label for="username" class="form-label">{{ __('admin.username') ?? 'Username' }}</label>
                        <input
                            type="text"
                            class="form-control @error('username') is-invalid @enderror"
                            id="username"
                            name="username"
                            value="{{ old('username') }}"
                            placeholder="Enter your username"
                            autofocus />
                        @error('username')
                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                        @enderror
                    </div>
                    <div class="mb-6 form-password-toggle form-control-validation">
                        <label class="form-label" for="password">{{ __('admin.password') }}</label>
                        <div class="input-group input-group-merge">
                            <input
                                type="password"
                                id="password"
                                class="form-control @error('password') is-invalid @enderror"
                                name="password"
                                placeholder="{{ __('admin.enter_password') }}"
                                aria-describedby="password" />
                            <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye-off"></i></span>
                            @error('password')
                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                            @enderror
                        </div>
                    </div>
                    <div class="mb-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember-me" name="remember" checked />
                            <label class="form-check-label" for="remember-me"> {{ __('website.remember_me') }} </label>
                        </div>
                    </div>
                    <button class="btn btn-primary d-grid w-100">{{ __('admin.sign_in') }}</button>
                </form>

            </div>
        </div>
        <!-- /Login -->
    </div>
</div>

<!-- / Content -->

<!-- Core JS -->
<script src="{{ asset('dashboard') }}/assets/vendor/libs/jquery/jquery.js"></script>
<script src="{{ asset('dashboard') }}/assets/vendor/libs/popper/popper.js"></script>
<script src="{{ asset('dashboard') }}/assets/vendor/js/bootstrap.js"></script>
<script src="{{ asset('dashboard') }}/assets/vendor/libs/node-waves/node-waves.js"></script>
<script src="{{ asset('dashboard') }}/assets/vendor/libs/@algolia/autocomplete-js.js"></script>
<script src="{{ asset('dashboard') }}/assets/vendor/libs/pickr/pickr.js"></script>
<script src="{{ asset('dashboard') }}/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
<script src="{{ asset('dashboard') }}/assets/vendor/libs/hammer/hammer.js"></script>
<script src="{{ asset('dashboard') }}/assets/vendor/libs/i18n/i18n.js"></script>
<script src="{{ asset('dashboard') }}/assets/vendor/js/menu.js"></script>

<!-- Vendors JS -->
<script src="{{ asset('dashboard') }}/assets/vendor/libs/@form-validation/popular.js"></script>
<script src="{{ asset('dashboard') }}/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
<script src="{{ asset('dashboard') }}/assets/vendor/libs/@form-validation/auto-focus.js"></script>

<!-- Main JS -->
<script src="{{ asset('dashboard') }}/assets/js/main.js"></script>

<script src="{{ asset('dashboard') }}/assets/vendor/libs/sweetalert2/sweetalert2.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: '{{ __("admin.Error") }}',
            text: "{{ session('error') }}",
            timer: 2000,
            timerProgressBar: true,
            showConfirmButton: false,
            didOpen: (toast) => {
                const swalTimer = Swal.getTimerLeft();
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
        @endif
    });
</script>
</body>
</html>
