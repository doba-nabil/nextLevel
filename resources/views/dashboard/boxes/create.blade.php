@extends('dashboard.layout.master')
@section('title', __('admin.create') . '.'. __('admin.meals_boxes'))

@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <h5 class="card-header">{{ __('admin.create') . ' '. __('admin.meals_boxes') }}</h5>
                    <div class="card-body">
                        <form id="productForm" class="row g-6" method="POST" action="{{ route('boxes.store') }}"
                              enctype="multipart/form-data"
                              onsubmit="return prepareAddonsForm(this);">
                            @csrf
                            <input value="box" name="product_type" hidden/>
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
                                                <input value="{{ old("name.$localeCode") }}" type="text"
                                                       class="form-control"
                                                       name="name[{{$localeCode}}]"
                                                       placeholder="{{ __('admin.name') }}">
                                                @error("name.$localeCode")
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label">{{ __('admin.description') }}</label>
                                                <textarea rows="10" class="form-control"
                                                          name="description[{{$localeCode}}]"
                                                          placeholder="{{ __('admin.description') }}">{{ old("description.$localeCode") }}</textarea>
                                                @error("description.$localeCode")
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label">{{ __('admin.ingrediant_text') }}</label>
                                                <textarea class="form-control editor"
                                                          name="ingrediant_text[{{$localeCode}}]">{{ old("ingrediant_text.$localeCode", isset($product) ? $product->getTranslation('ingrediant_text',$localeCode) : '') }}</textarea>
                                                @error("ingrediant_text.$localeCode")
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="col-md-6">
                                <label for="category_id" class="form-label">{{ __('admin.category') }}</label>
                                <select id="category_id" class="select2 form-select" name="category_id">
                                    <option selected disabled value="">{{ __('admin.select_category') }}</option>
                                    @foreach($categories as $category)
                                        <option
                                            value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->getTranslation('name', app()->getLocale()) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">{{ __('admin.type') }}</label>
                                <select class="form-select" name="type" id="productType">
                                    <option
                                        value="pickup" {{ old('type') == 'pickup' ? 'selected' : '' }}>{{ __('admin.pickup') }}
                                    </option>
                                    <option value="delivery" {{ old('type') == 'delivery' ? 'selected' : '' }}>
                                        {{ __('admin.delivery') }}
                                    </option>
                                    <option
                                        value="both" {{ old('type') == 'both' ? 'selected' : '' }}>{{ __('admin.both') }}</option>
                                </select>
                                @error('type') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-12 mb-3" id="branchesField">
                                <label for="select2Multiple" class="form-label">{{ __('admin.branches') }}</label>
                                <select id="select2Multiple" class="select2 form-select" name="branches[]" multiple>
                                    @foreach($branches as $branch)
                                        <option
                                            value="{{ $branch->id }}" {{ in_array($branch->id, old('branches', [])) ? 'selected' : '' }}>
                                            {{ $branch->getTranslation('name', app()->getLocale()) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <hr>


                            <h6 class="text-primary mb-1 mt-2">{{ __('admin.prices') }}</h6>
                            <div class="row">
                                @foreach($currencies as $currency)
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-4 mt-3">
                                                <label class="form-label">
                                                    {{ $currency->name }} ({{ __('admin.offer_type') }})
                                                </label>
                                                <select class="form-control discount-type"
                                                        data-currency="{{ $currency->id }}"
                                                        name="prices[{{ $currency->id }}][discount_type]">
                                                    <option
                                                        value="none" {{ old("prices.$currency->id.discount_type") == 'none' ? 'selected' : '' }}>
                                                        {{ __('admin.no_discount') }}
                                                    </option>
                                                    <option
                                                        value="fixed" {{ old("prices.$currency->id.discount_type") == 'fixed' ? 'selected' : '' }}>
                                                        {{ __('admin.fixed') }}
                                                    </option>
                                                    <option
                                                        value="percentage" {{ old("prices.$currency->id.discount_type") == 'percentage' ? 'selected' : '' }}>
                                                        {{ __('admin.percent') }}
                                                    </option>
                                                </select>
                                                @error("prices.$currency->id.discount_type")
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="col-md-4 mt-3">
                                                <label class="form-label">
                                                    {{ __('admin.price') }}
                                                    <small
                                                        class="text-danger discount-before discount-before-{{ $currency->id }}">
                                                        {{ __('admin.before_discount') }}
                                                    </small>
                                                </label>
                                                <div class="input-group">
                                                    <input type="number" step="0.01" min="0" class="form-control"
                                                           name="prices[{{ $currency->id }}][before]"
                                                           value="{{ old("prices.$currency->id.before") }}">
                                                    <span class="input-group-text">{{ $currency->sign }}</span>
                                                </div>
                                                @error("prices.$currency->id.before")
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="col-md-4 mt-3 discount-field discount-fixed-{{ $currency->id }}"
                                                 style="display:none;">
                                                <label class="form-label">
                                                    {{ __('admin.price') }}
                                                    <small
                                                        class="text-success after-discount">{{ __('admin.after_discount') }}</small>
                                                </label>
                                                <div class="input-group">
                                                    <input type="number" step="0.01" min="0" class="form-control"
                                                           name="prices[{{ $currency->id }}][after]"
                                                           value="{{ old("prices.$currency->id.after") }}">
                                                    <span class="input-group-text">{{ $currency->sign }}</span>
                                                </div>
                                                @error("prices.$currency->id.after")
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div
                                                class="col-md-4 mt-3 discount-field discount-percentage-{{ $currency->id }}"
                                                style="display:none;">
                                                <label class="form-label">{{ __('admin.discount_percentage') }}</label>
                                                <div class="input-group">
                                                    <input type="number" step="1" min="0" max="100"
                                                           class="form-control"
                                                           name="prices[{{ $currency->id }}][discount_percentage]"
                                                           value="{{ old("prices.$currency->id.discount_percentage") }}">
                                                    <span class="input-group-text">%</span>
                                                </div>
                                                @error("prices.$currency->id.discount_percentage")
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @if(count($addon_groups) > 0)
                                <hr>
                                <h6 class="text-primary m-0 mb-3">{{ __('admin.additionals') }}</h6>
                                <div class="col-md-12">
                                    @php
                                        // Prepare groups data for JavaScript
                                        $groupsData = $addon_groups->map(function($group) {
                                            return [
                                                'id' => $group->id,
                                                'name' => $group->getTranslation('name', app()->getLocale()),
                                                'addons' => $group->addons->map(function($addon) {
                                                    return [
                                                        'id' => $addon->id,
                                                        'name' => $addon->getTranslation('name', app()->getLocale()),
                                                        'group_id' => $addon->addon_group_id
                                                    ];
                                                })->values()
                                            ];
                                        })->values();
                                        
                                        $groupBlocks = old('group_blocks', []);
                                    @endphp
                                    
                                    <script>
                                        window.addonGroups = @json($groupsData);
                                    </script>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('admin.select_addon_group') }}</label>
                                        <div class="d-flex gap-2">
                                            <select class="form-select" id="addon-group-selector" style="max-width: 300px;">
                                                <option value="">{{ __('admin.select_group') }}</option>
                                                @foreach($addon_groups as $group)
                                                    <option value="{{ $group->id }}" data-group-name="{{ $group->getTranslation('name', app()->getLocale()) }}">
                                                        {{ $group->getTranslation('name', app()->getLocale()) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button type="button" class="btn btn-primary" onclick="addGroupBlock()">
                                                <i class="icon-base ti tabler-plus"></i> {{ __('admin.add_group') }}
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div id="addon-group-blocks-container" class="sortable-group-blocks">
                                        @if(!empty($groupBlocks))
                                            @foreach($groupBlocks as $blockIndex => $block)
                                                @php
                                                    $groupId = $block['group_id'] ?? null;
                                                    $group = $addon_groups->firstWhere('id', $groupId);
                                                    $blockAddons = $block['addons'] ?? [];
                                                @endphp
                                                @if($group)
                                                    <div class="card mb-3 group-block" data-group-id="{{ $groupId }}" data-block-index="{{ $blockIndex }}">
                                                        <input type="hidden" name="group_blocks[{{ $blockIndex }}][group_id]" value="{{ $groupId }}">
                                                        <input type="hidden" name="group_blocks[{{ $blockIndex }}][order]" class="group-block-order" value="{{ $block['order'] ?? $blockIndex }}">
                                                        <div class="card-header d-flex justify-content-between align-items-center">
                                                            <div class="d-flex align-items-center gap-2">
                                                                <i class="icon-base ti tabler-grip-vertical drag-handle" style="cursor: move; font-size: 20px;"></i>
                                                                <strong>{{ $group->getTranslation('name', app()->getLocale()) }}</strong>
                                                            </div>
                                                            <button type="button" class="btn btn-sm btn-danger" onclick="removeGroupBlock(this)">
                                                                <i class="icon-base ti tabler-trash"></i> {{ __('admin.delete') }}
                                                            </button>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="addons-list sortable-addons-in-group-block" data-group-id="{{ $groupId }}" data-block-index="{{ $blockIndex }}">
                                                                @foreach($group->addons as $addon)
                                                                    @php
                                                                        $isInBlock = isset($blockAddons[$addon->id]);
                                                                        $addonData = $isInBlock ? $blockAddons[$addon->id] : null;
                                                                    @endphp
                                                                    <div class="row mb-2 align-items-center addon-item-in-group-block" data-addon-id="{{ $addon->id }}" data-addon-group-id="{{ $addon->addon_group_id }}">
                                                                        <div class="col-md-1">
                                                                            <i class="icon-base ti tabler-grip-vertical drag-handle" style="cursor: move;"></i>
                                                                        </div>
                                                                        <div class="col-md-1">
                                                                            <input type="checkbox" 
                                                                                   class="form-check-input addon-checkbox-in-group" 
                                                                                   name="group_blocks[{{ $blockIndex }}][addons][{{ $addon->id }}][id]" 
                                                                                   value="{{ $addon->id }}"
                                                                                   {{ $isInBlock ? 'checked' : '' }}
                                                                                   onchange="toggleAddonInGroup(this, {{ $blockIndex }}, {{ $addon->id }})">
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <label class="form-label mb-0">
                                                                                <strong>{{ $addon->getTranslation('name', app()->getLocale()) }}</strong>
                                                                            </label>
                                                                        </div>
                                                                        <div class="col-md-3" style="display: none;">
                                                                            <select name="group_blocks[{{ $blockIndex }}][addons][{{ $addon->id }}][type]" class="form-select addon-type-select-in-group" {{ $isInBlock ? '' : 'disabled' }}>
                                                                                <option value="optional" {{ ($addonData['type'] ?? 'optional') == 'optional' ? 'selected' : '' }}>{{ __('admin.optional') }}</option>
                                                                                <option value="mandatory" {{ ($addonData['type'] ?? '') == 'mandatory' ? 'selected' : '' }}>{{ __('admin.mandatory') }}</option>
                                                                            </select>
                                                                        </div>
                                                                        <input type="hidden" name="group_blocks[{{ $blockIndex }}][addons][{{ $addon->id }}][type]" value="{{ $addonData['type'] ?? 'optional' }}">
                                                                        <div class="col-md-2">
                                                                            <input type="number" 
                                                                                   name="group_blocks[{{ $blockIndex }}][addons][{{ $addon->id }}][order]" 
                                                                                   class="form-control addon-order-input" 
                                                                                   value="{{ $addonData['order'] ?? 0 }}" 
                                                                                   min="0" 
                                                                                   placeholder="{{ __('admin.order') }}"
                                                                                   {{ $isInBlock ? '' : 'disabled' }}>
                                                                        </div>
                                                                        <div class="col-md-1">
                                                                            <button type="button" class="btn btn-sm btn-danger w-100" onclick="removeAddonFromGroupBlock(this)" {{ $isInBlock ? '' : 'disabled' }}>
                                                                                <i class="icon-base ti tabler-x"></i>
                                                                            </button>
                                                                        </div>
                                                                        <input type="hidden" name="group_blocks[{{ $blockIndex }}][addons][{{ $addon->id }}][addon_group_id]" value="{{ $addon->addon_group_id }}">
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            @endif

                            @if(count($definitions) > 0)
                                <hr class="m-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="text-primary m-0">
                                        {{ __('admin.nutrition') }}
                                    </h6>
                                    <button type="button" id="addDefinition"
                                            class="btn btn-secondary mt-2">{{ __('admin.add_new') }}
                                    </button>
                                </div>

                                <div class="col-md-12">
                                    <div id="definitions-wrapper">
                                        @foreach(old('definitions', []) as $index => $definition)
                                            <div class="row g-2 mb-2">
                                                <div class="col-md-5">
                                                    <select name="definitions[{{$index}}][id]"
                                                            class="form-select definition-select">
                                                        <option value="">{{ __('admin.please_select') }}</option>
                                                        @foreach($definitions as $def)
                                                            <option
                                                                value="{{ $def->id }}" {{ ($definition['id'] ?? '') == $def->id ? 'selected' : '' }}>{{ $def->name .' - '. $def->unit }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-5">
                                                    <input step="0.1" min="0" max="100000"
                                                           name="definitions[{{$index}}][value]"
                                                           class="form-control"
                                                           value="{{ $definition['value'] ?? '' }}"
                                                           placeholder="Definition value">
                                                </div>
                                                <div class="col-md-2">
                                                    <button type="button" class="btn btn-danger remove-definition">X
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            @if(count($products) > 0)
                                <hr class="m-0">
                                <div class="col-md-12 mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="text-primary m-0">{{ __('admin.box_title') }}</h6>
                                        <button type="button" id="addTitleBtn" class="btn btn-primary btn-sm">{{ __('admin.add_title') }}</button>
                                    </div>
                                    <div id="titles-container" class="sortable-titles-container">
                                        <!-- Titles will be added here dynamically -->
                                    </div>
                                </div>
                            @endif
                            <hr class="mb-0">
                            <div class="col-12">
                                <label class="form-label">{{ __('admin.image') }} <span class="text-muted small">({{ __('admin.recommended') }}: 800x800px)</span></label>
                                <div class="dropzone needsclick" id="dropzone-basic">
                                    <div class="dz-message needsclick">
                                        {{ __('admin.Drop files here or click to upload') }}
                                    </div>
                                </div>
                                @error("image")
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('admin.status') }}</label>
                                    <select id="active" class="select2 form-control" name="active"
                                            style="width:100%;">
                                        <option value="1" {{ old('active', '1') == '1' ? 'selected' : '' }}>{{ __('admin.active') }}</option>
                                        <option value="0" {{ old('active') == '0' ? 'selected' : '' }}>{{ __('admin.deactive') }}</option>
                                    </select>
                                    @error('active')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('admin.show_in_home') }}</label>
                                    <select id="is_home" class="select2 form-control" name="is_home"
                                            style="width:100%;">
                                        <option value="0" {{ old('is_home') == '0' || old('is_home') === null ? 'selected' : '' }}>{{ __('admin.no') }}</option>
                                        <option value="1" {{ old('is_home') == '1' ? 'selected' : '' }}>{{ __('admin.yes') }}</option>
                                    </select>
                                    @error('is_home')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('admin.order') }}</label>
                                    <input type="number" class="form-control" name="order" 
                                           value="{{ old('order', 0) }}" min="0" 
                                           placeholder="{{ __('admin.order') }}">
                                    @error('order')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <br>

                            @if(count($currencies) > 0)
                                <div class="col-12 mt-4 text-end">
                                    <button type="submit" class="btn btn-primary">{{ __('admin.save') }}</button>
                                </div>
                            @else
                                <h6 class="alert alert-warning">{{ __('admin.please_add_currency') }}</h6>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('dashboard-head')
    @include('dashboard.partials.create.css')
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <style>
        .discount-before {
            display: none;
        }

        .after-discount, .discount-before {
            font-size: 11px;
        }
    </style>
@endsection

@section('dashboard-footer')
    @include('dashboard.partials.create.js')
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
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.editor').summernote({
                height: 150,
                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['font', ['strikethrough', 'superscript', 'subscript']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        });
    </script>
    <script>
        'use strict';

        document.addEventListener('DOMContentLoaded', function () {
            const categoryForm = document.getElementById('productForm');

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
                                    message: 'Name must be between 3 and 50 characters'
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
                                    message: 'يجب أن تكون الصورة بصيغة JPG أو PNG'
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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const productType = document.getElementById('productType');
            const branchesField = document.getElementById('branchesField');
            const toggleBranches = () => {
                // Show branches for delivery, pickup, or both
                branchesField.style.display = 'block';
            };
            toggleBranches();
            productType.addEventListener('change', toggleBranches);
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const wrapper = document.getElementById('definitions-wrapper');
            const addBtn = document.getElementById('addDefinition');
            const maxDefinitions = @json(count($definitions));

            function updateAddButton() {
                addBtn.disabled = wrapper.children.length >= maxDefinitions;
            }

            addBtn.addEventListener('click', function () {
                if (wrapper.children.length >= maxDefinitions) return;

                const index = wrapper.children.length;
                const div = document.createElement('div');
                div.classList.add('row', 'g-2', 'mb-2');

                div.innerHTML = `
        <div class="col-md-5">
            <select name="definitions[${index}][id]" class="form-select definition-select">
                <option value=" "></option>
                @foreach($definitions as $definition)
                <option value="{{ $definition->id }}">{{ $definition->name .' - '. $definition->unit }}</option>
                @endforeach
                </select>
            </div>
            <div class="col-md-5">
                <input type="number" step="0.1" min="0" max="100000" name="definitions[${index}][value]" class="form-control" placeholder="Definition value">
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-danger remove-definition">X</button>
        </div>
        `;
                wrapper.appendChild(div);
                updateSelectOptions();
                updateAddButton();
            });
            wrapper.addEventListener('click', function (e) {
                if (e.target.classList.contains('remove-definition')) {
                    e.target.closest('.row').remove();
                    updateSelectOptions();
                    updateAddButton();
                }
            });
            updateAddButton();
        });
    </script>
    <style>
        .group-block {
            cursor: move;
        }
        .group-block .card-header {
            background-color: #f8f9fa;
        }
        .addon-item-in-group-block {
            transition: background-color 0.2s;
        }
        .addon-item-in-group-block:hover {
            background-color: #f8f9fa;
        }
        .sortable-group-blocks .sortable-ghost {
            opacity: 0.4;
            background-color: #e9ecef;
        }
        .sortable-group-blocks .sortable-chosen {
            background-color: #fff3cd;
        }
        .drag-handle {
            cursor: grab;
        }
        .drag-handle:active {
            cursor: grabbing;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script>
        let groupBlockCounter = {{ !empty($groupBlocks) ? count($groupBlocks) : 0 }};
        const optionalText = '{{ __('admin.optional') }}';
        const mandatoryText = '{{ __('admin.mandatory') }}';
        const orderText = '{{ __('admin.order') }}';
        const deleteText = '{{ __('admin.delete') }}';

        function addGroupBlock() {
            const groupSelector = document.getElementById('addon-group-selector');
            const selectedGroupId = groupSelector.value;
            
            if (!selectedGroupId) {
                alert('{{ __('admin.please_select_group') }}');
                return;
            }
            
            // Check if this group already exists
            const existingBlocks = document.querySelectorAll(`.group-block[data-group-id="${selectedGroupId}"]`);
            if (existingBlocks.length > 0) {
                alert('{{ __('admin.group_already_exists') }}');
                return;
            }
            
            // Find the selected group data
            const selectedGroup = window.addonGroups.find(g => g.id == selectedGroupId);
            if (!selectedGroup) return;
            
            const container = document.getElementById('addon-group-blocks-container');
            const blockIndex = groupBlockCounter++;
            
            // Create HTML for all addons in this group (all pre-selected)
            const addonsHtml = selectedGroup.addons.map(function(addon, index) {
                return `
                    <div class="row mb-2 align-items-center addon-item-in-group-block" data-addon-id="${addon.id}" data-addon-group-id="${addon.group_id}">
                        <div class="col-md-1">
                            <i class="icon-base ti tabler-grip-vertical drag-handle" style="cursor: move;"></i>
                        </div>
                        <div class="col-md-1">
                            <input type="checkbox" 
                                   class="form-check-input addon-checkbox-in-group" 
                                   name="group_blocks[${blockIndex}][addons][${addon.id}][id]" 
                                   value="${addon.id}"
                                   checked
                                   onchange="toggleAddonInGroup(this, ${blockIndex}, ${addon.id})">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label mb-0">
                                <strong>${addon.name}</strong>
                            </label>
                        </div>
                        <div class="col-md-3" style="display: none;">
                            <select name="group_blocks[${blockIndex}][addons][${addon.id}][type]" class="form-select addon-type-select-in-group">
                                <option value="optional" selected>${optionalText}</option>
                                <option value="mandatory">${mandatoryText}</option>
                            </select>
                        </div>
                        <input type="hidden" name="group_blocks[${blockIndex}][addons][${addon.id}][type]" value="optional">
                        <div class="col-md-2">
                            <input type="number" 
                                   name="group_blocks[${blockIndex}][addons][${addon.id}][order]" 
                                   class="form-control addon-order-input" 
                                   value="${index}" 
                                   min="0" 
                                   placeholder="${orderText}">
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-sm btn-danger w-100" onclick="removeAddonFromGroupBlock(this)">
                                <i class="icon-base ti tabler-x"></i>
                            </button>
                        </div>
                        <input type="hidden" name="group_blocks[${blockIndex}][addons][${addon.id}][addon_group_id]" value="${addon.group_id}">
                    </div>
                `;
            }).join('');
            
            const blockHtml = `
                <div class="card mb-3 group-block" data-group-id="${selectedGroupId}" data-block-index="${blockIndex}">
                    <input type="hidden" name="group_blocks[${blockIndex}][group_id]" value="${selectedGroupId}">
                    <input type="hidden" name="group_blocks[${blockIndex}][order]" class="group-block-order" value="${blockIndex}">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <i class="icon-base ti tabler-grip-vertical drag-handle" style="cursor: move; font-size: 20px;"></i>
                            <strong>${selectedGroup.name}</strong>
                        </div>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeGroupBlock(this)">
                            <i class="icon-base ti tabler-trash"></i> ${deleteText}
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="addons-list sortable-addons-in-group-block" data-group-id="${selectedGroupId}" data-block-index="${blockIndex}">
                            ${addonsHtml}
                        </div>
                    </div>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', blockHtml);
            groupSelector.value = '';
            
            // Initialize sortable for addons in this block
            initSortableForAddonsInGroupBlock(blockIndex);
            
            // Reinitialize group blocks sortable
            initGroupBlocksSortable();
        }

        function removeGroupBlock(button) {
            if (confirm('{{ __('admin.sure') }}')) {
                const block = button.closest('.group-block');
                block.remove();
                updateGroupBlockOrders();
                initGroupBlocksSortable();
            }
        }

        function toggleAddonInGroup(checkbox, blockIndex, addonId) {
            const addonItem = checkbox.closest('.addon-item-in-group-block');
            const typeSelect = addonItem.querySelector('.addon-type-select-in-group');
            const orderInput = addonItem.querySelector('.addon-order-input');
            const removeBtn = addonItem.querySelector('button[onclick*="removeAddonFromGroupBlock"]');
            
            if (checkbox.checked) {
                typeSelect.disabled = false;
                orderInput.disabled = false;
                if (removeBtn) removeBtn.disabled = false;
            } else {
                typeSelect.disabled = true;
                orderInput.disabled = true;
                if (removeBtn) removeBtn.disabled = true;
            }
        }

        function removeAddonFromGroupBlock(button) {
            const addonItem = button.closest('.addon-item-in-group-block');
            const checkbox = addonItem.querySelector('.addon-checkbox-in-group');
            if (checkbox) {
                checkbox.checked = false;
                toggleAddonInGroup(checkbox, 
                    addonItem.closest('.group-block').getAttribute('data-block-index'),
                    addonItem.getAttribute('data-addon-id')
                );
            }
            updateAddonOrdersInGroupBlock(button.closest('.sortable-addons-in-group-block'));
        }

        function updateGroupBlockOrders() {
            const blocks = document.querySelectorAll('.group-block');
            blocks.forEach(function(block, index) {
                const orderInput = block.querySelector('.group-block-order');
                if (orderInput) {
                    orderInput.value = index;
                }
                // Update all input names with new index
                const blockIndex = block.getAttribute('data-block-index');
                const newIndex = index;
                block.setAttribute('data-block-index', newIndex);
                
                // Update all inputs inside this block
                block.querySelectorAll('input, select').forEach(function(input) {
                    if (input.name) {
                        input.name = input.name.replace(`group_blocks[${blockIndex}]`, `group_blocks[${newIndex}]`);
                    }
                });
                
                // Update onclick handlers
                block.querySelectorAll('[onchange*="toggleAddonInGroup"]').forEach(function(checkbox) {
                    const addonId = checkbox.value;
                    checkbox.setAttribute('onchange', `toggleAddonInGroup(this, ${newIndex}, ${addonId})`);
                });
            });
        }

        function updateAddonOrdersInGroupBlock(container) {
            const addonItems = container.querySelectorAll('.addon-item-in-group-block .addon-checkbox-in-group:checked');
            addonItems.forEach(function(checkbox, index) {
                const addonItem = checkbox.closest('.addon-item-in-group-block');
                const orderInput = addonItem.querySelector('.addon-order-input');
                if (orderInput) {
                    orderInput.value = index;
                }
            });
        }

        function initSortableForAddonsInGroupBlock(blockIndex) {
            const container = document.querySelector(`.sortable-addons-in-group-block[data-block-index="${blockIndex}"]`);
            if (!container || container.sortableInstance) return;
            
            if (typeof Sortable !== 'undefined') {
                container.sortableInstance = new Sortable(container, {
                    handle: '.drag-handle',
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    filter: '.addon-checkbox-in-group:not(:checked)',
                    onEnd: function() {
                        updateAddonOrdersInGroupBlock(container);
                    }
                });
            }
        }

        function initGroupBlocksSortable() {
            const container = document.getElementById('addon-group-blocks-container');
            if (!container) return;
            
            if (container.groupBlocksSortable) {
                container.groupBlocksSortable.destroy();
            }
            
            if (typeof Sortable !== 'undefined') {
                container.groupBlocksSortable = new Sortable(container, {
                    handle: '.group-block .drag-handle',
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    onEnd: function() {
                        updateGroupBlockOrders();
                        // Reinitialize addon sortables in all blocks
                        document.querySelectorAll('.sortable-addons-in-group-block').forEach(function(addonContainer) {
                            const blockIndex = addonContainer.getAttribute('data-block-index');
                            if (addonContainer.sortableInstance) {
                                addonContainer.sortableInstance.destroy();
                                addonContainer.sortableInstance = null;
                            }
                            initSortableForAddonsInGroupBlock(blockIndex);
                        });
                    }
                });
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            initGroupBlocksSortable();
            
            // Initialize sortable for existing addon blocks
            document.querySelectorAll('.sortable-addons-in-group-block').forEach(function(container) {
                const blockIndex = container.getAttribute('data-block-index');
                initSortableForAddonsInGroupBlock(blockIndex);
            });
            
            // Initialize disabled state for unchecked addons
            document.querySelectorAll('.addon-checkbox-in-group').forEach(function(checkbox) {
                if (!checkbox.checked) {
                    toggleAddonInGroup(checkbox, 
                        checkbox.closest('.group-block').getAttribute('data-block-index'),
                        checkbox.value
                    );
                }
            });
        });

        function prepareAddonsForm(form) {
            // Ensure all checked addons have their name attributes set
            form.querySelectorAll('.addon-checkbox-in-group:checked').forEach(function(checkbox) {
                if (!checkbox.hasAttribute('name') || !checkbox.getAttribute('name')) {
                    const blockIndex = checkbox.closest('.group-block').getAttribute('data-block-index');
                    const addonId = checkbox.value;
                    checkbox.setAttribute('name', `group_blocks[${blockIndex}][addons][${addonId}][id]`);
                }
            });
            
            // Enable all disabled inputs so they get submitted
            const disabledInputs = form.querySelectorAll('input[disabled], select[disabled]');
            disabledInputs.forEach(function(input) {
                // Don't enable inputs for unchecked addons
                const addonItem = input.closest('.addon-item-in-group-block');
                if (addonItem) {
                    const checkbox = addonItem.querySelector('.addon-checkbox-in-group');
                    if (checkbox && !checkbox.checked) {
                        return; // Skip disabled inputs for unchecked addons
                    }
                }
                input.disabled = false;
                input.style.display = 'none';
            });
            
            // Update all group block orders before submission
            form.querySelectorAll('.group-block').forEach(function(block, index) {
                const orderInput = block.querySelector('.group-block-order');
                if (orderInput) {
                    orderInput.value = index;
                }
            });
            
            // Update all addon orders within each group block
            form.querySelectorAll('.sortable-addons-in-group-block').forEach(function(container) {
                updateAddonOrdersInGroupBlock(container);
            });
            
            return true;
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const titlesContainer = document.getElementById('titles-container');
            const addTitleBtn = document.getElementById('addTitleBtn');
            let titleIndex = 0;
            const allProducts = @json($products);
            const allAddons = @json($all_addons);
            const oldBoxTitles = @json(old('box_titles', []));

            // Restore old box_titles data on page load
            if (oldBoxTitles && oldBoxTitles.length > 0) {
                oldBoxTitles.forEach(function(titleData) {
                    addTitle(titleData);
                });
            }

            // Add title button handler
            addTitleBtn.addEventListener('click', function() {
                addTitle();
            });

            function addTitle(titleData = null) {
                const index = titleIndex++;
                const title = titleData ? titleData.title : {ar: '', en: ''};
                const products = titleData ? titleData.products : [];
                const isRequired = titleData ? titleData.is_required : false;
                const maxCount = titleData ? titleData.max_count : 1;
                const minCount = titleData ? titleData.min_count : 0;

                let productOptions = '<option value="">{{ __('admin.please_select') }}</option>';
                // Get dashboard locale from URL segment (first segment in admin routes is locale)
                const locale = '{{ request()->segment(1) ?: (session('admin_locale') ?: config('app.locale')) }}';
                // Convert products array to numbers for proper comparison
                const productIds = products.map(id => parseInt(id));
                allProducts.forEach(p => {
                    const productId = parseInt(p.id);
                    const selected = productIds.includes(productId) ? 'selected' : '';
                    
                    // Get product name with proper locale handling
                    let productName = '';
                    if (p.name && typeof p.name === 'object' && p.name !== null) {
                        // Check if we have the name in the current locale
                        if (locale === 'en' || locale === 'en_US' || locale === 'en-GB') {
                            // English locale - use English name if available and not empty
                            if (p.name['en'] && String(p.name['en']).trim() !== '') {
                                productName = String(p.name['en']).trim();
                            } else if (p.name['ar'] && String(p.name['ar']).trim() !== '') {
                                // Fallback to Arabic if English is empty
                                productName = String(p.name['ar']).trim();
                            }
                        } else {
                            // Arabic or other locale - use Arabic name if available and not empty
                            if (p.name['ar'] && String(p.name['ar']).trim() !== '') {
                                productName = String(p.name['ar']).trim();
                            } else if (p.name['en'] && String(p.name['en']).trim() !== '') {
                                // Fallback to English if Arabic is empty
                                productName = String(p.name['en']).trim();
                            }
                        }
                    } else if (p.name) {
                        // If name is a string, use it directly
                        productName = String(p.name);
                    }
                    
                    // Only add option if we have a name
                    if (productName) {
                        productOptions += `<option value="${p.id}" ${selected}>${productName}</option>`;
                    }
                });

                const orderValue = titleData && titleData.order !== undefined ? titleData.order : index;
                const titleHtml = `
                    <div class="card mb-3 title-card" data-title-index="${index}">
                        <input type="hidden" name="box_titles[${index}][order]" class="title-order-input" value="${orderValue}">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col-md-1">
                                    <i class="icon-base ti tabler-grip-vertical drag-handle" style="cursor: move; font-size: 20px;"></i>
                                </div>
                                <div class="col-md-9">
                                    <h6 class="mb-0">{{ __('admin.box_title') }} ${index + 1}</h6>
                                </div>
                                <div class="col-md-2 text-end">
                                    <button type="button" class="btn btn-sm btn-danger remove-title">{{ __('admin.remove_title') }}</button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('admin.title_name') }} ({{ __('admin.'.$properties['name']) }})</label>
                                    <input type="text" class="form-control" 
                                           name="box_titles[${index}][title][{{$localeCode}}]" 
                                           value="${title.{{$localeCode}} || ''}" 
                                           placeholder="{{ __('admin.title_name') }}">
                                </div>
                                @endforeach
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input is-required-checkbox" type="checkbox" 
                                               name="box_titles[${index}][is_required]" 
                                               value="1" ${isRequired ? 'checked' : ''} 
                                               id="is_required_${index}"
                                               data-index="${index}">
                                        <label class="form-check-label" for="is_required_${index}">
                                            {{ __('admin.selection_mandatory_for_customers') }}
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('admin.maximum_number_of_options_customer_can_select') }}</label>
                                    <input type="number" class="form-control" 
                                           name="box_titles[${index}][max_count]" 
                                           value="${maxCount}" min="1" max="10">
                                </div>
                            </div>
                            <div class="row mb-3 min-count-row" id="min_count_row_${index}" style="display: ${isRequired ? 'flex' : 'none'};">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('admin.minimum_number_of_options_customer_must_select') }}</label>
                                    <input type="number" class="form-control min-count-input" 
                                           value="${minCount}" min="0" max="10"
                                           id="min_count_${index}">
                                </div>
                            </div>
                            <!-- Hidden input to always submit min_count even when field is hidden -->
                            <input type="hidden" name="box_titles[${index}][min_count]" id="min_count_hidden_${index}" value="${minCount}">
                            <div class="mb-3">
                                <label class="form-label">{{ __('admin.products') }}</label>
                                <select name="box_titles[${index}][products][]" class="form-select product-select" multiple>
                                    ${productOptions}
                                </select>
                            </div>
                        </div>
                    </div>
                `;
                titlesContainer.insertAdjacentHTML('beforeend', titleHtml);
                
                // Initialize select2 for the new select after a small delay to ensure DOM is ready
                setTimeout(() => {
                    const selectElement = $(`select[name="box_titles[${index}][products][]"]`);
                    selectElement.select2({
                        placeholder: "{{ __('admin.please_select') }}",
                        allowClear: true
                    });
                    
                    // Trigger change to update Select2 display with selected values
                    if (products.length > 0) {
                        const selectedValues = productIds.map(id => String(id));
                        selectElement.val(selectedValues).trigger('change');
                    }
                }, 100);
            }

            // Remove title handler
            titlesContainer.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-title')) {
                    e.target.closest('.title-card').remove();
                    updateTitleOrders();
                    initTitlesSortable();
                }
            });

            // Update title orders after drag and drop
            function updateTitleOrders() {
                const titleCards = titlesContainer.querySelectorAll('.title-card');
                titleCards.forEach(function(card, index) {
                    const orderInput = card.querySelector('.title-order-input');
                    if (orderInput) {
                        orderInput.value = index;
                    }
                    // Update data-title-index
                    card.setAttribute('data-title-index', index);
                    // Update all input names with new index
                    const oldIndex = card.getAttribute('data-title-index');
                    const newIndex = index;
                    card.setAttribute('data-title-index', newIndex);
                    
                    // Update all inputs inside this card
                    card.querySelectorAll('input, select').forEach(function(input) {
                        if (input.name) {
                            input.name = input.name.replace(`box_titles[${oldIndex}]`, `box_titles[${newIndex}]`);
                        }
                    });
                    
                    // Update IDs
                    card.querySelectorAll('[id]').forEach(function(element) {
                        if (element.id) {
                            element.id = element.id.replace(`_${oldIndex}`, `_${newIndex}`);
                        }
                    });
                    
                    // Update for labels
                    card.querySelectorAll('label[for]').forEach(function(label) {
                        if (label.getAttribute('for')) {
                            label.setAttribute('for', label.getAttribute('for').replace(`_${oldIndex}`, `_${newIndex}`));
                        }
                    });
                });
            }

            // Initialize SortableJS for titles
            function initTitlesSortable() {
                if (typeof Sortable === 'undefined') {
                    console.warn('SortableJS is not loaded');
                    return;
                }
                
                if (titlesContainer.sortableInstance) {
                    titlesContainer.sortableInstance.destroy();
                }
                
                titlesContainer.sortableInstance = new Sortable(titlesContainer, {
                    handle: '.drag-handle',
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    onEnd: function() {
                        updateTitleOrders();
                    }
                });
            }

            // Initialize sortable on page load
            setTimeout(function() {
                initTitlesSortable();
            }, 500);

            // Handle is_required checkbox toggle to show/hide min_count field
            titlesContainer.addEventListener('change', function(e) {
                if (e.target.classList.contains('is-required-checkbox')) {
                    const index = e.target.getAttribute('data-index');
                    const minCountRow = document.getElementById(`min_count_row_${index}`);
                    const hiddenInput = document.getElementById(`min_count_hidden_${index}`);
                    if (minCountRow) {
                        if (e.target.checked) {
                            minCountRow.style.display = 'flex';
                            // Sync visible input to hidden input
                            const minCountInput = document.getElementById(`min_count_${index}`);
                            if (minCountInput && hiddenInput) {
                                hiddenInput.value = minCountInput.value || '0';
                            }
                        } else {
                            minCountRow.style.display = 'none';
                            // Keep the value in hidden input, don't reset to 0
                            const minCountInput = document.getElementById(`min_count_${index}`);
                            if (minCountInput && hiddenInput) {
                                hiddenInput.value = minCountInput.value || '0';
                            }
                        }
                    }
                }
            });
            
            // Sync min_count input changes to hidden input
            titlesContainer.addEventListener('input', function(e) {
                if (e.target.classList.contains('min-count-input')) {
                    const index = e.target.getAttribute('id').replace('min_count_', '');
                    const hiddenInput = document.getElementById(`min_count_hidden_${index}`);
                    if (hiddenInput) {
                        hiddenInput.value = e.target.value || '0';
                    }
                }
            });
            
            titlesContainer.addEventListener('change', function(e) {
                if (e.target.classList.contains('min-count-input')) {
                    const index = e.target.getAttribute('id').replace('min_count_', '');
                    const hiddenInput = document.getElementById(`min_count_hidden_${index}`);
                    if (hiddenInput) {
                        hiddenInput.value = e.target.value || '0';
                    }
                }
            });
        });
    </script>
    <script>
        $(document).ready(function () {
            function toggleDiscountFields(currencyId, type) {
                $('.discount-before-' + currencyId).hide();
                $('.discount-fixed-' + currencyId).hide();
                $('.discount-percentage-' + currencyId).hide();
                if (type === 'fixed') {
                    $('.discount-before-' + currencyId).show();
                    $('.discount-fixed-' + currencyId).show();
                } else if (type === 'percentage') {
                    $('.discount-before-' + currencyId).show();
                    $('.discount-percentage-' + currencyId).show();
                }
            }

            $('.discount-type').on('change', function () {
                let type = $(this).val();
                let currencyId = $(this).data('currency');
                toggleDiscountFields(currencyId, type);
            });

            $('.discount-type').each(function () {
                let type = $(this).val();
                let currencyId = $(this).data('currency');
                toggleDiscountFields(currencyId, type);
            });
        });

    </script>
    @include('dashboard.partials.image-cropper-js')
    <script>
        'use strict';
        document.addEventListener('DOMContentLoaded', function () {
            // Wait a bit to ensure all scripts are loaded
            setTimeout(function() {
                // Destroy any existing Dropzone instance
                const dropzoneEl = document.querySelector('#dropzone-basic');
                if (dropzoneEl && dropzoneEl.dropzone) {
                    dropzoneEl.dropzone.destroy();
                }
                const existingInstance = Dropzone.instances.find(dz => dz.element && dz.element.id === 'dropzone-basic');
                if (existingInstance) {
                    existingInstance.destroy();
                }
                
                initImageCropper('#dropzone-basic', 'image');
            }, 100);
        });
    </script>
@endsection
