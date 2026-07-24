<aside id="layout-menu" class="layout-menu menu-vertical menu">
    <div class="app-brand demo">
        <a href="{{ url('admin') }}" class="app-brand-link">
              <span class="app-brand-logo demo">
                <span class="text-primary">
                    <img height="30" style="filter: invert(100%);" src="{{ asset('dashboard') }}/assets/img/favicon/fav-icon.png" alt="logo">
                </span>
              </span>
            <span class="app-brand-text demo menu-text fw-bold ms-3">Next Level</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="icon-base ti menu-toggle-icon d-none d-xl-block"></i>
            <i class="icon-base ti tabler-x d-block d-xl-none"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <!-- Dashboards -->
        <li class="menu-item {{ request()->is('*admin') ? 'active' : '' }}">
            <a href="{{ url('admin') }}" class="menu-link">
                <i class="menu-icon icon-base ti tabler-smart-home"></i>
                <div>{{__('admin.admin-panel')}}</div>
            </a>
        </li>

         <li class="menu-item">
            <a href="{{ route('website.home') }}" target="_blank" class="menu-link">
                <i class="menu-icon icon-base ti tabler-external-link"></i>
                <div>{{__('admin.visit_website')}}</div>
            </a>
        </li>

        <li class="menu-item {{ request()->is('*admins*') || request()->is('*roles*') ? 'active open' : '' }}">
            <a href="javascript:void(0)" class="menu-link menu-toggle">
                <i class="menu-icon icon-base ti tabler-user-star"></i>
                <div>{{ __('admin.our_staff') }}</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->is('*admins*') ? 'active open' : '' }}">
                    <a href="{{ url('admin/admins') }}" class="menu-link">
                        <div>{{ __('admin.supervisors') }}</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->is('*roles*') ? 'active open' : '' }}">
                    <a href="{{ url('admin/roles') }}" class="menu-link">
                        <div>{{ __('admin.Supervisors Privileges') }}</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->is('*audits*') ? 'active' : '' }}">
                    <a href="{{ url('admin/audits') }}" class="menu-link">
                        <div>{{__('admin.audits')}}</div>
                    </a>
                </li>
            </ul>
        </li>

        <li class="menu-item {{ request()->is('*users*') ? 'active open' : '' }}">
            <a href="{{ url('admin/users') }}" class="menu-link">
                <i class="menu-icon icon-base ti tabler-users"></i>
                <div>{{__('admin.customers')}}</div>
            </a>
        </li>

        <li class="menu-item {{ request()->is('*categories*') ? 'active open' : '' }}">
            <a href="{{ url('admin/categories') }}" class="menu-link">
                <i class="menu-icon icon-base ti tabler-category"></i>
                <div>{{__('admin.categories')}}</div>
            </a>
        </li>

        <li class="menu-item {{ request()->is('*menus*') ? 'active open' : '' }}">
            <a href="{{ url('admin/menus') }}" class="menu-link">
                <i class="menu-icon icon-base ti tabler-menu-2"></i>
                <div>{{__('admin.menus')}}</div>
            </a>
        </li>

        <li class="menu-item {{ request()->is('*sliders*') ? 'active open' : '' }}">
            <a href="{{ url('admin/sliders') }}" class="menu-link">
                <i class="menu-icon icon-base ti tabler-photo"></i>
                <div>{{__('admin.sliders')}}</div>
            </a>
        </li>

        <li class="menu-item {{ request()->is('*banners*') ? 'active open' : '' }}">
            <a href="{{ url('admin/banners') }}" class="menu-link">
                <i class="menu-icon icon-base ti tabler-photo"></i>
                <div>{{__('admin.banners')}}</div>
            </a>
        </li>

        <li class="menu-item {{ request()->is('*offers*') ? 'active open' : '' }}">
            <a href="{{ url('admin/offers') }}" class="menu-link">
                <i class="menu-icon icon-base ti tabler-discount"></i>
                <div>{{__('admin.offers')}}</div>
            </a>
        </li>

        <li class="menu-item {{ request()->is('*products*') || request()->is('*meals*') || request()->is('*boxes*') || request()->is('*product_definitions*') || request()->is('*addon_groups*') || request()->is('*addons*') ? 'active open' : '' }}">
            <a href="javascript:void(0)" class="menu-link menu-toggle">
                <i class="menu-icon icon-base ti tabler-package"></i>
                <div>{{__('admin.site_exhibits')}}</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->is('*products*') ? 'active open' : '' }}">
                    <a href="{{ url('admin/products') }}" class="menu-link">
                        <div>{{__('admin.products')}}</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->is('*meals*') ? 'active open' : '' }}">
                    <a href="{{ url('admin/meals') }}" class="menu-link">
                        <div>{{__('admin.meals')}}</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->is('*boxes*') ? 'active open' : '' }}">
                    <a href="{{ url('admin/boxes') }}" class="menu-link">
                        <div>{{__('admin.meals_boxes')}}</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->is('*product_definitions*') ? 'active open' : '' }}">
                    <a href="{{ url('admin/product_definitions') }}" class="menu-link">
                        <div>{{ __('admin.nutrition_type') }}</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->is('*addon_groups*') ? 'active open' : '' }}">
                    <a href="{{ url('admin/addon_groups') }}" class="menu-link">
                        <div>{{__('admin.additional_types')}}</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->is('*addons*') ? 'active open' : '' }}">
                    <a href="{{ url('admin/addons') }}" class="menu-link ">
                        <div>{{__('admin.additionals')}}</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->is('*product-notes*') ? 'active open' : '' }}">
                    <a href="{{ route('product-notes.index') }}" class="menu-link">
                        <div>{{__('admin.product_notes')}}</div>
                    </a>
                </li>

            </ul>
        </li>

        <li class="menu-item {{ request()->is('*countries*') || request()->is('*cities*') || request()->is('*states*') ? 'active open' : '' }}">
            <a href="javascript:void(0)" class="menu-link menu-toggle">
                <i class="menu-icon icon-base ti tabler-world"></i>
                <div>{{ __('admin.locations') }}</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->is('*countries*') ? 'active' : '' }}">
                    <a href="{{ url('admin/countries') }}" class="menu-link">
                        <div>{{ __('admin.countries') }}</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->is('*states*') ? 'active open' : '' }}">
                    <a href="{{ url('admin/states') }}" class="menu-link">
                        <div>{{ __('admin.states') }}</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->is('*cities*') ? 'active open' : '' }}">
                    <a href="{{ url('admin/cities') }}" class="menu-link">
                        <div>{{ __('admin.cities') }}</div>
                    </a>
                </li>
            </ul>
        </li>

        <li class="menu-item {{ request()->is('*branches*') ? 'active open' : '' }}">
            <a href="{{ url('admin/branches') }}" class="menu-link">
                <i class="menu-icon icon-base ti tabler-burger"></i>
                <div>{{__('admin.branches')}}</div>
            </a>
        </li>

        <li class="menu-item {{ request()->is('*orders*') ? 'active open' : '' }}">
            <a href="javascript:void(0)" class="menu-link menu-toggle">
                <i class="menu-icon icon-base ti tabler-shopping-cart"></i>
                <div>{{__('admin.orders')}}</div>
            </a>
            <ul class="menu-sub {{ request()->is('*orders') ? 'active' : '' }}">
                <li class="menu-item">
                    <a href="{{ url('admin/orders?type=pending_orders') }}" class="menu-link">
                        <div>{{__('admin.pending_orders')}}</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="{{ url('admin/orders?type=shipping_orders') }}" class="menu-link">
                        <div>{{__('admin.shipping_orders')}}</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="{{ url('admin/orders?type=canceled_orders') }}" class="menu-link">
                        <div>{{__('admin.canceled_orders')}}</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="{{ url('admin/orders?type=completed_orders') }}" class="menu-link">
                        <div>{{__('admin.completed_orders')}}</div>
                    </a>
                </li>
            </ul>
        </li>

        <li class="menu-item {{ request()->is('*reports*') ? 'active open' : '' }}">
            <a href="javascript:void(0)" class="menu-link menu-toggle">
                <i class="menu-icon icon-base ti tabler-chart-bar"></i>
                <div>{{__('admin.reports')}}</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->is('*reports') && !request()->is('*reports/*') ? 'active' : '' }}">
                    <a href="{{ route('reports.index') }}" class="menu-link">
                        <div>{{__('admin.reports_overview')}}</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->is('*reports/best-selling*') ? 'active' : '' }}">
                    <a href="{{ route('reports.best-selling') }}" class="menu-link">
                        <div>{{__('admin.best_selling_products')}}</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->is('*reports/best-branches*') ? 'active' : '' }}">
                    <a href="{{ route('reports.best-branches') }}" class="menu-link">
                        <div>{{__('admin.best_branches')}}</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->is('*reports/payment-methods*') ? 'active' : '' }}">
                    <a href="{{ route('reports.payment-methods') }}" class="menu-link">
                        <div>{{__('admin.payment_methods_report')}}</div>
                    </a>
                </li>
            </ul>
        </li>

        <li class="menu-item {{ request()->is('*wallets*') || request()->is('*coupons*') ? 'active open' : '' }}">
            <a href="javascript:void(0)" class="menu-link menu-toggle">
                <i class="menu-icon icon-base ti tabler-brand-cashapp"></i>
                <div>{{ __('admin.finance') }}</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->is('*wallets*') ? 'active' : '' }}">
                    <a href="{{ url('admin/wallets') }}" class="menu-link">
                        <div>{{ __('admin.wallets_transactions') }}</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->is('*coupons*') ? 'active open' : '' }}">
                    <a href="{{ url('admin/coupons') }}" class="menu-link">
                        <div>{{ __('admin.coupons') }}</div>
                    </a>
                </li>
            </ul>
        </li>

        <li class="menu-item {{ request()->is('*pages*') ? 'active open' : '' }}">
            <a href="{{ url('admin/pages') }}" class="menu-link">
                <i class="menu-icon icon-base ti tabler-file"></i>
                <div>{{__('admin.pages')}}</div>
            </a>
        </li>

        <li class="menu-item {{ request()->is('*home-page-settings*') ? 'active open' : '' }}">
            <a href="{{ route('home-page-settings.get') }}" class="menu-link">
                <i class="menu-icon icon-base ti tabler-home"></i>
                <div>{{__('admin.home_page_settings')}}</div>
            </a>
        </li>

        <li class="menu-item {{ request()->is('*settings*') && !request()->is('*home-page-settings*') ? 'active open' : '' }}">
            <a href="{{ route('settings.get') }}" class="menu-link">
                <i class="menu-icon icon-base ti tabler-settings"></i>
                <div>{{__('admin.settings')}}</div>
            </a>
        </li>


    </ul>
</aside>
