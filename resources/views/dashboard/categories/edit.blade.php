@extends('dashboard.layout.master')
@section('title', __('admin.edit').' '. $category->name)

@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <h5 class="card-header"> {{ __('admin.edit').' '. $category->name }}</h5>
                    <div class="card-body">
                        <form id="formValidationExamples" class="row g-6" method="POST"
                              action="{{ route('categories.update' , $category->id) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <ul class="nav nav-tabs" id="langTabs" role="tablist">
                                @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                                                id="{{$localeCode}}-tab" data-bs-toggle="tab"
                                                data-bs-target="#{{$localeCode}}" type="button"
                                                role="tab">{{ __('admin.'.$properties['name']) }}
                                        </button>
                                    </li>
                                @endforeach
                            </ul>

                            <div class="tab-content mt-3 p-3" id="langTabsContent">
                                @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                                    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                         id="{{$localeCode}}" role="tabpanel">
                                        <div class="row g-3">
                                            <div class="col-md-12">
                                                <label class="form-label">{{ __('admin.name') }}</label>
                                                <input value="{{$category->getTranslation('name', $localeCode)}}" type="text" class="form-control" name="name[{{$localeCode}}]"
                                                       placeholder="{{ __('admin.name') }}">
                                                @error("name.$localeCode")
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('admin.status') }}</label>
                                    <select id="active" class="select2 form-control" name="active"
                                            style="width:100%;">
                                        <option {{ $category->active ? 'selected' : '' }} value="1">{{ __('admin.active') }}</option>
                                        <option {{ !$category->active ? 'selected' : '' }} value="0">{{ __('admin.deactive') }}</option>
                                    </select>
                                    @error('active')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('admin.order') }}</label>
                                    <input type="number" class="form-control" name="order"
                                           value="{{ old('order', $category->order ?? 0) }}" min="0"
                                           placeholder="{{ __('admin.order') }}">
                                    @error('order')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">{{ __('admin.image') }} <span class="text-muted small">({{ __('admin.recommended') }}: 400x400px)</span></label>
                                <div class="dropzone needsclick" id="dropzone-basic">
                                    <div class="dz-message needsclick">
                                        {{__('admin.Drop files here or click to upload')}}
                                    </div>
                                </div>
                                @error("image")
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Products Order Section -->
                            <div class="col-12 mt-4">
                                <div class="card">
                                    <h5 class="card-header">{{ __('admin.products_order') }}</h5>
                                    <div class="card-body">
                                        <p class="text-muted small mb-3">{{ __('admin.products_order_description') }}</p>
                                        <div id="products-order-container">
                                            @php
                                                $products = $category->products()->orderBy('order', 'asc')->get();
                                                $locale = app()->getLocale();
                                            @endphp
                                            @if($products->count() > 0)
                                                <div class="row g-2">
                                                    @foreach($products as $product)
                                                        <div class="col-md-4 mb-2">
                                                            <div class="card p-2">
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <div class="d-flex align-items-center gap-2">
                                                                        <input type="number"
                                                                               class="form-control form-control-sm product-order-input"
                                                                               name="products_order[{{ $product->id }}]"
                                                                               value="{{ old("products_order.{$product->id}", $product->order ?? 0) }}"
                                                                               min="0"
                                                                               style="width: 60px;"
                                                                               placeholder="{{ __('admin.order') }}">
                                                                        <span>{{ $product->getTranslation('name', $locale) }}</span>
                                                                    </div>
                                                                    <div>
                                                                        @if($product->active)
                                                                            <span class="badge bg-label-success">{{ __('admin.active') }}</span>
                                                                        @else
                                                                            <span class="badge bg-label-secondary">{{ __('admin.deactive') }}</span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <p class="text-muted text-center py-3">{{ __('admin.no_products_in_category') }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
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
    @include('dashboard.partials.edit.js')
    @php
        $messages = [
            'name_required' => __('admin.name_required'),
            'required' => __('admin.required'),
            'name_length' => __('admin.name_length'),
            'email_required' => __('admin.email_required'),
            'email_valid' => __('admin.email_valid'),
            'password_required' => __('admin.password_required'),
            'password_length' => __('admin.password_length'),
            'password_confirm' => __('admin.password_confirm'),
        ];
    @endphp
    <script>
        'use strict';

        document.addEventListener('DOMContentLoaded', function () {
            const categoryForm = document.getElementById('formValidationExamples');

            if (categoryForm) {
                const messages = @json($messages);

                const fv = FormValidation.formValidation(categoryForm, {
                    fields: {
                        "name[ar]": {
                            validators: {
                                notEmpty: {message: messages.required},
                                stringLength: {
                                    min: 3,
                                    max: 50,
                                    message: messages.name_length
                                }
                            }
                        },
                        "description[ar]": {
                            validators: {
                                notEmpty: {message: messages.required},
                            }
                        },
                        "name[en]": {
                            validators: {
                                notEmpty: {message: messages.required},
                                stringLength: {
                                    min: 3,
                                    max: 50,
                                    message: messages.name_length
                                }
                            }
                        },
                        "description[en]": {
                            validators: {
                                notEmpty: {message: messages.required},
                            }
                        },
                        "image": {
                            validators: {
                                notEmpty: {message: messages.required},
                                file: {
                                    extension: 'jpg,jpeg,png',
                                    type: 'image/jpeg,image/jpg,image/png',
                                }
                            }
                        }
                    },
                    plugins: {
                        trigger: new FormValidation.plugins.Trigger(),
                        bootstrap5: new FormValidation.plugins.Bootstrap5(),
                        submitButton: new FormValidation.plugins.SubmitButton(),
                        autoFocus: new FormValidation.plugins.AutoFocus(),
                        defaultSubmit: new FormValidation.plugins.DefaultSubmit()
                    }
                });

                fv.on('core.form.invalid', function () {
                    const firstInvalidField = categoryForm.querySelector('.is-invalid');

                    if (firstInvalidField) {
                        const tabPane = firstInvalidField.closest('.tab-pane');
                        if (tabPane) {
                            const tabId = tabPane.getAttribute('id');
                            const tabTrigger = document.querySelector(`[data-bs-target="#${tabId}"]`);

                            if (tabTrigger) {
                                const tabName = tabTrigger.innerText.trim();

                                Swal.fire({
                                    icon: 'error',
                                    title: '{{ __('admin.validation_error') }}',
                                    text: ` "${tabName}"{{ __('admin.validation_error_lang') }} `,
                                    confirmButtonText: '{{ __('admin.ok') }}'
                                });
                            }
                        }
                    }
                });
            }
        });
    </script>
    @include('dashboard.partials.image-cropper-js')
    <script>
        'use strict';
        document.addEventListener('DOMContentLoaded', function () {
            @if(isset($category) && $category->getFirstMediaUrl('categories'))
            initImageCropper('#dropzone-basic', 'image', null, "{{ $category->getFirstMediaUrl('categories') }}");
            @else
            initImageCropper('#dropzone-basic', 'image');
            @endif
        });
    </script>
@endsection
