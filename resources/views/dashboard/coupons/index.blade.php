@extends('dashboard.layout.master')
@section('title', __('admin.coupons'))

@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <h5 class="card-header d-flex justify-content-between border-b">
                <span>
                    {{__('admin.coupons')}}
                </span>
                <div class="buttons d-flex justify-content-between">
                        @include('dashboard.partials.index.table_btns')
                    <div>
                        <a class="btn btn-primary" href="{{ url('admin/coupons/create') }}"><i
                                class="menu-icon icon-base ti tabler-plus"></i> {{ __('admin.add_new') }}</a>
                    </div>
                </div>
            </h5>
            <div class="table-responsive text-nowrap">
                {{ $dataTable->table() }}
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
