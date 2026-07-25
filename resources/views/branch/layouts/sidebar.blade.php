<aside id="layout-menu" class="layout-menu menu-vertical menu">
    <div class="app-brand demo">
        <a href="{{ route('branch.dashboard') }}" class="app-brand-link">
              <span class="app-brand-logo demo">
                <span class="text-primary">
                    <img height="30" src="{{ asset('dashboard') }}/assets/img/favicon/fav-icon.png" alt="logo">
                </span>
              </span>
            <span class="app-brand-text demo menu-text fw-bold ms-3">Branch Panel</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="icon-base ti menu-toggle-icon d-none d-xl-block"></i>
            <i class="icon-base ti tabler-x d-block d-xl-none"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <!-- Dashboards -->
        <li class="menu-item {{ request()->routeIs('branch.dashboard') ? 'active' : '' }}">
            <a href="{{ route('branch.dashboard') }}" class="menu-link">
                <i class="menu-icon icon-base ti tabler-smart-home"></i>
                <div>{{__('admin.admin-panel')}}</div>
            </a>
        </li>

        <li class="menu-item {{ request()->routeIs('branch.products.*') || request()->routeIs('branch.meals.*') || request()->routeIs('branch.boxes.*') ? 'active open' : '' }}">
            <a href="javascript:void(0)" class="menu-link menu-toggle">
                <i class="menu-icon icon-base ti tabler-package"></i>
                <div>{{__('admin.site_exhibits')}}</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('branch.products.index') ? 'active open' : '' }}">
                    <a href="{{ route('branch.products.index') }}" class="menu-link">
                        <div>{{__('admin.products')}}</div>
                    </a>
                </li>
                
                <li class="menu-item {{ request()->routeIs('branch.meals.index') ? 'active open' : '' }}">
                    <a href="{{ route('branch.meals.index') }}" class="menu-link">
                        <div>{{__('admin.meals')}}</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('branch.boxes.index') ? 'active open' : '' }}">
                    <a href="{{ route('branch.boxes.index') }}" class="menu-link">
                        <div>{{__('admin.meals_boxes')}}</div>
                    </a>
                </li>
            </ul>
        </li>

    </ul>
</aside>
