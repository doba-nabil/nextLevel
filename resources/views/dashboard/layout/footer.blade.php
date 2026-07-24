<!-- jQuery -->
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

<!-- endbuild -->

<!-- Vendors JS -->
<script src="{{ asset('dashboard') }}/assets/vendor/libs/apex-charts/apexcharts.js"></script>
<script src="{{ asset('dashboard') }}/assets/vendor/libs/swiper/swiper.js"></script>

<!-- Main JS -->

<script src="{{ asset('dashboard') }}/assets/js/main.js"></script>

<!-- Page JS -->
<script src="{{ asset('dashboard') }}/assets/js/dashboards-analytics.js"></script>
<script>
    document.querySelectorAll('[data-bs-theme-value]').forEach(button => {
        button.addEventListener('click', function () {
            let newTheme = this.getAttribute('data-bs-theme-value');
            fetch("{{ route('theme.change') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ theme: newTheme })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.documentElement.setAttribute('data-bs-theme', data.theme);
                        document.querySelectorAll('[data-bs-theme-value]').forEach(btn => {
                            btn.classList.remove('active');
                        });
                        this.classList.add('active');
                        let icon = document.querySelector('#nav-theme .theme-icon-active');
                        if (data.theme === 'dark') {
                            icon.classList.remove('tabler-sun');
                            icon.classList.add('tabler-moon-stars');
                        } else {
                            icon.classList.remove('tabler-moon-stars');
                            icon.classList.add('tabler-sun');
                        }
                    }
                });
        });
    });
</script>
@section('dashboard-footer')

@show
<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: '',
            text: "{{ session('success') }}",
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

        @if ($errors->any())
        @php
            $allErrors = implode("\\n", $errors->all());
        @endphp
        Swal.fire({
            icon: 'error',
            title: '',
            html: "{!! $allErrors !!}",
            timer: 4000,
            timerProgressBar: true,
            showConfirmButton: true,
            didOpen: (toast) => {
                const swalTimer = Swal.getTimerLeft();
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
        @endif

        // Load notifications
        function loadNotifications() {
            fetch('{{ route("notifications.latest") }}')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('notifications-container');
                    const loading = document.getElementById('notifications-loading');
                    const empty = document.getElementById('notifications-empty');
                    const badge = document.getElementById('notification-badge');
                    const countBadge = document.getElementById('notification-count');
                    
                    loading.style.display = 'none';
                    
                    if (data.length === 0) {
                        empty.style.display = 'block';
                        badge.style.display = 'none';
                        countBadge.textContent = '0 {{__("admin.new")}}';
                        return;
                    }
                    
                    empty.style.display = 'none';
                    container.innerHTML = '';
                    
                    const unreadCount = data.filter(n => !n.is_read).length;
                    
                    if (unreadCount > 0) {
                        badge.style.display = 'block';
                        countBadge.textContent = unreadCount + ' {{__("admin.new")}}';
                    } else {
                        badge.style.display = 'none';
                        countBadge.textContent = '0 {{__("admin.new")}}';
                    }
                    
                    data.forEach(notification => {
                        const item = document.createElement('li');
                        item.className = 'list-group-item list-group-item-action dropdown-notifications-item' + (notification.is_read ? ' marked-as-read' : '');
                        item.innerHTML = `
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar">
                                        <span class="avatar-initial rounded-circle bg-label-${notification.type === 'order' ? 'success' : 'primary'}">
                                            <i class="icon-base ti tabler-${notification.type === 'order' ? 'shopping-cart' : 'bell'}"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 small">${notification.title}</h6>
                                    <small class="mb-1 d-block text-body">${notification.message}</small>
                                    <small class="text-body-secondary">${new Date(notification.created_at).toLocaleString()}</small>
                                </div>
                                <div class="flex-shrink-0 dropdown-notifications-actions">
                                    <a href="javascript:void(0)" class="dropdown-notifications-read mark-read-btn" data-id="${notification.id}">
                                        <span class="badge badge-dot"></span>
                                    </a>
                                    ${notification.order_id ? `<a href="{{ url('admin/orders') }}/${notification.order_id}" class="dropdown-notifications-archive">
                                        <span class="icon-base ti tabler-external-link"></span>
                                    </a>` : ''}
                                </div>
                            </div>
                        `;
                        container.appendChild(item);
                    });
                    
                    // Add click handlers
                    document.querySelectorAll('.mark-read-btn').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const id = this.getAttribute('data-id');
                            fetch(`{{ url('admin/notifications') }}/${id}/mark-as-read`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            }).then(() => {
                                loadNotifications();
                                loadUnreadCount();
                            });
                        });
                    });
                })
                .catch(error => {
                    console.error('Error loading notifications:', error);
                    document.getElementById('notifications-loading').style.display = 'none';
                });
        }
        
        function loadUnreadCount() {
            fetch('{{ route("notifications.unread-count") }}')
                .then(response => response.json())
                .then(data => {
                    const badge = document.getElementById('notification-badge');
                    const countBadge = document.getElementById('notification-count');
                    
                    if (data.count > 0) {
                        badge.style.display = 'block';
                        countBadge.textContent = data.count + ' {{__("admin.new")}}';
                    } else {
                        badge.style.display = 'none';
                        countBadge.textContent = '0 {{__("admin.new")}}';
                    }
                });
        }
        
        // Mark all as read
        document.getElementById('mark-all-read-btn')?.addEventListener('click', function() {
            fetch('{{ route("notifications.mark-all-as-read") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }).then(() => {
                loadNotifications();
                loadUnreadCount();
            });
        });
        
        // Load notifications on page load
        loadNotifications();
        loadUnreadCount();
        
        // Refresh notifications every 30 seconds
        setInterval(() => {
            loadUnreadCount();
        }, 30000);
    });
</script>
