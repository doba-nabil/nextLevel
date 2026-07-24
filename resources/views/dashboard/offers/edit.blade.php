@extends('dashboard.layout.master')
@section('title', __('admin.edit').' '. $offer->title)

@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <h5 class="card-header"> {{ __('admin.edit').' '. $offer->title }}</h5>
                    <div class="card-body">
                        <form id="offerForm" class="row g-6" method="POST"
                              action="{{ route('offers.update', $offer->id) }}">
                            @csrf
                            @method('PUT')

                            <div class="col-md-12">
                                <label class="form-label">{{ __('admin.title') }}</label>
                                <input value="{{ old('title', $offer->title) }}" type="text" class="form-control" name="title"
                                       placeholder="{{ __('admin.title') }}">
                                @error('title')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">{{ __('admin.discount_type') }}</label>
                                <select class="form-control" name="discount_type" id="discount_type">
                                    <option value="percentage" {{ old('discount_type', $offer->discount_type) == 'percentage' ? 'selected' : '' }}>
                                        {{ __('admin.percent') }}
                                    </option>
                                    <option value="fixed" {{ old('discount_type', $offer->discount_type) == 'fixed' ? 'selected' : '' }}>
                                        {{ __('admin.fixed') }}
                                    </option>
                                </select>
                                @error('discount_type')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">{{ __('admin.discount') }}</label>
                                <input value="{{ old('discount_value', $offer->discount_value) }}" type="number" step="0.01" min="0" class="form-control" name="discount_value"
                                       placeholder="{{ __('admin.discount') }}">
                                @error('discount_value')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">{{ __('admin.start_date') }}</label>
                                <input value="{{ old('start_date', $offer->start_date ? \Carbon\Carbon::parse($offer->start_date)->format('Y-m-d\TH:i') : '') }}" type="datetime-local" class="form-control" name="start_date">
                                @error('start_date')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">{{ __('admin.end_date') }}</label>
                                <input value="{{ old('end_date', $offer->end_date ? \Carbon\Carbon::parse($offer->end_date)->format('Y-m-d\TH:i') : '') }}" type="datetime-local" class="form-control" name="end_date">
                                @error('end_date')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">{{ __('admin.status') }}</label>
                                <select class="form-control" name="is_active">
                                    <option value="1" {{ old('is_active', $offer->is_active) == 1 ? 'selected' : '' }}>
                                        {{ __('admin.active') }}
                                    </option>
                                    <option value="0" {{ old('is_active', $offer->is_active) == 0 ? 'selected' : '' }}>
                                        {{ __('admin.deactive') }}
                                    </option>
                                </select>
                                @error('is_active')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">{{ __('admin.products') }}</label>
                                <select class="form-control select2" name="products[]" multiple style="width:100%;">
                                    @foreach($products as $product)
                                        @php
                                            // Get dashboard locale from session or URL, not website locale
                                            $locale = session('admin_locale', request()->segment(1) ?: config('app.locale'));
                                            if ($locale === 'en' || $locale === 'en_US' || $locale === 'en-GB') {
                                                // English locale - prefer English, fallback to Arabic
                                                $productName = $product->getTranslation('name', 'en', false) 
                                                    ?: $product->getTranslation('name', 'ar', false) 
                                                    ?: '';
                                            } else {
                                                // Arabic locale - prefer Arabic, fallback to English
                                                $productName = $product->getTranslation('name', 'ar', false) 
                                                    ?: $product->getTranslation('name', 'en', false) 
                                                    ?: '';
                                            }
                                        @endphp
                                        <option value="{{ $product->id }}" {{ in_array($product->id, old('products', $offer->products->pluck('id')->toArray())) ? 'selected' : '' }}>
                                            {{ $productName }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('products')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-12 mt-4 text-end">
                                <button type="submit" class="btn btn-primary">{{ __('admin.save') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('dashboard-head')
    @include('dashboard.partials.create.css')
@endsection

@section('dashboard-footer')
    @include('dashboard.partials.create.js')
    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>
@endsection

















