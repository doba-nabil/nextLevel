@include('website.profile.partials.address-scripts')
<script>
    // Wait for jQuery to be loaded
    (function() {
        function initProfileScripts() {
            // Ensure jQuery is loaded before executing
            if (typeof jQuery === 'undefined' || typeof $ === 'undefined') {
                console.error('jQuery is not loaded!');
                setTimeout(initProfileScripts, 100);
                return;
            }

            $(document).ready(function() {
        // Mobile Sidebar Functionality (only if elements exist)
        const $toggle = $('#profileTabsToggle');
        const $sidebar = $('#profileSidebarMobile');
        const $overlay = $('#profileSidebarOverlay');
        const $closeBtn = $('#profileSidebarClose');
        const $body = $('body');

        if ($toggle.length > 0 && $sidebar.length > 0) {
            // Function to open sidebar
            function openSidebar() {
                if ($toggle.length) $toggle.attr('aria-expanded', 'true');
                if ($overlay.length) $overlay.addClass('active');
                if ($sidebar.length) $sidebar.addClass('active');
                $body.addClass('sidebar-open');
            }

            // Function to close sidebar
            function closeSidebar() {
                if ($toggle.length) $toggle.attr('aria-expanded', 'false');
                if ($sidebar.length) $sidebar.removeClass('active');
                if ($overlay.length) $overlay.removeClass('active');
                $body.removeClass('sidebar-open');
            }

            // Toggle sidebar on button click
            $toggle.on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const isExpanded = $toggle.attr('aria-expanded') === 'true';
                if (isExpanded) {
                    closeSidebar();
                } else {
                    openSidebar();
                }
            });

            // Close sidebar on close button click
            if ($closeBtn.length) {
                $closeBtn.on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    closeSidebar();
                });
            }

            // Close sidebar when clicking on overlay
            if ($overlay.length) {
                $overlay.on('click', function(e) {
                    e.preventDefault();
                    closeSidebar();
                });
            }

            // Close sidebar when clicking on a link (mobile only)
            $(document).on('click', '.profile-sidebar-mobile .asideCat_link', function() {
                if ($(window).width() < 992) {
                    setTimeout(function() {
                        closeSidebar();
                    }, 200);
                }
            });

            // Close sidebar on window resize if desktop
            $(window).on('resize', function() {
                if ($(window).width() >= 992) {
                    closeSidebar();
                }
            });

            // Prevent body scroll when sidebar is open
            $(document).on('touchmove', function(e) {
                if ($body.hasClass('sidebar-open')) {
                    if (!$(e.target).closest('.profile-sidebar-mobile').length) {
                        e.preventDefault();
                    }
                }
            });
        }

        // --- Profile Edit Logic ---

        // Helper to get card elements
        function getCardElements(element) {
            const card = $(element).closest('.accountInfo_cardN');
            return {
                card: card,
                field: card.data('field'),
                editBtn: card.find('.edit_bttn'),
                editControls: card.find('.edit-controls'),
                saveBtn: card.find('.savePro_bttn'),
                closeBtn: card.find('.closePro_bttn'),
                displayField: card.find('.FName_des'),
                inputContainer: card.find('.hiddProf_input')
            };
        }

        // Edit Button Click
        $(document).on('click', '.edit_bttn, .EDitProfile_bttn', function(e) {
            e.preventDefault();
            const els = getCardElements(this);
            if (!els.card.length) return;

            // Store original values
            els.inputContainer.find('input, select').each(function() {
                $(this).data('original-value', $(this).val());
            });

            // Toggle Visibility
            els.editBtn.hide();
            els.displayField.hide();

            // Show Inputs
            els.inputContainer.css({
                'display': 'block',
                'visibility': 'visible',
                'opacity': '1'
            }).removeAttr('hidden');

            // Show Controls
            if (els.editControls.length) {
                els.editControls.removeAttr('style').css({
                    'display': 'flex',
                    'visibility': 'visible',
                    'opacity': '1',
                    'margin-top': '1rem',
                    'justify-content': 'flex-end'
                });
                els.saveBtn.show();
                els.closeBtn.show();
            } else {
                console.error('Edit controls not found for card:', els.card);
            }

            // Focus first input
            setTimeout(() => {
                els.inputContainer.find('input, select').first().focus();
            }, 50);
        });

        // Close/Cancel Button Click
        $(document).on('click', '.closePro_bttn', function(e) {
            e.preventDefault();
            const els = getCardElements(this);

            // Reset Values
            els.inputContainer.find('input, select').each(function() {
                $(this).val($(this).data('original-value'));
            });

            // Toggle Visibility
            els.editBtn.show();
            els.displayField.show();
            els.inputContainer.hide().css('display', 'none');
            els.editControls.hide();
        });

        // Save Button Click
        $(document).on('click', '.savePro_bttn', function(e) {
            e.preventDefault();
            const els = getCardElements(this);
            const field = els.field;
            let data = {
                _token: '{{ csrf_token() }}',
                field: field
            };

            // Collect Data
            if (field === 'password') {
                data.current_password = els.inputContainer.find('input[name="current_password"]').val();
                data.value = els.inputContainer.find('input[name="new_password"]').val(); // Using 'value' for consistency with backend

                if (!data.current_password || !data.value) {
                    AppSwal.warning('{{ __("website.all_fields_required") }}');
                    return;
                }
            } else if (field === 'phone') {
                data.value = els.inputContainer.find('input[name="phone"]').val();
                data.country_id = els.inputContainer.find('select[name="country_id"]').val();

                // Phone Specific Logic (OTP)
                handlePhoneUpdate(els, data);
                return;
            } else {
                // Name, Email, Address
                if (els.inputContainer.is('input')) {
                    data.value = els.inputContainer.val();
                } else {
                    data.value = els.inputContainer.find('input').val();
                }

                if (!data.value) {
                    AppSwal.warning('{{ __("website.this_field_is_required") }}');
                    return;
                }
            }

            // Generic AJAX Update
            performProfileUpdate(els, data);
        });

        // Handle Phone Update (OTP Flow)
        function handlePhoneUpdate(els, data) {
            const currentPhone = '{{ auth()->user()->phone ?? "" }}';
            const currentCountryId = '{{ auth()->user()->country_id ?? "" }}';

            // Check if changed
            if (data.value === currentPhone && data.country_id == currentCountryId) {
                els.closeBtn.trigger('click');
                return;
            }

            // Send OTP
            els.saveBtn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>');

            $.ajax({
                url: '{{ route("profile.send.phone.otp") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    phone: data.value,
                    country_id: data.country_id
                },
                success: function(response) {
                    els.saveBtn.prop('disabled', false).text('{{ __("website.save") }}');
                    if (response.success) {
                        // Get phone display - use response.phone_full if available, otherwise construct it
                        let phoneDisplay = response.phone_full;
                        if (!phoneDisplay) {
                            // Fallback: construct phone display from country and phone
                            let countrySelect = els.inputContainer.find('select[name="country_id"]');
                            let phoneCode = countrySelect.find('option:selected').data('phone-code') || '';
                            let phoneValue = data.value || '';
                            phoneDisplay = phoneCode ? phoneCode + ' ' + phoneValue : phoneValue;
                        }
                        showOtpModal(els, data, phoneDisplay);
                    } else {
                        AppSwal.error(response.error || '{{ __("website.error") }}');
                    }
                },
                error: function(xhr) {
                    els.saveBtn.prop('disabled', false).text('{{ __("website.save") }}');
                    AppSwal.error(xhr.responseJSON?.error || '{{ __("website.error") }}');
                }
            });
        }

        // Show OTP Modal
        function showOtpModal(els, data, fullPhoneDisplay) {
            Swal.fire({
                title: '{{ __("website.verify_phone") }}',
                html: `
                    <p class="mb-3">{{ __("website.enter_otp_sent_to") }} <strong>${fullPhoneDisplay}</strong></p>
                    <input type="text" id="swal-otp-input" class="swal2-input text-center" placeholder="######" maxlength="6">
                `,
                showCancelButton: true,
                confirmButtonText: '{{ __("website.verify") }}',
                cancelButtonText: '{{ __("website.cancel") }}',
                preConfirm: () => {
                    const otp = document.getElementById('swal-otp-input').value;
                    if (!otp || otp.length < 6) {
                        Swal.showValidationMessage('{{ __("website.invalid_otp") }}');
                    }
                    return otp;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    data.otp_code = result.value;
                    performProfileUpdate(els, data);
                }
            });
        }

        // Perform Generic Profile Update
        function performProfileUpdate(els, data) {
            els.saveBtn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>');

            $.ajax({
                url: '{{ route("profile.update") }}', // Make sure this route exists and points to updateProfile
                method: 'POST',
                data: data,
                success: function(response) {
                    els.saveBtn.prop('disabled', false).text('{{ __("website.save") }}');
                    if (response.success) {
                        AppSwal.success(response.message || '{{ __("website.updated_successfully") }}');

                        // Update Display
                        if (data.field !== 'password') {
                            if (data.field === 'phone' && response.value) {
                                // Format phone display if needed, simplified here
                                els.displayField.text(response.value);
                                // Ideally backend returns formatted display value, or we construct it
                                // For now, let's reload or construct simple display
                                const countryCode = els.inputContainer.find('select option:selected').text().trim();
                                els.displayField.html(`<span class="country-pill">${countryCode}</span> ${data.value}`);
                            } else {
                                els.displayField.text(data.value);
                            }
                        }

                        // Close Edit Mode (simulating close button click but updating original data)
                        els.inputContainer.find('input, select').each(function() {
                            $(this).data('original-value', $(this).val());
                        });
                        els.editBtn.show();
                        els.displayField.show();
                        els.inputContainer.hide().css('display', 'none');
                        els.editControls.hide();

                        // Clear password fields
                        if (data.field === 'password') {
                            els.inputContainer.find('input').val('');
                        }

                    } else {
                        AppSwal.error(response.error || '{{ __("website.error") }}');
                    }
                },
                error: function(xhr) {
                    els.saveBtn.prop('disabled', false).text('{{ __("website.save") }}');
                    AppSwal.error(xhr.responseJSON?.error || '{{ __("website.error") }}');
                }
            });
        }

        // Track Order Modal Handler
        $(document).on('click', '.btn-track-order', function(e) {
            e.preventDefault();
            e.stopPropagation();

            console.log('Track order button clicked'); // Debug

            const $btn = $(this);
            const orderId = $btn.data('order-id');
            const orderNumber = $btn.data('order-number') || '#N/A';
            const orderDate = $btn.data('order-date') || '';
            const orderStatus = $btn.data('order-status') || '';
            const orderTotal = $btn.data('order-total') || '';
            const paymentMethod = $btn.data('payment-method') || 'Cash';
            const orderStatusValue = $btn.data('order-status-value') || 'pending';

            console.log('Order ID:', orderId); // Debug
            console.log('Order Number:', orderNumber); // Debug

            if (!orderId) {
                console.error('Order ID not found');
                AppSwal.error('{{ __("website.error") }}', 'Order ID not found');
                return;
            }

            // Get tracking section content from the page
            const trackingSection = $(`#tracking-${orderId}`);
            let trackingHtml = '';

            if (trackingSection.length && trackingSection.find('.tracking-content').length) {
                // Get the inner content of tracking section
                trackingHtml = trackingSection.find('.tracking-content').html();
            } else {
                // Fallback: create basic tracking steps based on status
                const statusLower = orderStatusValue.toLowerCase();
                const isPending = statusLower === 'pending';
                const isProcessing = statusLower === 'processing';
                const isShipped = statusLower === 'shipped';
                const isDelivered = statusLower === 'delivered';

                trackingHtml = `
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="fw-800 mb-0">
                            <i class="fa-solid fa-route gold-icon me-2"></i>
                            {{ __('website.order_progress') }}
                        </h6>
                        <span class="text-muted small">{{ __('website.current_status') }}: <strong>${orderStatus}</strong></span>
                    </div>
                    <div class="progress-steps-premium horizontal-steps">
                        <div class="step-item ${isPending ? 'active' : (isProcessing || isShipped || isDelivered ? 'completed' : '')}">
                            <div class="step-icon">
                                <i class="fa-solid fa-file-invoice"></i>
                            </div>
                            <span class="step-label">{{ __('website.order_placed') }}</span>
                        </div>
                        <div class="step-item ${isProcessing ? 'active' : (isShipped || isDelivered ? 'completed' : '')}">
                            <div class="step-icon">
                                <i class="fa-solid fa-spinner fa-spin-slow"></i>
                            </div>
                            <span class="step-label">{{ __('website.processing') }}</span>
                        </div>
                        <div class="step-item ${isShipped ? 'active' : (isDelivered ? 'completed' : '')}">
                            <div class="step-icon">
                                <i class="fa-solid fa-truck"></i>
                            </div>
                            <span class="step-label">{{ __('website.shipped') ?? 'Shipped' }}</span>
                        </div>
                        <div class="step-item ${isDelivered ? 'active completed' : ''}">
                            <div class="step-icon">
                                <i class="fa-solid fa-check-double"></i>
                            </div>
                            <span class="step-label">{{ __('website.delivered') }}</span>
                        </div>
                    </div>
                `;
            }

            // Build modal content
            const modalContent = `
                <div class="mb-4">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="info-item-card border-0 p-0">
                                <div>
                                    <div class="info-label">{{ __('website.order_number') }}</div>
                                    <div class="info-value">${orderNumber}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item-card border-0 p-0">
                                <div>
                                    <div class="info-label">{{ __('website.order_date') }}</div>
                                    <div class="info-value">${orderDate}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item-card border-0 p-0">
                                <div>
                                    <div class="info-label">{{ __('website.total_amount') }}</div>
                                    <div class="info-value text-success">${orderTotal}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item-card border-0 p-0">
                                <div>
                                    <div class="info-label">{{ __('website.payment') }}</div>
                                    <div class="info-value">${paymentMethod}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tracking-content p-4 border-top bg-light-soft rounded-4">
                    ${trackingHtml}
                </div>
            `;

            // Update modal content first
            console.log('Updating modal content'); // Debug
            $('#orderTrackingContent').html(modalContent);

            // Open modal manually after content is ready
            const modalElement = document.getElementById('orderTrackingModal');
            if (!modalElement) {
                console.error('Modal element not found');
                AppSwal.error('{{ __("website.error") }}', 'Modal not found');
                return;
            }

            console.log('Modal element found, attempting to open'); // Debug

            // Try multiple methods to open modal
            let modalOpened = false;

            // Method 1: Bootstrap 5
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                try {
                    let modal = bootstrap.Modal.getInstance(modalElement);
                    if (!modal) {
                        modal = new bootstrap.Modal(modalElement);
                    }
                    modal.show();
                    console.log('Bootstrap 5 modal opened'); // Debug
                    modalOpened = true;
                } catch (error) {
                    console.error('Bootstrap 5 modal error:', error);
                }
            }

            // Method 2: jQuery Bootstrap (if Bootstrap 5 didn't work)
            if (!modalOpened && typeof $.fn.modal !== 'undefined') {
                try {
                    $('#orderTrackingModal').modal('show');
                    console.log('jQuery modal opened'); // Debug
                    modalOpened = true;
                } catch (error) {
                    console.error('jQuery modal error:', error);
                }
            }

            // Method 3: Manual show (last resort)
            if (!modalOpened) {
                console.log('Using manual show method'); // Debug
                $(modalElement).addClass('show').css({
                    'display': 'block',
                    'padding-right': '17px'
                });
                $('body').append('<div class="modal-backdrop fade show"></div>').addClass('modal-open');
            }
        });

        // Track Order Toggle Handler (Keep existing for collapse functionality)
        $(document).on('click', '.btn-track-toggle', function(e) {
            e.preventDefault();
            let targetId = $(this).data('target');
            let $target = $(targetId);

            if ($target.length) {
                if ($target.is(':visible')) {
                    $target.slideUp(300);
                } else {
                    $target.slideDown(300);
                }
            }
        });

        // Convert Points to Wallet Handler
        $(document).on('click', '.convert-points-btn', function(e) {
            e.preventDefault();
            const $btn = $(this);
            const points = $btn.data('points');
            const amount = $btn.data('amount');

            Swal.fire({
                title: '{{ __("website.confirm_conversion") }}',
                html: `
                    <p class="mb-3">{{ __("website.convert_points_confirmation") }}</p>
                    <div class="text-center">
                        <div class="mb-2">
                            <strong>${points}</strong> {{ __("website.points") }}
                        </div>
                        <div class="text-muted">
                            <i class="fa-solid fa-arrow-down"></i>
                        </div>
                        <div class="mt-2">
                            <strong>${amount}</strong> {{ \App\Models\Currency::getCurrentCurrencySign() }}
                        </div>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '{{ __("website.convert") }}',
                cancelButtonText: '{{ __("website.cancel") }}',
                confirmButtonColor: '#000',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Disable button and show loading
                    $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i>{{ __("website.processing") }}...');

                    $.ajax({
                        url: '{{ route("profile.convert.points") }}',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                AppSwal.success(response.message || '{{ __("website.points_converted_successfully") }}');
                                
                                // Update points display
                                $('.vip-points-value').html(`${response.new_points} <small>points</small>`);
                                
                                // Reload page after 1.5 seconds to update wallet balance
                                setTimeout(function() {
                                    window.location.reload();
                                }, 1500);
                            } else {
                                $btn.prop('disabled', false).html('<i class="fa-solid fa-exchange-alt me-2"></i>{{ __("website.convert_points_to_wallet") }}');
                                AppSwal.error(response.message || '{{ __("website.error") }}');
                            }
                        },
                        error: function(xhr) {
                            $btn.prop('disabled', false).html('<i class="fa-solid fa-exchange-alt me-2"></i>{{ __("website.convert_points_to_wallet") }}');
                            const errorMsg = xhr.responseJSON?.message || xhr.responseJSON?.error || '{{ __("website.error") }}';
                            AppSwal.error(errorMsg);
                        }
                    });
                }
            });
        });
            });
        }

        // Try to initialize immediately
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initProfileScripts);
        } else {
            // DOM is already loaded
            initProfileScripts();
        }
    })();
</script>
