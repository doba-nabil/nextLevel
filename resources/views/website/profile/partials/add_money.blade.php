<div class="proCont_wrapper">
    <h3 class="profile-section-title">
        <i class="fa-solid fa-money-bill-transfer gold-icon"></i>
        {{ __('website.add_money') }}
    </h3>

    <div class="profile-content-card">
        <p class="text-muted fw-bold mb-4"> {{ __('website.how_much_add') }} </p>

        @if(session('error'))
            <div class="alert alert-danger rounded-4">
                {{ session('error') }}
            </div>
        @endif

        <form id="addMoneyForm" action="{{ route('profile.add.money') }}" method="POST">
            @csrf
            <div class="form-group mb-5">
                <label for="amount" class="info-label mb-2">{{ __('website.amount') }}</label>
                <div class="input-group input-group-lg">
                    <input type="number" name="amount" id="amount" class="form-control premium-input text-center fw-bold"
                           style="font-size: 24px; height: 60px;"
                           placeholder="0.000"
                           min="0.001" max="10000" step="0.001"
                           value="{{ old('amount') }}" required>
                    <span class="input-group-text premium-input bg-light border-start-0 px-4" style="height: 60px; font-weight: 800; border-radius: 0 12px 12px 0;">
                        {{ \App\Models\Currency::getCurrentCurrencySign() }}
                    </span>
                </div>
                @error('amount')
                    <small class="text-danger d-block mt-2">{{ $message }}</small>
                @enderror
            </div>

            <h5 class="fw-bold mb-4"> {{ __('website.payment_method') }} </h5>
            <div class="row g-3 mb-5 payment-grid">
                <!-- KNET -->
                <div class="col-6 col-md-3">
                    <input type="radio" class="d-none payment-radio" name="payment_method" value="knet" id="knet_payment" data-payment-type="knet" data-pay-type="knet" checked>
                    <label for="knet_payment" class="payment-method-card">
                        <img src="{{ asset('website') }}/assets/img/knet.png" alt="" class="method-img">
                        <div class="method-name"> KNET </div>
                        <div class="check-icon"><i class="fa-solid fa-circle-check"></i></div>
                    </label>
                </div>

                <!-- Credit Card -->
                <div class="col-6 col-md-3">
                    <input type="radio" class="d-none payment-radio" name="payment_method" value="credit" id="credit_payment" data-payment-type="credit" data-pay-type="credit">
                    <label for="credit_payment" class="payment-method-card">
                        <img src="{{ asset('website') }}/assets/img/visa.png" alt="Credit Card" class="method-img" onerror="this.src='{{ asset('website/assets/img/knet.png') }}'">
                        <div class="method-name"> {{ __('website.credit_card') }} </div>
                        <div class="check-icon"><i class="fa-solid fa-circle-check"></i></div>
                    </label>
                </div>

                <!-- AMEX -->

                <!-- Apple Pay -->
                <div class="col-6 col-md-3">
                    <input type="radio" class="d-none payment-radio" name="payment_method" value="applepay" id="applepay_payment" data-payment-type="applepay" data-pay-type="applepay">
                    <label for="applepay_payment" class="payment-method-card">
                        <img src="{{ asset('website') }}/assets/img/apay.png" alt="Apple Pay" class="method-img" onerror="this.src='{{ asset('website/assets/img/knet.png') }}'">
                        <div class="method-name"> Apple Pay </div>
                        <div class="check-icon"><i class="fa-solid fa-circle-check"></i></div>
                    </label>
                </div>
            </div>

            <input type="hidden" name="pay_type" id="pay_type_input" value="knet">

            <button type="submit" class="main_bttn mid_bttn w-100 py-3" id="submitBtn">
                <i class="fa fa-wallet me-2"></i>
                {{ __('website.proceed_to_payment') }}
            </button>
        </form>
    </div>
</div>


<script>
$(document).ready(function() {
    // Function to update pay_type
    function updatePayType() {
        const selectedMethod = $('input[name="payment_method"]:checked');
        if (selectedMethod.length) {
            const payType = selectedMethod.data('pay-type') || selectedMethod.val() || 'knet';
            $('#pay_type_input').val(payType);
            console.log('Payment method changed, pay_type set to:', payType, 'Method:', selectedMethod.attr('id'), 'Value:', selectedMethod.val());
        }
    }

    // Update pay_type when payment method changes
    $('input[name="payment_method"]').on('change', function() {
        updatePayType();
    });

    // Also listen to label clicks (since inputs are hidden)
    $('.payment-method-card').on('click', function(e) {
        const inputId = $(this).attr('for');
        const $input = $('#' + inputId);
        if ($input.length) {
            $input.prop('checked', true).trigger('change');
        }
    });

    // Trigger change on page load
    updatePayType();

    $('#addMoneyForm').on('submit', function(e) {
        // Ensure pay_type is set before submit
        updatePayType();

        // Log final values before submit
        const selectedMethod = $('input[name="payment_method"]:checked');
        const payType = $('#pay_type_input').val();
        console.log('Form submitting:', {
            'payment_method': selectedMethod.val(),
            'pay_type': payType,
            'method_id': selectedMethod.attr('id'),
            'data_pay_type': selectedMethod.data('pay-type'),
            'all_form_data': $(this).serialize()
        });

        var $submitBtn = $('#submitBtn');
        $submitBtn.prop('disabled', true);
        $submitBtn.html('<i class="fa fa-spinner fa-spin me-2"></i> {{ __("website.processing") }}...');
    });
});
</script>
