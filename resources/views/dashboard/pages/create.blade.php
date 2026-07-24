@extends('dashboard.layout.master')
@section('title', isset($page) ?  __('admin.edit') . ' ' . $page->title : __('admin.create') .' '. __('admin.page'))

@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <h5 class="card-header">{{ isset($page) ?  __('admin.edit') . ' ' . $page->title : __('admin.create') .' '. __('admin.page') }}</h5>
                    <div class="card-body">
                        <form id="formValidationExamples" class="row g-6" method="POST"
                              action="{{ isset($page) ? route('pages.update', $page->id) : route('pages.store') }}">
                            @csrf
                            @if(isset($page))
                                @method('PUT')
                            @endif

                            <!-- Tabs for languages -->
                            <ul class="nav nav-tabs" id="langTabs" role="tablist">
                                @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                                                id="{{$localeCode}}-tab" data-bs-toggle="tab"
                                                data-bs-target="#{{$localeCode}}" type="button" role="tab">
                                            {{ __('admin.'.$properties['name']) }}
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
                                                <label class="form-label">{{ __('admin.title') }}</label>
                                                <input type="text" class="form-control"
                                                       name="title[{{$localeCode}}]"
                                                       value="{{ old("title.$localeCode", isset($page) ? $page->getTranslation('title',$localeCode) : '') }}">
                                                @error("title.$localeCode")
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-12">
                                                <label class="form-label">{{ __('admin.content') }}</label>
                                                <textarea class="form-control editor"
                                                          name="content[{{$localeCode}}]">{{ old("content.$localeCode", isset($page) ? $page->getTranslation('content',$localeCode) : '') }}</textarea>
                                                @error("content.$localeCode")
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
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
                                        <option value="1">{{ __('admin.active') }}</option>
                                        @if(isset($page) && $page->slug != 'about-us')
                                        <option value="0">{{ __('admin.deactive') }}</option>
                                        @endif
                                    </select>
                                    @error('active')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
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
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">

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
                height: 300,
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
        document.addEventListener('DOMContentLoaded', function () {
            const pageForm = document.getElementById('formValidationExamples');

            if (pageForm) {
                // تمرير رسائل التحقق من Laravel للـ JS
                const messages = @json([
            'title_required'   => __('admin.title_required'),
            'content_required' => __('admin.content_required'),
        ]);

                const fv = FormValidation.formValidation(pageForm, {
                    fields: {
                        @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                        "title[{{$localeCode}}]": {
                            validators: {
                                notEmpty: { message: messages.title_required.replace(':lang', '{{ $properties["name"] }}') }
                            }
                        },
                        "content[{{$localeCode}}]": {
                            validators: {
                                notEmpty: { message: messages.content_required.replace(':lang', '{{ $properties["name"] }}') }
                            }
                        }@if(!$loop->last),@endif
                        @endforeach
                    },
                    plugins: {
                        trigger: new FormValidation.plugins.Trigger(),
                        bootstrap5: new FormValidation.plugins.Bootstrap5(),
                        submitButton: new FormValidation.plugins.SubmitButton(),
                        autoFocus: new FormValidation.plugins.AutoFocus(),
                        defaultSubmit: new FormValidation.plugins.DefaultSubmit()
                    }
                });

                // تنبيه عند وجود حقول غير مكتملة في التبويب
                fv.on('core.form.invalid', function () {
                    const firstInvalidField = pageForm.querySelector('.is-invalid');
                    if (firstInvalidField) {
                        const tabPane = firstInvalidField.closest('.tab-pane');
                        if (tabPane) {
                            const tabId = tabPane.getAttribute('id');
                            const tabTrigger = document.querySelector(`[data-bs-target="#${tabId}"]`);
                            if (tabTrigger) {
                                const tabName = tabTrigger.innerText.trim();
                                Swal.fire({
                                    icon: 'error',
                                    title: '{{ __("admin.validation_error") }}',
                                    text: `"${tabName}" {{ __("admin.fields_incomplete") }}`,
                                    confirmButtonText: '{{ __("admin.ok") }}'
                                });
                            }
                        }
                    }
                });
            }
        });
    </script>

@endsection
