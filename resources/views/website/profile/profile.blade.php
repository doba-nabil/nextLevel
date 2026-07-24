@extends('website.layout.master')
@section('title',  __('website.my_account') )
@section('body', 'profile-body')
@section('website-main')
    <!-- Content -->
    <div class="breadCrumb_section midPadding">

        <div class="container px-lg-0">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">{{ __('website.home') }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page"> {{ __('website.my_account') }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <section class="pickup_section secPadding pt-0">
        <div class="container container_start px-lg-0">
            <div class="row">
                <div class="col-12 col-lg-4">
                    @include('website.profile.partials.tabs')
                </div>
                <div class="col-12 col-lg-8">
                    <div id="proCont_wrapper">

                    </div>
                </div>
            </div>
        </div>
    </section>

    <!--/ Content -->

    @include('website.profile.partials.address-modals')

    @include('website.profile.partials.address-scripts')
        .address-item-map {
            transition: all 0.3s ease;
        }
        .address-item-map:hover {
            background-color: #f8f9fa;
            transform: translateX(5px);
        }
        .address-item-map.active {
            background-color: #e7f3ff;
            border-left: 4px solid #0d6efd !important;
        }
        #addresses-map {
            border-radius: 0 0 0.375rem 0;
        }
        .profile-address-item {
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .profile-address-item:hover {
            background-color: #f8f9fa;
        }
        .profile-address-item.selected {
            background-color: #e7f3ff;
            border-left: 4px solid #0d6efd !important;
        }
        /* Ensure Autocomplete dropdown appears above modal */
        .pac-container {
            z-index: 1060 !important;
            border-radius: 4px !important;
            box-shadow: 0 2px 6px rgba(0,0,0,0.3) !important;
            background-color: white !important;
        }

        /* Address form map styles */
        #profile-address-map-container {
            position: relative;
            width: 100%;
            height: 350px;
        }

        #profile-address-search {
            z-index: 1055 !important;
            position: absolute !important;
            top: 10px !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            width: 90% !important;
            height: 40px !important;
            padding: 0 12px !important;
            border: 2px solid #007bff !important;
            border-radius: 4px !important;
            box-shadow: 0 2px 6px rgba(0,0,0,0.3) !important;
            background-color: white !important;
        }

        #profile-address-map {
            width: 100%;
            height: 100%;
        }

        .pac-item {
            padding: 10px !important;
            cursor: pointer !important;
            border-bottom: 1px solid #eee !important;
        }

        .pac-item:last-child {
            border-bottom: none !important;
        }

        .pac-item:hover {
            background-color: #f0f0f0 !important;
        }

        .pac-item-selected {
            background-color: #e9ecef !important;
        }

        /* Mobile Tabs Toggle Styles - Beautiful Button */
        .profile-tabs-toggle {
            display: flex;
            width: 100%;
            padding: 18px 24px;
            background: linear-gradient(135deg, #f6d814 0%, rgba(246, 216, 20, 0.8) 100%);
            border: none;
            border-radius: 16px;
            margin-bottom: 20px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            align-items: center;
            justify-content: space-between;
            font-weight: 600;
            color: #fff;
            box-shadow: 0 4px 16px rgba(176, 210, 55, 0.35);
            position: relative;
            overflow: hidden;
            gap: 12px;
        }

        .profile-tabs-toggle::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .profile-tabs-toggle:hover::before {
            left: 100%;
        }

        .profile-tabs-toggle:hover {
            background: linear-gradient(135deg, rgba(246, 216, 20, 0.8) 0%, #f6d814 100%);
            box-shadow: 0 6px 16px rgba(176, 210, 55, 0.4);
            transform: translateY(-2px);
        }

        .profile-tabs-toggle:active {
            transform: translateY(0);
        }

        /* Hamburger Icon */
        .hamburger-icon {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            width: 26px;
            height: 20px;
            position: relative;
            z-index: 1;
            flex-shrink: 0;
        }

        .hamburger-line {
            width: 100%;
            height: 3px;
            background-color: #fff;
            border-radius: 4px;
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            transform-origin: center;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .profile-tabs-toggle[aria-expanded="true"] .hamburger-line:nth-child(1) {
            transform: translateY(8.5px) rotate(45deg);
        }

        .profile-tabs-toggle[aria-expanded="true"] .hamburger-line:nth-child(2) {
            opacity: 0;
            transform: scaleX(0);
        }

        .profile-tabs-toggle[aria-expanded="true"] .hamburger-line:nth-child(3) {
            transform: translateY(-8.5px) rotate(-45deg);
        }

        .profile-tabs-toggle .toggle-text {
            font-size: 17px;
            font-weight: 600;
            letter-spacing: 0.3px;
            flex: 1;
            text-align: right;
        }

        [dir="rtl"] .profile-tabs-toggle .toggle-text {
            text-align: left;
        }

        .profile-tabs-toggle .toggle-arrow {
            font-size: 14px;
            transition: transform 0.3s ease;
        }

        .profile-tabs-toggle[aria-expanded="true"] .toggle-arrow {
            transform: rotate(180deg);
        }

        /* Mobile Sidebar Overlay */
        .profile-sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 998;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
            backdrop-filter: blur(2px);
        }

        .profile-sidebar-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        /* Mobile Sidebar */
        .profile-sidebar-mobile {
            position: fixed;
            top: 0;
            right: 0;
            width: 85%;
            max-width: 320px;
            height: 100vh;
            background: #fff;
            z-index: 999;
            transform: translateX(100%);
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: -4px 0 20px rgba(0, 0, 0, 0.15);
            overflow-y: auto;
            overflow-x: hidden;
            -webkit-overflow-scrolling: touch;
            visibility: hidden;
            opacity: 0;
        }

        .profile-sidebar-mobile.active {
            transform: translateX(0) !important;
            visibility: visible;
            opacity: 1;
        }

        /* Custom scrollbar for sidebar */
        .profile-sidebar-mobile::-webkit-scrollbar {
            width: 6px;
        }

        .profile-sidebar-mobile::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .profile-sidebar-mobile::-webkit-scrollbar-thumb {
            background: #f6d814;
            border-radius: 3px;
        }

        .profile-sidebar-mobile::-webkit-scrollbar-thumb:hover {
            background: #AACC3B;
        }

        [dir="rtl"] .profile-sidebar-mobile {
            right: auto;
            left: 0;
            transform: translateX(-100%);
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.15);
        }


        [dir="rtl"] .profile-sidebar-mobile .asideCat_link:hover,
        [dir="rtl"] .profile-sidebar-mobile .asideCat_link.active_catlink {
            padding-right: 20px;
            padding-left: 24px;
        }

        .profile-sidebar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px;
            background: linear-gradient(135deg, #f6d814 0%, rgba(246, 216, 20, 0.8) 100%);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .profile-sidebar-title {
            font-size: 18px;
            font-weight: 700;
            color: #fff;
            margin: 0;
        }

        .profile-sidebar-close {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: none;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            font-size: 18px;
        }

        .profile-sidebar-close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }

        .profile-sidebar-mobile .asideCat_list {
            padding: 20px 0;
            margin: 0;
        }

        .profile-sidebar-mobile .asideCat_list {
            padding: 10px 0;
            margin: 0;
            list-style: none;
        }

        .profile-sidebar-mobile .asideCat_list li {
            margin: 0;
            padding: 0;
        }

        .profile-sidebar-mobile .asideCat_link {
            padding: 16px 20px;
            margin: 0;
            border-radius: 0;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 16px;
            text-decoration: none;
            color: #666060;
            border-bottom: 1px solid #f0f0f0;
            font-size: 16px;
        }

        .profile-sidebar-mobile .asideCat_link:hover {
            background-color: #f8f9fa;
            padding-right: 24px;
        }

        .profile-sidebar-mobile .asideCat_link.active_catlink {
            background-color: rgba(246, 216, 20, 0.1);
            color: #000;
            font-weight: 500;
            padding-right: 24px;
        }

        .profile-sidebar-mobile .asideCat_link:last-child {
            border-bottom: none;
        }

        .profile-sidebar-mobile .aslidCat_img {
            width: 24px;
            height: 24px;
            flex-shrink: 0;
        }

        /* Mobile Styles */
        @media (max-width: 991px) {
            .profile-tabs-toggle {
                display: flex !important;
            }

            body.sidebar-open {
                overflow: hidden;
                position: fixed;
                width: 100%;
            }
        }

        /* Desktop: Hide mobile elements */
        @media (min-width: 992px) {
            .profile-tabs-toggle,
            .profile-sidebar-overlay,
            .profile-sidebar-mobile {
                display: none !important;
            }
        }
@endsection
@section('website-footer')
    <script>
        $(document).ready(function() {
            // Mobile tabs toggle functionality
            $('#profileTabsToggle').on('click', function() {
                const $toggle = $(this);
                const $tabsList = $('#profileTabsList');
                const isExpanded = $toggle.attr('aria-expanded') === 'true';

                $toggle.attr('aria-expanded', !isExpanded);
                $tabsList.toggleClass('profile-tabs-open');
            });

            // Close tabs when a tab is clicked on mobile
            $(document).on('click', '.tab_link', function() {
                if ($(window).width() < 992) {
                    setTimeout(function() {
                        $('#profileTabsToggle').attr('aria-expanded', 'false');
                        $('#profileTabsList').removeClass('profile-tabs-open');
                    }, 300);
                }
            });

            // Load default tab content
            loadTabContent('profile_data');

            $('.tab_link').on('click', function(e) {
                e.preventDefault();
                var tab = $(this).data('tab');
                if (!tab) return;

                // Remove active class from all tab links (both in sidebar and content area)
                $('.tab_link').removeClass('active_catlink');

                // If the button clicked is from the wallet tab content (not from sidebar)
                if (!$(this).closest('.asideCat_column').length) {
                    // Check if tab exists in sidebar
                    var sidebarTab = $('.asideCat_list .tab_link[data-tab="' + tab + '"]');
                    if (sidebarTab.length) {
                        // Activate the corresponding sidebar tab
                        sidebarTab.addClass('active_catlink');
                    }
                } else {
                    // Regular sidebar tab click - add active to this button
                    $(this).addClass('active_catlink');
                }

                loadTabContent(tab);
            });

            // Special handler for "Add Money" button from wallet tab content
            $(document).on('click', 'button.tab_link[data-tab="add_money"]', function(e) {
                e.preventDefault();
                e.stopPropagation();


                // Load the add_money tab content directly
                loadTabContent('add_money');
            });

            function loadTabContent(tab) {
                $.get('/profile/tab/' + tab, function(response) {
                    $('#proCont_wrapper').html(response.content);
                });
            }

            // Use event delegation for remove favorite button (works with dynamically loaded content)
            $(document).on('click', '.remove_favorite_btn', function(e) {
                e.preventDefault();
                e.stopPropagation();

                var productId = $(this).data('product-id');
                var button = $(this);

                AppSwal.confirm({
                    title: '{{ __("website.are_you_sure_remove") }}',
                    text: '{{ __("website.product_removed_from_favorites") }}',
                    confirmButtonText: '{{ __("website.yes_remove") }}',
                    cancelButtonText: '{{ __("website.cancel") }}'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ url(app()->getLocale() . "/profile/remove-favorite") }}',
                            method: 'POST',
                            data: {
                                product_id: productId,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                button.closest('.col-12, .col-md-6').fadeOut(function() {
                                    $(this).remove();
                                });
                                AppSwal.success('{{ __("website.product_removed_from_favorites") }}', '{{ __("website.removed_from_favorites") }}');
                            },
                            error: function(xhr) {
                                AppSwal.error('{{ __("website.error_processing_favorite") }}', '{{ __("website.an_error_occurred") }}');
                            }
                        });
                    }
                });
            });

            // Use event delegation for dynamically loaded content
            $(document).on('click', '.edit_bttn', function() {
                let card = $(this).closest('.accountInfo_cardN');
                let editBtn = card.find('.edit_bttn');
                let saveBtn = card.find('.savePro_bttn');
                let displayField = card.find('.FName_des');
                let inputField = card.find('.hiddProf_input');

                // Hide edit button and display field, show save button and input field
                editBtn.hide();
                displayField.hide();
                
                if (card.find('.edit-controls').length) {
                    card.find('.edit-controls').css('display', 'flex');
                } else {
                    saveBtn.show();
                }
                
                inputField.show();
            });

            // Handlers for dynamically loaded content
            $(document).on('click', '.closePro_bttn', function() {
                let card = $(this).closest('.accountInfo_cardN');
                card.find('.hiddProf_input').hide();
                card.find('.FName_des').show();
                
                if (card.find('.edit-controls').length) {
                    card.find('.edit-controls').hide();
                } else {
                    card.find('.savePro_bttn').hide();
                }
                
                card.find('.edit-circle-btn').show();
            });

            $(document).on('click', '.btn-track-toggle', function() {
                const target = $(this).data('target');
                const $targetEl = $(target);
                
                $targetEl.slideToggle(400);
                $(this).toggleClass('btn-warning btn-dark bg-gold text-dark');
            });

            $(document).on('click', '.savePro_bttn', function(e) {
                e.preventDefault();
                let card = $(this).closest('.accountInfo_cardN');
                let editBtn = card.find('.edit_bttn');
                let saveBtn = card.find('.savePro_bttn');
                let displayField = card.find('.FName_des');
                let inputField = card.find('.hiddProf_input');
                // Get the field name from the input's name attribute or data attribute
                let field = '';
                let inputName = inputField.attr('name');
                let inputType = inputField.attr('type');

                // If input has name attribute, use it
                if (inputName) {
                    field = inputName;
                } else {
                    // Fallback: map based on input type and position
                    if (inputType === 'email') {
                        field = 'email';
                    } else if (inputType === 'password') {
                        field = 'password';
                    } else {
                        // Try to determine from the card's data attribute or class
                        let cardData = card.data('field');
                        if (cardData) {
                            field = cardData;
                        } else {
                            // Last resort: map based on input placeholder or label
                            let placeholder = inputField.attr('placeholder').toLowerCase();
                            if (placeholder.includes('email')) {
                                field = 'email';
                            } else if (placeholder.includes('mobile') || placeholder.includes('phone')) {
                                field = 'phone';
                            } else if (placeholder.includes('address')) {
                                field = 'address';
                            } else if (placeholder.includes('name')) {
                                field = 'name';
                            } else if (placeholder.includes('password')) {
                                field = 'password';
                            }
                        }
                    }
                }

                let value = inputField.val();

                // Debug: Log what we're getting

                // Handle password fields
                let currentPassword = '';
                if (field === 'password') {
                    let currentPasswordInput = card.find('input[name="current_password"]');
                    let newPasswordInput = card.find('input[name="new_password"]');
                    currentPassword = currentPasswordInput.val();
                    value = newPasswordInput.val();
                }

                // Handle phone field - require OTP verification first
                if (field === 'phone') {
                    // Get phone number and country
                    let phoneInput = card.find('#phone');
                    let countrySelect = card.find('#phone_country_id');
                    let newPhone = phoneInput.val();
                    let countryId = countrySelect.val();
                    let phoneCode = countrySelect.find('option:selected').data('phone-code') || '';

                    let currentPhone = '{{ auth()->user()->phone ?? "" }}';
                    let currentCountryId = '{{ auth()->user()->country_id ?? "" }}';

                    // Check if phone actually changed
                    if ((newPhone === currentPhone && countryId == currentCountryId) || (!newPhone && !currentCountryId)) {
                        // Phone didn't change, just close edit mode
                        displayField.show();
                        inputField.hide();
                        saveBtn.hide();
                        editBtn.show();
                        return;
                    }

                    if (!countryId) {
                        AppSwal.warning('{{ __("website.please_select_country") }}', '{{ __("website.error") }}');
                        return;
                    }

                    if (!newPhone || newPhone.length !== 8) {
                        AppSwal.warning('{{ __("website.please_enter_valid_phone") }}', '{{ __("website.error") }}');
                        return;
                    }

                    // Combine phone code and number for OTP
                    let fullPhone = phoneCode + newPhone;

                    // Check if OTP was already sent for this phone
                    if (!card.data('otp-sent') || card.data('otp-phone') !== fullPhone) {
                        // Send OTP first
                        sendPhoneChangeOtp(fullPhone, card, inputField, displayField, editBtn, saveBtn, countryId);
                        return;
                    }

                    // OTP was sent, show OTP input modal
                    showPhoneOtpModal(fullPhone, card, inputField, displayField, editBtn, saveBtn, newPhone, currentPassword, countryId);
                    return;
                }

                // Validate required fields
                if (!value && field !== 'phone' && field !== 'address') {
                    AppSwal.warning('{{ __("website.this_field_is_required") }}', '{{ __("website.this_field_is_required") }}');
                    return;
                }

                $.ajax({
                    url: '/profile/update',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        field: field,
                        value: value,
                        current_password: currentPassword
                    },
                    success: function(response) {
                        if (response.success) {
                            // Update display field with new value
                            displayField.text(response.value).show();
                            inputField.hide();
                            saveBtn.hide();
                            editBtn.show();

                            AppSwal.success(response.message || '{{ __("website.updated_successfully") }}', '{{ __("website.updated_successfully") }}');
                        } else {
                            AppSwal.error(response.error || '{{ __("website.update_failed") }}', '{{ __("website.update_failed") }}');
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = '{{ __("website.an_error_occurred") }}';

                        if (xhr.responseJSON) {
                            if (xhr.responseJSON.error) {
                                errorMessage = xhr.responseJSON.error;
                            } else if (xhr.responseJSON.messages) {
                                errorMessage = Object.values(xhr.responseJSON.messages).flat().join(', ');
                            }
                        }

                        // Show detailed error for debugging
                        if (xhr.responseJSON && xhr.responseJSON.allowed_fields) {
                            errorMessage += '\n\n{{ __("website.allowed_fields") }}: ' + xhr.responseJSON.allowed_fields.join(', ');
                            errorMessage += '\n{{ __("website.received_field") }}: ' + xhr.responseJSON.received_field;
                        }

                        AppSwal.error(errorMessage, '{{ __("website.an_error_occurred") }}');
                    }
                });
            });

            // Function to send OTP for phone change
            function sendPhoneChangeOtp(newPhone, card, inputField, displayField, editBtn, saveBtn, countryId) {
                $.ajax({
                    url: '{{ route("profile.send.phone.otp") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        phone: newPhone,
                        country_id: countryId
                    },
                    beforeSend: function() {
                        saveBtn.prop('disabled', true).text('{{ __("website.sending") }}...');
                    },
                    success: function(response) {
                        if (response.success) {
                            // Mark OTP as sent for this phone
                            card.data('otp-sent', true);
                            card.data('otp-phone', newPhone);

                            AppSwal.success(response.message || '{{ __("website.otp_sent_to_phone") }}', '{{ __("website.success") }}');

                            // Show OTP modal
                            showPhoneOtpModal(newPhone, card, inputField, displayField, editBtn, saveBtn, newPhone, '');
                        } else {
                            AppSwal.error(response.error || '{{ __("website.sms_sending_failed") }}', '{{ __("website.error") }}');
                            saveBtn.prop('disabled', false).text('{{ __("website.save") }}');
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = '{{ __("website.sms_sending_failed") }}';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMsg = xhr.responseJSON.error;
                        }
                        AppSwal.error(errorMsg, '{{ __("website.error") }}');
                        saveBtn.prop('disabled', false).text('{{ __("website.save") }}');
                    }
                });
            }

            // Function to show OTP modal for phone verification
            function showPhoneOtpModal(newPhone, card, inputField, displayField, editBtn, saveBtn, phoneValue, currentPassword, countryId) {
                Swal.fire({
                    title: '{{ __("website.verify_otp") }}',
                    html: `
                        <p>{{ __("website.enter_otp_sent_to_phone") }}: ${newPhone}</p>
                        <input type="text" id="phone_otp_code" class="swal2-input"
                               placeholder="{{ __("website.enter_otp_code") }}"
                               maxlength="6" style="font-size: 24px; letter-spacing: 8px; text-align: center;">
                        <button type="button" class="btn btn-link mt-2" id="resend-phone-otp-btn">
                            {{ __("website.resend_otp") }}
                        </button>
                    `,
                    showCancelButton: true,
                    confirmButtonText: '{{ __("website.verify") }}',
                    cancelButtonText: '{{ __("website.cancel") }}',
                    inputValidator: (value) => {
                        if (!value || value.length !== 6) {
                            return '{{ __("website.please_enter_valid_otp") }}';
                        }
                    },
                    preConfirm: () => {
                        return document.getElementById('phone_otp_code').value;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const otpCode = result.value;

                        // Update phone with OTP verification
                        $.ajax({
                            url: '/profile/update',
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                field: 'phone',
                                value: phoneValue,
                                country_id: countryId,
                                otp_code: otpCode,
                                current_password: currentPassword
                            },
                            success: function(response) {
                                if (response.success) {
                                    // Update display field with new value
                                    displayField.text(response.value || phoneValue).show();
                                    inputField.hide().val(phoneValue);
                                    saveBtn.hide();
                                    editBtn.show();

                                    // Clear OTP data
                                    card.removeData('otp-sent');
                                    card.removeData('otp-phone');

                                    AppSwal.success(response.message || '{{ __("website.phone_updated_successfully") }}', '{{ __("website.updated_successfully") }}');
                                } else {
                                    AppSwal.error(response.error || '{{ __("website.update_failed") }}', '{{ __("website.update_failed") }}');
                                }
                            },
                            error: function(xhr) {
                                let errorMessage = '{{ __("website.an_error_occurred") }}';
                                if (xhr.responseJSON && xhr.responseJSON.error) {
                                    errorMessage = xhr.responseJSON.error;
                                }
                                AppSwal.error(errorMessage, '{{ __("website.an_error_occurred") }}');
                            }
                        });
                    }
                });

                // Handle resend OTP button
                setTimeout(() => {
                    $('#resend-phone-otp-btn').on('click', function() {
                        sendPhoneChangeOtp(newPhone, card, inputField, displayField, editBtn, saveBtn, countryId);
                    });
                }, 100);
            }

            // Handle address field edit - show address selection modal
            $(document).on('click', '.accountInfo_cardN[data-field="address"] .edit_bttn', function() {
                @auth('web')
                // Open address selection modal
                const modalElement = document.getElementById('profileAddressSelectionModal');
                if (modalElement) {
                    try {
                        let modal = bootstrap.Modal.getInstance(modalElement);
                        if (!modal) {
                            modal = new bootstrap.Modal(modalElement);
                        }
                        modal.show();

                        // Load addresses and initialize map
                        loadProfileAddresses();
                    } catch (error) {
                    }
                }
                @else
                // For non-authenticated users, just show the input field
                let card = $(this).closest('.accountInfo_cardN');
                let editBtn = card.find('.edit_bttn');
                let saveBtn = card.find('.savePro_bttn');
                let displayField = card.find('.FName_des');
                let inputField = card.find('.hiddProf_input');

                editBtn.hide();
                displayField.hide();
                saveBtn.show();
                inputField.show();
                @endauth
            });

            // Profile Address Selection Modal Functions
            let profileAddressMap = null;
            let profileAddressMarker = null;
            let profileAddressAutocomplete = null;
            let selectedProfileAddress = null;

            function loadProfileAddresses() {
                $.ajax({
                    url: '{{ route("addresses.index") }}',
                    method: 'GET',
                    success: function(response) {
                        if (response.success && response.addresses) {
                            const addressesList = $('#profile-addresses-list');
                            const noAddresses = $('#profile-no-addresses');

                            if (response.addresses.length === 0) {
                                addressesList.html('');
                                noAddresses.show();
                            } else {
                                noAddresses.hide();
                                let html = '';

                                response.addresses.forEach(function(address) {
                                    const isMain = address.is_main ? '<span class="badge bg-primary ms-2">{{ __("website.main_address") }}</span>' : '';
                                    const isSelected = selectedProfileAddress && selectedProfileAddress.id == address.id ? 'selected' : '';

                                    html += `
                                        <div class="list-group-item profile-address-item ${isSelected}"
                                             data-address-id="${address.id}"
                                             data-lat="${address.latitude || ''}"
                                             data-lng="${address.longitude || ''}"
                                             data-address-text="${address.full_address || address.address || ''}">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1">
                                                        ${address.title || '{{ __("website.address") }} #' + address.id}
                                                        ${isMain}
                                                    </h6>
                                                    <p class="mb-1 small text-muted">${address.full_address || address.address || ''}</p>
                                                </div>
                                                ${!address.is_main ? `
                                                    <button type="button" class="btn btn-sm btn-outline-primary set-main-profile-address-btn"
                                                            data-address-id="${address.id}">
                                                        <i class="fa fa-star"></i>
                                                    </button>
                                                ` : ''}
                                            </div>
                                        </div>
                                    `;
                                });

                                addressesList.html(html);

                                // Add click handlers
                                $('.profile-address-item').on('click', function() {
                                    $('.profile-address-item').removeClass('selected');
                                    $(this).addClass('selected');

                                    const addressId = $(this).data('address-id');
                                    const lat = parseFloat($(this).data('lat'));
                                    const lng = parseFloat($(this).data('lng'));
                                    const addressText = $(this).data('address-text');

                                    selectedProfileAddress = {
                                        id: addressId,
                                        lat: lat,
                                        lng: lng,
                                        address: addressText
                                    };

                                    // Update map
                                    if (lat && lng && !isNaN(lat) && !isNaN(lng)) {
                                        if (profileAddressMap) {
                                            const position = { lat: lat, lng: lng };
                                            profileAddressMap.setCenter(position);
                                            profileAddressMap.setZoom(15);
                                            if (profileAddressMarker) {
                                                profileAddressMarker.setPosition(position);
                                            } else {
                                                profileAddressMarker = new google.maps.Marker({
                                                    position: position,
                                                    map: profileAddressMap,
                                                    draggable: false
                                                });
                                            }
                                        }
                                    }

                                    // Update selected address display
                                    $('#profile-address-display').text(addressText);
                                    $('#profile-selected-address').show();
                                    $('#profile-select-address-btn').prop('disabled', false);
                                });
                            }

                            // Initialize map if not already done
                            if (!profileAddressMap) {
                                // Small delay to ensure modal is fully rendered
                                setTimeout(function() {
                                    initProfileAddressMap();
                                }, 200);
                            } else {
                                // Map already initialized, trigger resize to ensure it displays correctly
                                setTimeout(function() {
                                    if (profileAddressMap) {
                                        google.maps.event.trigger(profileAddressMap, 'resize');
                                    }
                                }, 100);
                            }
                        }
                    },
                    error: function() {
                        AppSwal.error('{{ __("website.failed_to_load_addresses") ?? "Failed to load addresses" }}');
                    }
                });
            }

            function initProfileAddressMap() {
                const mapContainer = document.getElementById('profile-address-selection-map');
                if (!mapContainer) {
                    return;
                }

                // Check if container is visible
                if (mapContainer.offsetWidth === 0 || mapContainer.offsetHeight === 0) {
                    setTimeout(function() {
                        initProfileAddressMap();
                    }, 200);
                    return;
                }


                // Wait for Google Maps API to be fully loaded
                let retryCount = 0;
                const maxRetries = 50; // 5 seconds max wait

                function waitForGoogleMaps() {
                    if (typeof google !== 'undefined' && typeof google.maps !== 'undefined' && typeof google.maps.places !== 'undefined') {

                        // Default location (Kuwait)
                        const defaultLat = 29.3759;
                        const defaultLng = 47.9774;

                        try {
                            // Initialize map
                            profileAddressMap = new google.maps.Map(mapContainer, {
                                center: { lat: defaultLat, lng: defaultLng },
                                zoom: 12,
                                reverseButtons: true,
                                mapTypeControl: true,
                                streetViewControl: true,
                                fullscreenControl: true
                            });


                            // Trigger resize to ensure map displays correctly
                            setTimeout(function() {
                                if (profileAddressMap) {
                                    google.maps.event.trigger(profileAddressMap, 'resize');
                                }
                            }, 100);

                            // Initialize Autocomplete
                            const searchInput = document.getElementById('profile-address-selection-search');
                            if (searchInput) {
                                try {
                                    profileAddressAutocomplete = new google.maps.places.Autocomplete(searchInput, {
                                        types: ['geocode', 'establishment'],
                                        fields: ['geometry', 'formatted_address', 'address_components', 'name']
                                    });


                                    // Bias Autocomplete results towards current map's viewport
                                    profileAddressMap.addListener('bounds_changed', function() {
                                        const bounds = profileAddressMap.getBounds();
                                        if (bounds && profileAddressAutocomplete) {
                                            profileAddressAutocomplete.setBounds(bounds);
                                        }
                                    });

                                    // When a place is selected from autocomplete
                                    profileAddressAutocomplete.addListener('place_changed', function() {
                                        const place = profileAddressAutocomplete.getPlace();

                                        if (!place.geometry || !place.geometry.location) {
                                            return;
                                        }

                                        // Update marker
                                        if (profileAddressMarker) {
                                            profileAddressMarker.setPosition(place.geometry.location);
                                        } else {
                                            profileAddressMarker = new google.maps.Marker({
                                                position: place.geometry.location,
                                                map: profileAddressMap,
                                                draggable: false
                                            });
                                        }

                                        // Center map on selected place
                                        if (place.geometry.viewport) {
                                            profileAddressMap.fitBounds(place.geometry.viewport);
                                        } else {
                                            profileAddressMap.setCenter(place.geometry.location);
                                            profileAddressMap.setZoom(15);
                                        }

                                        // Update selected address display
                                        $('#profile-address-display').text(place.formatted_address || place.name || '');
                                        $('#profile-selected-address').show();
                                        $('#profile-select-address-btn').prop('disabled', false);

                                        selectedProfileAddress = {
                                            id: null,
                                            lat: place.geometry.location.lat(),
                                            lng: place.geometry.location.lng(),
                                            address: place.formatted_address || place.name || ''
                                        };
                                    });
                                } catch (error) {
                                }
                            } else {
                            }

                            // Update lat/lng when map is clicked
                            profileAddressMap.addListener('click', function(event) {
                                const lat = event.latLng.lat();
                                const lng = event.latLng.lng();

                                if (profileAddressMarker) {
                                    profileAddressMarker.setPosition(event.latLng);
                                } else {
                                    profileAddressMarker = new google.maps.Marker({
                                        position: event.latLng,
                                        map: profileAddressMap,
                                        draggable: false
                                    });
                                }

                                // Reverse geocode to get address
                                const geocoder = new google.maps.Geocoder();
                                geocoder.geocode({ location: event.latLng }, function(results, status) {
                                    if (status === 'OK' && results[0]) {
                                        const address = results[0].formatted_address;
                                        $('#profile-address-display').text(address);
                                        $('#profile-selected-address').show();
                                        $('#profile-select-address-btn').prop('disabled', false);

                                        selectedProfileAddress = {
                                            id: null,
                                            lat: lat,
                                            lng: lng,
                                            address: address
                                        };
                                    }
                                });
                            });

                            // Locate Me button
                            $('#locate-me-profile').on('click', function() {
                                const locateBtn = $(this);
                                const originalText = locateBtn.html();

                                if (!navigator.geolocation) {
                                    AppSwal.error('{{ __("website.geolocation_not_supported") ?? "Geolocation is not supported by your browser" }}');
                                    return;
                                }

                                locateBtn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> {{ __("website.locating") ?? "Locating..." }}');

                                navigator.geolocation.getCurrentPosition(
                                    function(position) {
                                        const lat = position.coords.latitude;
                                        const lng = position.coords.longitude;
                                        const userLocation = { lat: lat, lng: lng };

                                        profileAddressMap.setCenter(userLocation);
                                        profileAddressMap.setZoom(15);

                                        if (profileAddressMarker) {
                                            profileAddressMarker.setPosition(userLocation);
                                        } else {
                                            profileAddressMarker = new google.maps.Marker({
                                                position: userLocation,
                                                map: profileAddressMap,
                                                draggable: false
                                            });
                                        }

                                        // Reverse geocode to get address
                                        const geocoder = new google.maps.Geocoder();
                                        geocoder.geocode({ location: userLocation }, function(results, status) {
                                            if (status === 'OK' && results[0]) {
                                                const address = results[0].formatted_address;
                                                $('#profile-address-display').text(address);
                                                $('#profile-selected-address').show();
                                                $('#profile-select-address-btn').prop('disabled', false);

                                                selectedProfileAddress = {
                                                    id: null,
                                                    lat: lat,
                                                    lng: lng,
                                                    address: address
                                                };
                                            }
                                        });

                                        locateBtn.prop('disabled', false).html(originalText);
                                    },
                                    function(error) {
                                        locateBtn.prop('disabled', false).html(originalText);

                                        let errorMessage = '{{ __("website.failed_to_get_location") ?? "Failed to get your location" }}';

                                        switch(error.code) {
                                            case error.PERMISSION_DENIED:
                                                errorMessage = '{{ __("website.location_permission_denied") ?? "Location access denied. Please enable location permissions in your browser settings." }}';
                                                break;
                                            case error.POSITION_UNAVAILABLE:
                                                errorMessage = '{{ __("website.location_unavailable") ?? "Location information is unavailable. Please try again." }}';
                                                break;
                                            case error.TIMEOUT:
                                                errorMessage = '{{ __("website.location_timeout") ?? "Location request timed out. Please try again." }}';
                                                break;
                                        }

                                        AppSwal.error(errorMessage);
                                    },
                                    {
                                        enableHighAccuracy: true,
                                        timeout: 15000,
                                        maximumAge: 0
                                    }
                                );
                            });
                        } catch (error) {
                                AppSwal.error('Error initializing map: ' + error.message);
                        }
                    } else {
                        retryCount++;
                        if (retryCount >= maxRetries) {
                                AppSwal.error('Google Maps API failed to load. Please refresh the page.');
                            return;
                        }
                        // Retry after a short delay
                        setTimeout(waitForGoogleMaps, 100);
                    }
                }

                waitForGoogleMaps();
            }

            // Make functions globally accessible for debugging
            window.profileAddressMap = function() { return profileAddressMap; };
            window.initProfileAddressMap = initProfileAddressMap;
            window.loadProfileAddresses = loadProfileAddresses;

            // Make setMainProfileAddress globally accessible
            window.setMainProfileAddress = function(addressId) {
                $.ajax({
                    url: '{{ route("addresses.set.main", ":id") }}'.replace(':id', addressId),
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            AppSwal.success(response.message);
                            loadProfileAddresses();
                        }
                    },
                    error: function(xhr) {
                        AppSwal.error(xhr.responseJSON?.message || '{{ __("website.something_went_wrong") }}');
                    }
                });
            };

            // Use event delegation for set main address button
            $(document).on('click', '.set-main-profile-address-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const addressId = $(this).data('address-id');
                if (addressId) {
                    window.setMainProfileAddress(addressId);
                }
            });

            // Select address button
            $('#profile-select-address-btn').on('click', function() {
                if (!selectedProfileAddress) {
                    AppSwal.warning('{{ __("website.please_select_an_address") ?? "Please select an address" }}');
                    return;
                }

                // Update profile address field
                const addressCard = $('.accountInfo_cardN[data-field="address"]');
                const displayField = addressCard.find('.FName_des');
                const inputField = addressCard.find('.hiddProf_input');
                const editBtn = addressCard.find('.edit_bttn');
                const saveBtn = addressCard.find('.savePro_bttn');

                // Update the address value
                inputField.val(selectedProfileAddress.address);

                // Save the address
                $.ajax({
                    url: '/profile/update',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        field: 'address',
                        value: selectedProfileAddress.address
                    },
                    success: function(response) {
                        if (response.success) {
                            displayField.text(response.value || selectedProfileAddress.address).show();
                            inputField.hide();
                            saveBtn.hide();
                            editBtn.show();

                            // Close modal
                            const modalElement = document.getElementById('profileAddressSelectionModal');
                            if (modalElement) {
                                const modal = bootstrap.Modal.getInstance(modalElement);
                                if (modal) {
                                    modal.hide();
                                }
                            }

                            AppSwal.success(response.message || '{{ __("website.address_updated_successfully") ?? "Address updated successfully" }}', '{{ __("website.updated_successfully") }}');
                        } else {
                            AppSwal.error(response.error || '{{ __("website.update_failed") }}', '{{ __("website.update_failed") }}');
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = '{{ __("website.an_error_occurred") }}';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        }
                        AppSwal.error(errorMessage);
                    }
                });
            });

            // Initialize map when modal is shown
            $(document).on('shown.bs.modal', '#profileAddressSelectionModal', function() {
                // Small delay to ensure modal is fully rendered
                setTimeout(function() {
                    if (!profileAddressMap) {
                        initProfileAddressMap();
                    } else {
                        // Refresh map if already initialized
                        google.maps.event.trigger(profileAddressMap, 'resize');
                    }
                }, 300);
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            // Handle favorite button click (toggle favorite)
            $('.absAdd_fav').on('click', function(e) {
                e.preventDefault();

                // Check if user is authenticated
                @auth('web')
                var button = $(this);
                var productId = button.data('product-id');
                var isFavorited = button.hasClass('favorited');

                $.ajax({
                    url: isFavorited ? '{{ url(app()->getLocale() . "/profile/remove-favorite") }}' : '{{ url(app()->getLocale() . "/profile/add-favorite") }}',
                    method: 'POST',
                    data: {
                        product_id: productId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            button.toggleClass('favorited');

                            // Update background instead of image
                            if (button.hasClass('favorited')) {
                                button.css('background-color', '#ff4444'); // Red background when favorited
                                AppSwal.success('{{ __("website.product_added_to_favorites") }}', '{{ __("website.added_to_favorites") }}');
                            } else {
                                button.css('background-color', '#fff'); // White background when not favorited
                                AppSwal.success('{{ __("website.product_removed_from_favorites") }}', '{{ __("website.removed_from_favorites") }}');
                            }
                        }
                    },
                    error: function(xhr) {
                        AppSwal.error('{{ __("website.error_processing_favorite") }}');
                    }
                });
                @else
                // User not authenticated - redirect to login
                AppSwal.confirm({
                    title: '{{ __("website.login_required") }}',
                    text: '{{ __("website.please_login_to_add_favorites") }}',
                    confirmButtonText: '{{ __("website.login") }}',
                    cancelButtonText: '{{ __("website.cancel") }}'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '{{ route("website.login") }}';
                    }
                });
                @endauth
            });
        });
    </script>

    <!-- Google Maps API -->
    @if(config('services.google_maps_api_key'))
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps_api_key') }}&libraries=places" async defer></script>
    @else
    <script>
    </script>
    @endif
@endsection
