@extends('dashboard.layout.master')
@section('title', __('admin.additionals'))
@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Basic Bootstrap Table -->
        <button type="button" class="btn btn-success d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="menu-icon icon-base ti tabler-upload"></i>
            {{ __('admin.import_excel') }}
        </button>
        <br>
        <div class="card">

            <h5 class="card-header d-flex justify-content-between border-b">
                {{__('admin.additionals')}}
                <br>
                <div class="buttons d-flex justify-content-between">
                    @include('dashboard.partials.index.table_btns')
                    <a class="btn btn-primary" href="{{ url('admin/addons/create') }}"><i
                            class="menu-icon icon-base ti tabler-plus"></i> {{ __('admin.add_new') }}</a>
                </div>
            </h5>

            <div class="table-responsive text-nowrap">
                {{ $dataTable->table() }}
            </div>
        </div>
    </div>

    <!-- Modal الاستيراد -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="importModalLabel">
                        <i class="ti ti-upload me-1"></i> {{ __('admin.import_excel') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="{{ route('addons.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="file" class="form-label fw-bold">{{ __('admin.choose_excel_file') }}</label>
                            <input type="file" name="file" id="file" class="form-control" accept=".xlsx,.xls" required>
                            <small class="text-muted d-block mt-2">
                                ⚠️ {{ __('admin.allowed_file_types') }}: .xlsx, .xls
                            </small>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('admin.close') }}</button>
                        <button type="submit" class="btn btn-success">{{ __('admin.import') }}</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

@endsection
@section('dashboard-head')
    @include('dashboard.partials.index.css')
@endsection

@section('dashboard-footer')
    {{ $dataTable->scripts() }}
    @include('dashboard.partials.index.js')
@endsection

