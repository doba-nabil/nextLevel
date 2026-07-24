<div class="profile-sidebar-tabs">
    <!-- Mobile Toggle Button (Kept as is) -->
    <button type="button" class="profile-tabs-toggle d-lg-none" id="profileTabsToggle" aria-expanded="false">
        <span class="toggle-text">{{ __('website.my_account') }}</span>
        <i class="fa-solid fa-chevron-down toggle-icon"></i>
    </button>

    <!-- Sidebar Content -->
    <div class="tabs-wrapper" id="profileTabsList">
        <div class="tabs-header d-none d-lg-block">
            <h5 class="tabs-title">{{ __('website.my_account') }}</h5>
            <p class="tabs-subtitle">{{ Auth::user()->name ?? '' }}</p>
        </div>

        <ul class="tabs-nav">
            <li class="nav-item">
                <a href="{{ route('profile.account-info') }}" class="nav-link {{ request()->routeIs('profile.account-info') || request()->routeIs('profile.index') ? 'active' : '' }}">
                    <div class="icon-box">
                        <img src="/website/assets/img/user.svg" alt="" class="tab-icon">
                    </div>
                    <span> {{ __('website.account_info') }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('profile.orders') }}" class="nav-link {{ request()->routeIs('profile.orders') ? 'active' : '' }}">
                    <div class="icon-box">
                        <img src="/website/assets/img/box.svg" alt="" class="tab-icon">
                    </div>
                    <span> {{ __('website.my_orders') }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('profile.wallet') }}" class="nav-link {{ request()->routeIs('profile.wallet') || request()->routeIs('profile.wallet.add-money') ? 'active' : '' }}">
                    <div class="icon-box">
                        <img src="/website/assets/img/empty-wallet.svg" alt="" class="tab-icon">
                    </div>
                    <span>  {{ __('website.my_wallet') }}</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('profile.wishlist') }}" class="nav-link {{ request()->routeIs('profile.wishlist') ? 'active' : '' }}">
                    <div class="icon-box">
                        <img src="/website/assets/img/heart.svg" alt="" class="tab-icon">
                    </div>
                    <span>  {{ __('website.favourite') }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('profile.addresses') }}" class="nav-link {{ request()->routeIs('profile.addresses') ? 'active' : '' }}">
                    <div class="icon-box">
                        <img src="/website/assets/img/location.svg" alt="" class="tab-icon">
                    </div>
                    <span> {{ __('website.my_addresses') }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('website.logout') }}" class="nav-link logout-link">
                    <div class="icon-box">
                        <img src="/website/assets/img/logout.svg" alt="" class="tab-icon">
                    </div>
                    <span> {{ __('website.log_out') }} </span>
                </a>
            </li>
        </ul>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('profileTabsToggle');
        const tabsList = document.getElementById('profileTabsList');

        if (toggleBtn && tabsList) {
            toggleBtn.addEventListener('click', function() {
                const isExpanded = this.getAttribute('aria-expanded') === 'true';
                this.setAttribute('aria-expanded', !isExpanded);
                tabsList.classList.toggle('show');
            });
        }
    });
</script>
