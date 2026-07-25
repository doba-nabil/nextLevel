@extends('dashboard.layout.master')
@section('title', $title)

@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="py-3 mb-4">
            <span class="text-muted fw-light">{{ __('admin.dashboard') ?? 'لوحة التحكم' }} /</span> {{ $title }}
        </h4>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <h5 class="card-header">{{ __('admin.copy_branch_menu_details') ?? 'تفاصيل نسخ القائمة بين الفروع' }}</h5>
                    <div class="card-body">
                        <form action="{{ route('admin.branch-menu-copier.copy') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="source_branch_id">{{ __('admin.source_branch') ?? 'الفرع المصدر (يتم النسخ منه)' }}</label>
                                    <select name="source_branch_id" id="source_branch_id" class="form-select select2" required>
                                        <option value="" disabled selected>{{ __('admin.select_branch') ?? 'اختر الفرع' }}</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="destination_branch_id">{{ __('admin.destination_branch') ?? 'الفرع الهدف (يتم النسخ إليه)' }}</label>
                                    <select name="destination_branch_id" id="destination_branch_id" class="form-select select2" required>
                                        <option value="" disabled selected>{{ __('admin.select_branch') ?? 'اختر الفرع' }}</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <label class="form-label">{{ __('admin.items_to_copy') ?? 'العناصر المراد نسخها' }}</label>
                                    <div class="d-flex gap-4">
                                        <div class="form-check">
                                            <input class="form-check-input type-checkbox" type="checkbox" name="types[]" value="product" id="type_product" checked>
                                            <label class="form-check-label" for="type_product">
                                                {{ __('admin.products') ?? 'المنتجات' }}
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input type-checkbox" type="checkbox" name="types[]" value="meal" id="type_meal" checked>
                                            <label class="form-check-label" for="type_meal">
                                                {{ __('admin.meals') ?? 'الوجبات' }}
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input type-checkbox" type="checkbox" name="types[]" value="box" id="type_box" checked>
                                            <label class="form-check-label" for="type_box">
                                                {{ __('admin.boxes') ?? 'البوكسات' }}
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="select_all_types" checked>
                                            <label class="form-check-label fw-bold text-primary" for="select_all_types">
                                                {{ __('admin.all') ?? 'الكل' }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="delete_old" name="delete_old" value="1">
                                        <label class="form-check-label text-danger" for="delete_old">
                                            {{ __('admin.delete_old_items') ?? 'حذف القديم (سيتم حذف العناصر الموجودة مسبقاً في الفرع الهدف والتي تتطابق مع الأنواع المحددة أعلاه قبل النسخ)' }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary" id="submit_copy_btn">
                                        {{ __('admin.copy_now') ?? 'تنفيذ النسخ الآن' }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('dashboard-footer')
    <script>
        $(document).ready(function() {
            if ($('.select2').length) {
                $('.select2').select2({
                    theme: 'bootstrap-5'
                });
            }

            // Handle select all checkbox
            $('#select_all_types').change(function() {
                $('.type-checkbox').prop('checked', $(this).prop('checked'));
            });

            // Handle individual checkboxes to update "select all" state
            $('.type-checkbox').change(function() {
                if ($('.type-checkbox:checked').length == $('.type-checkbox').length) {
                    $('#select_all_types').prop('checked', true);
                } else {
                    $('#select_all_types').prop('checked', false);
                }
            });

            // Prevent same source and destination
            $('form').on('submit', function(e) {
                var source = $('#source_branch_id').val();
                var dest = $('#destination_branch_id').val();

                if (source === dest) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: '{{ __('admin.error') ?? "خطأ" }}',
                        text: '{{ __('admin.source_destination_same') ?? "لا يمكن أن يكون الفرع المصدر والهدف متطابقين." }}'
                    });
                    return false;
                }

                if ($('.type-checkbox:checked').length === 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: '{{ __('admin.error') ?? "خطأ" }}',
                        text: '{{ __('admin.select_at_least_one_type') ?? "يجب اختيار نوع واحد على الأقل للنسخ." }}'
                    });
                    return false;
                }
                
                // Show confirmation if deleting old
                if ($('#delete_old').is(':checked')) {
                    e.preventDefault();
                    var form = this;
                    Swal.fire({
                        title: '{{ __('admin.are_you_sure') ?? "هل أنت متأكد؟" }}',
                        text: '{{ __('admin.delete_warning_text') ?? "لقد اخترت حذف القديم. هذا الإجراء سيحذف القائمة الحالية من الفرع الهدف للأنواع المحددة فقط قبل النسخ ولا يمكن التراجع عنه." }}',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: '{{ __('admin.yes_copy_delete') ?? "نعم، احذف وانسخ" }}',
                        cancelButtonText: '{{ __('admin.cancel') ?? "إلغاء" }}'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('#submit_copy_btn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> جاري النسخ...');
                            form.submit();
                        }
                    });
                } else {
                    $('#submit_copy_btn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> جاري النسخ...');
                }
            });
        });
    </script>
@endsection
