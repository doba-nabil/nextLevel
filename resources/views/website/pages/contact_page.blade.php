@extends('website.layout.master')
@section('title', __('website.contact_us'))
@section('website-main')
    <!-- Content -->
    <section class="sign-up-page contact-page">
        <div class="container">
            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    <div class="map">
                        <iframe style="border:0;" allowfullscreen="" loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                                src="https://maps.google.com/maps?q={{ config('settings.location.lat') }},{{ config('settings.location.long') }}&hl=es;z=14&amp;output=embed"></iframe>
                    </div>
                    <h4 class="form-title text-center">{{ __('website.contact_us') }}</h4>
                    <div class="sign-up-form">
                        <form method="post" action="{{ url('contact-us') }}">
                            @csrf
                            <div class="form-group col-md-6 col-sm-6">
                                <x-input-label for="first_name" :value="__('website.first_name')"/>
                                <x-text-input id="first_name" class="block mt-1 w-full form-control" type="text"
                                              name="first_name" :value="old('first_name')" required autofocus
                                              autocomplete="first_name"/>
                                <x-input-error :messages="$errors->get('first_name')" class="mt-2"/>
                            </div>
                            <div class="form-group col-md-6 col-sm-6">
                                <x-input-label for="last_name" :value="__('website.last_name')"/>
                                <x-text-input id="last_name" class="block mt-1 w-full form-control" type="text"
                                              name="last_name" :value="old('last_name')" required autofocus
                                              autocomplete="last_name"/>
                                <x-input-error :messages="$errors->get('last_name')" class="mt-2"/>
                            </div>
                            <div class="form-group col-md-6 col-sm-6">
                                <x-input-label for="email" :value="__('website.email')"/>
                                <x-text-input id="email" class="block mt-1 w-full form-control" type="email"
                                              name="email" :value="old('email')" required autocomplete="email"/>
                                <x-input-error :messages="$errors->get('email')" class="mt-2"/>
                            </div>
                            <div class="form-group col-md-6 col-sm-6">
                                <x-input-label for="phone" :value="__('website.mobile_no')"/>
                                <x-text-input id="phone" class="block mt-1 w-full form-control" type="text" name="phone"
                                              :value="old('phone')" required autofocus autocomplete="phone"/>
                                <x-input-error :messages="$errors->get('phone')" class="mt-2"/>
                            </div>
                            <div class="form-group col-md-12 col-sm-12">
                                <x-input-label for="message" :value="__('website.message')"/>
                                <textarea id="message" name="message" class="form-control" rows="6">{{ old('message') }}</textarea>
                                <x-input-error :messages="$errors->get('message')" class="mt-2"/>
                            </div>
                            <div class="submit col-md-12 col-sm-12">
                                <button type="submit" class="">{{ __('website.send') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--/ Content -->
@endsection
