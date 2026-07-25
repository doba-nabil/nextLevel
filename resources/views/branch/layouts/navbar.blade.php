<nav
    class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme"
    id="layout-navbar">
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
            <i class="icon-base ti tabler-menu-2 icon-md"></i>
        </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center justify-content-end" id="navbar-collapse">

        <ul class="navbar-nav flex-row align-items-center ms-md-auto">
            <li class="nav-item dropdown-language dropdown me-2 me-xl-0">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <i class="icon-base ti tabler-language icon-22px text-heading"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                        <li>
                            @if(LaravelLocalization::getCurrentLocale() != $localeCode)
                                <a class="dropdown-item"
                                   href="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}">
                                    <span>{{ $localeCode == 'ar' ? 'عربي' : 'English' }}</span>
                                </a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </li>

            <!-- Style Switcher -->
            <li class="nav-item dropdown">
                <a
                    class="nav-link dropdown-toggle hide-arrow btn btn-icon btn-text-secondary rounded-pill"
                    id="nav-theme"
                    href="javascript:void(0);"
                    data-bs-toggle="dropdown">
                    <i class="icon-base ti tabler-sun icon-22px theme-icon-active text-heading"></i>
                    <span class="d-none ms-2" id="nav-theme-text">{{ __('admin.toggle_theme') }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="nav-theme-text">
                    <li>
                        <button
                            type="button"
                            class="dropdown-item align-items-center active"
                            data-bs-theme-value="light"
                            aria-pressed="false">
                            <span><i class="icon-base ti tabler-sun icon-22px me-3" data-icon="sun"></i>{{ __('admin.light') }}</span>
                        </button>
                    </li>
                    <li>
                        <button
                            type="button"
                            class="dropdown-item align-items-center"
                            data-bs-theme-value="dark"
                            aria-pressed="true">
                        <span
                        ><i class="icon-base ti tabler-moon-stars icon-22px me-3" data-icon="moon-stars"></i
                            >{{ __('admin.dark') }}</span
                        >
                        </button>
                    </li>
                </ul>
            </li>
            <!-- / Style Switcher-->

            <!-- User -->
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a
                    class="nav-link dropdown-toggle hide-arrow p-0"
                    href="javascript:void(0);"
                    data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        <img src="{{ asset('dashboard') }}/assets/img/avatars/avatar.png" alt class="rounded-circle"/>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item mt-0" href="#">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-2">
                                    <div class="avatar avatar-online">
                                        <img src="{{ asset('dashboard') }}/assets/img/avatars/avatar.png" alt
                                             class="rounded-circle"/>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">{{ auth('branch')->user()?->getTranslation('name', app()->getLocale()) ?? 'Branch' }}</h6>
                                    <small class="text-body-secondary">Branch</small>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider my-1 mx-n2"></div>
                    </li>
                    <li>
                        <div class="d-grid px-2 pt-2 pb-1">
                            <form action="{{ route('branch.logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-danger w-100 d-flex justify-content-center">
                                    <small class="align-middle">{{ __('admin.logout') }}</small>
                                    <i class="icon-base ti tabler-logout ms-2 icon-14px"></i>
                                </button>
                            </form>
                        </div>
                    </li>
                </ul>
            </li>
            <!--/ User -->
        </ul>
    </div>
</nav>
