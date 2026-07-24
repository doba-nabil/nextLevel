@extends('website.layout.master')
@section('title', __('website.verify_otp'))

@section('website-head')
    <link rel="stylesheet" href="{{ asset('website/assets/css/auth_redesign.css') }}">
    <link rel="stylesheet" href="{{ asset('website/assets/css/website-custom.css') }}">
@endsection

@section('website-main')
    @php
        $settingModel = \App\Models\Setting::first();
        $logoUrl = $settingModel?->getFirstMediaUrl('logo') ?: asset('website/assets/img/logo.png');
    @endphp
    <!-- Content -->
    <section class="login_section">
        <form action="{{ route('website.otp.verify.phone.post') }}" method="post" class="w-100 d-flex justify-content-center">
            @csrf
            <div class="login_wrap">
                <a href="{{ route('website.home') }}" class="inside_logo">
                    <img src="{{ $logoUrl }}" alt="{{ \App\Models\Setting::getValue('site_name', app()->getLocale(), 'Run2Diet') }}">
                </a>
                <h3 class="login_title mb-3">
                    <span class="login_titleText">{{ __('website.verify_otp') }}</span>
                </h3>
                <p class="login_desc mbttom_50 text-center">
                    {{ __('website.verify_otp_sent_to_phone') ?? 'Please enter the OTP code sent to your phone via SMS' }}
                </p>

                @if (session('info'))
                    <div class="alert alert-info mb-3">
                        {{ session('info') }}
                    </div>
                @endif

                @if ($errors->has('otp_code'))
                    <div class="alert alert-danger mb-3">
                        {{ $errors->first('otp_code') }}
                    </div>
                @endif

                @if (isset($show_otp) && $show_otp && isset($otp_code))
                <div class="alert alert-info mb-3 text-center">
                    <strong>{{ __('website.your_otp_code') }}:</strong>
                    <div style="font-size: 24px; font-weight: bold; color: #007bff; letter-spacing: 4px; margin-top: 10px;">
                        {{ $otp_code }}
                    </div>
                    <small class="text-muted">{{ __('website.this_otp_is_for_testing_only') }}</small>
                </div>
                @endif
                
                <div class="lgoin_form">
                    <label for="otp_code" class="login_label">{{ __('website.otp_code') }}</label>
                    <div class="form_group mbttom_40">
                        <input
                            id="otp_code"
                            name="otp_code"
                            type="text"
                            class="login_input"
                            placeholder="******"
                            maxlength="6"
                            pattern="[0-9]{6}"
                            inputmode="numeric"
                            value="{{ isset($otp_code) && isset($show_otp) && $show_otp ? $otp_code : '' }}"
                            required
                        >
                        <i class="code_icon absinput_icon"></i>
                    </div>
                    <button type="submit" class="main_bttn login_bttn w-100 hvr-sweep-to-right mbttom_20">
                        {{ __('website.verify_account') }}
                    </button>
                    
                    <br>
                    <button type="button" id="resend-otp" class="main_bttn login_bttn w-100 hvr-sweep-to-right">
                        {{ __('website.resend_otp') }}
                    </button>

                    <p id="resend-message" class="text-center mt-2 text-success" style="display:none;">
                        {{ __('website.otp_sent_again') }}
                    </p>
                </div>
            </div>
        </form>
    </section>
@endsection

@section('website-footer')
    <script>
        $(document).ready(function() {
            $('#resend-otp').on('click', function() {
                const $btn = $(this);
                const $message = $('#resend-message');
                
                $btn.prop('disabled', true).text('{{ __("website.sending") }}...');

                $.ajax({
                    url: '{{ route("website.otp.resend.phone") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status === 200) {
                            $message.text(response.message || '{{ __("website.otp_sent_again") }}').fadeIn();
                            
                            // If OTP is shown in response (test mode), update the input
                            if (response.otp && response.show_otp) {
                                $('#otp_code').val(response.otp);
                                $('.alert-info').html(
                                    '<strong>{{ __("website.your_otp_code") }}:</strong>' +
                                    '<div style="font-size: 24px; font-weight: bold; color: #007bff; letter-spacing: 4px; margin-top: 10px;">' +
                                    response.otp + '</div>' +
                                    '<small class="text-muted">{{ __("website.this_otp_is_for_testing_only") }}</small>'
                                ).show();
                            }
                            
                            setTimeout(function() {
                                $message.fadeOut();
                            }, 5000);
                        } else {
                            alert(response.message || '{{ __("website.something_went_wrong") }}');
                        }
                        $btn.prop('disabled', false).text('{{ __("website.resend_otp") }}');
                    },
                    error: function(xhr) {
                        let errorMsg = '{{ __("website.something_went_wrong") }}';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        alert(errorMsg);
                        $btn.prop('disabled', false).text('{{ __("website.resend_otp") }}');
                    }
                });
            });
        });
    </script>
@endsection
