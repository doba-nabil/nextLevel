@extends('website.layout.master')
@section('title', __('website.delivery'))
@section('body', 'bg-white grey_mob')
@section('website-main')

    <section class="pickup_section secPadding pt_sm_0 pt-5">
        <div class="container px-lg-0">
            <a href="cart.html" class="back_link mb-2 d__mob__none">
                <i class="back_icon"></i>
                <span> {{ __('website.checkout') }} </span>
            </a>
            <div class="row">
                <div class="mealCol_wrap col-12 col-lg-8 mx-lg-auto">
                    <ul style="display: none" class="nav nav-pills basket_pills mbttom_40">
                        <li class="nav-item">
                            <a class="nav-link active" id="delivery_tab" data-bs-toggle="tab"
                               data-bs-target="#delivery_tab_pane" type="button" role="tab"
                               aria-controls="delivery_tab_pane" aria-selected="true"> {{ __('website.delivery') }} </a>
                        </li>
                        @if(count($branches) > 0)
                            <li class="nav-item">
                                <a class="nav-link" id="pickup_tab" data-bs-toggle="tab"
                                   data-bs-target="#pickup_tab_pane" type="button" role="tab"
                                   aria-controls="pickup_tab_pane" aria-selected="false"> {{ __('website.pick_up') }} </a>
                            </li>
                        @endif
                    </ul>
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active" id="delivery_tab_pane" role="tabpanel"
                             aria-labelledby="delivery_tab" tabindex="0">
                            <div class="DPick_Wrap">
                                <div class="tab-content" id="myTabContent">
                                    <div class="tab-pane fade show active" id="delivery-tab-pane" role="tabpanel"
                                         aria-labelledby="delivery-tab" tabindex="0">
                                        <form action="{{ route('checkout.store') }}" method="post" class="search_form">
                                            @csrf
                                            <input name="order_type" value="delivery" hidden/>

                                            @guest('web')
                                            <div class="DPick_formDIV mb-4">
                                                <h3 class="asideSM_title"> {{ __('website.contact_information') }} </h3>
                                                <div class="row">
                                                    <div class="col-12 col-lg-6">
                                                        <label for="guest_name" class="login_label"> {{ __('website.full_name') }} <span class="text-danger">*</span> </label>
                                                        <div class="form_group mbttom_30">
                                                            <input type="text" name="guest_name" id="guest_name" class="login_input" placeholder="{{ __('website.enter_your_full_name') }}" required>
                                                            <i class="name_icon absinput_icon"></i>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-lg-6">
                                                        <label for="guest_phone" class="login_label"> {{ __('website.mobile_no') }} <span class="text-danger">*</span> </label>
                                                        <div class="form_group mbttom_30">
                                                            <input type="tel" name="guest_phone" id="guest_phone" class="login_input" placeholder="{{ __('website.enter_your_mobile_number') }}" required>
                                                            <i class="phone_icon absinput_icon"></i>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-lg-6">
                                                        <label for="guest_email" class="login_label"> {{ __('website.email') }} <span class="text-danger">*</span> </label>
                                                        <div class="form_group mbttom_30">
                                                            <input type="email" name="guest_email" id="guest_email" class="login_input" placeholder="{{ __('website.enter_your_email') }}" required>
                                                            <i class="email_icon absinput_icon"></i>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-lg-6">
                                                        <label for="guest_address" class="login_label"> {{ __('website.address') }} <span class="text-danger">*</span> </label>
                                                        <div class="form_group mbttom_30">
                                                            <input type="text" name="guest_address" id="guest_address" class="login_input" placeholder="{{ __('website.enter_your_address') }}" required>
                                                            <i class="name_icon absinput_icon"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endguest

                                            @auth('web')
                                            <iframe
                                                width="100%" height="489" style="border:0;" allowfullscreen=""
                                                loading="lazy"
                                                frameborder="0"
                                                style="border:0"
                                                src="https://www.google.com/maps?q={{ auth('web')->user()->lat ?? '29.3759' }},{{ auth('web')->user()->long ?? '47.9774' }}&hl=ar&z=15&output=embed"
                                                allowfullscreen>
                                            </iframe>
                                            @else
                                            <label for="address" class="login_label"> {{ __('website.delivery_address') }} <span class="text-danger">*</span> </label>
                                            <div class="form_group mbttom_30">
                                                <textarea name="address" id="address" class="login_input" placeholder="{{ __('website.enter_delivery_address') }}" rows="4" required></textarea>
                                            </div>
                                            @endauth

                                            <div class="buttons_wrapper w-100 ">
                                                <button type="submit" class="main_bttn hvr-sweep-to-right">
                                                    {{ __('website.continue_to_checkout') }} </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if(count($branches) > 0)
                            <div class="tab-pane fade" id="pickup_tab_pane" role="tabpanel" aria-labelledby="pickup_tab"
                                 tabindex="0">
                                <div class="DPick_Wrap">
                                    <form action="{{ route('checkout.store') }}" method="post" class="DPick_form">
                                        @csrf
                                        <input name="order_type" value="pick_up" hidden/>
                                        <div class="DPick_formDIV">
                                            <h3 class="asideSM_title">{{ __('website.pickup_information') }}</h3>
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="formSc_group formSc_home">
                                                        <select name="branch_id" class="datePick_input">
                                                            @foreach($branches as $branch)
                                                                <option
                                                                    value="{{ $branch->id }}"> {{ $branch->name }} </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="DPick_formDIV">
                                            <h3 class="asideSM_title">{{ __('website.pick_up_time') }}</h3>

                                            <label class="DPick_label">
                                                <input type="radio" name="meal_type" value="asap" class="DPick_checkbox">
                                                <span>{{ __('website.as_soon_as_possible') }}</span>
                                            </label>

                                            <label class="DPick_label">
                                                <input type="radio" name="meal_type" value="scheduled" class="DPick_checkbox" checked>
                                                <span>{{ __('website.scheduled') }}</span>
                                            </label>

                                            <div class="row schedule_fields">
                                                <div class="col-12 col-lg-5">
                                                    <div class="formSc_group">
                                                        <input class="login_input" name="scheduled_date" type="date">
                                                    </div>
                                                </div>
                                                <div class="col-12 col-lg-5">
                                                    <div class="formSc_group">
                                                        <input class="login_input" name="scheduled_time" type="time">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="DPick_formDIV">
                                            <h3 class="asideSM_title"> {{ __('website.contact_information') }} </h3>
                                            <div class="row">
                                                <div class="col-12 col-lg-6">
                                                    <label for="name" class="login_label"> {{ __('website.name') }} </label>
                                                    <div class="form_group mbttom_30">
                                                        @auth('web')
                                                        <input type="text" name="name" class="login_input"
                                                               value="{{ auth('web')->user()->name }}">
                                                        @else
                                                        <input type="text" name="guest_name" class="login_input"
                                                               placeholder="{{ __('website.enter_your_full_name') }}" required>
                                                        @endauth
                                                        <i class="name_icon absinput_icon"></i>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-lg-6">
                                                    <label for="phone" class="login_label"> {{ __('website.mobile_no') }} </label>
                                                    <div class="form_group mbttom_30">
                                                        @auth('web')
                                                        @php
                                                            $countries = \App\Models\Location::where('type', 'country')->where('active', true)->orderBy('id')->get();
                                                            $firstCountry = $countries->first();
                                                            $selectedCountryId = auth('web')->user()->country_id ?: ($firstCountry ? $firstCountry->id : '');
                                                        @endphp
                                                        <div class="d-flex">
                                                            <select name="country_id" id="country_id" class="form-select" style="width: 120px; border-radius: 8px 0 0 8px; border-right: none;">
                                                                <option value="">{{ __('website.select_country') }}</option>
                                                                @foreach($countries as $country)
                                                                    <option value="{{ $country->id }}" data-phone-code="{{ $country->phone_code }}" {{ $selectedCountryId == $country->id ? 'selected' : '' }}>
                                                                        {{ $country->phone_code }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <input type="tel" name="phone" class="login_input"
                                                                   value="{{ auth('web')->user()->phone ?? '' }}"
                                                                   maxlength="8"
                                                                   style="border-radius: 0 8px 8px 0; flex: 1;">
                                                            <i class="phone_icon absinput_icon" style="right: auto; left: 130px;"></i>
                                                        </div>
                                                        @else
                                                        @php
                                                            $guestCountries = \App\Models\Location::where('type', 'country')->where('active', true)->orderBy('id')->get();
                                                            $firstGuestCountry = $guestCountries->first();
                                                            $selectedGuestCountryId = old('guest_country_id', $firstGuestCountry ? $firstGuestCountry->id : '');
                                                        @endphp
                                                        <div class="d-flex">
                                                            <select name="guest_country_id" id="guest_country_id" class="form-select" style="width: 120px; border-radius: 8px 0 0 8px; border-right: none;">
                                                                <option value="">{{ __('website.select_country') }}</option>
                                                                @foreach($guestCountries as $country)
                                                                    <option value="{{ $country->id }}" data-phone-code="{{ $country->phone_code }}" {{ $selectedGuestCountryId == $country->id ? 'selected' : '' }}>
                                                                        {{ $country->phone_code }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <input type="tel" name="guest_phone" class="login_input"
                                                                   placeholder="99999999"
                                                                   value="{{ old('guest_phone') }}"
                                                                   maxlength="8"
                                                                   style="border-radius: 0 8px 8px 0; flex: 1;"
                                                                   required>
                                                            <i class="phone_icon absinput_icon" style="right: auto; left: 130px;"></i>
                                                        </div>
                                                        @endauth
                                                    </div>
                                                    @error('guest_country_id')
                                                    <small class="text-danger">{{ $message }}</small>
                                                    @enderror
                                                </div>
                                                <div class="col-12 col-lg-6">
                                                    <label for="email" class="login_label"> {{ __('website.email') }}  </label>
                                                    <div class="form_group mbttom_30">
                                                        @auth('web')
                                                        <input type="email" name="email" class="login_input"
                                                               value="{{ auth('web')->user()->email }}">
                                                        @else
                                                        <input type="email" name="guest_email" class="login_input"
                                                               placeholder="{{ __('website.enter_your_email') }}" required>
                                                        @endauth
                                                        <i class="email_icon absinput_icon"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="submit"
                                                    class="main_bttn check_bttn  w-100 hvr-sweep-to-right mrgTop_wide">
                                                {{ __('website.next') }}
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Content -->
    <!--start Bold download section-->
    <section class="download_section">
        <div class="container px-lg-0">
            <div class="row">
                <div class="col-12 col-lg-6 orderMD_two">
                    <div class="collectM_column wow zoomIn" data-wow-offset="100" data-wow-duration="1.5s">
                        <h3>{{ __('website.download_our_app_now') }}</h3>
                        <p>{{ __('website.with_our_app_ordering_is_faster') }}</p>
                        <div class="buttons_wrapper mt_90">
                            <a href="#" class="download_bttn">
                                <img src="{{asset('website')}}/assets/img/g.png" alt="" class="app_thumb">
                            </a>
                            <a href="#" class="download_bttn">
                                <img src="{{asset('website')}}/assets/img/a.png" alt="" class="app_thumb">
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-12 offset-lg-1 col-lg-4 orderMD_one">
                    <div class="Thumbs_wrap wow fadeInDown" data-wow-offset="100" data-wow-duration="1.5s">
                        <img src="{{asset('website')}}/assets/img/mob.png" alt="" class="download_thumb">
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--/ Content -->
@endsection
@section('website-footer')
    <script>
        $(document).ready(function() {
            function toggleScheduleFields() {
                if ($('input[name="order_type"]:checked').val() === 'scheduled') {
                    $('.schedule_fields').slideDown();
                } else {
                    $('.schedule_fields').slideUp();
                }
            }
            toggleScheduleFields();
            $('input[name="order_type"]').on('change', toggleScheduleFields);
        });
    </script>

@endsection
