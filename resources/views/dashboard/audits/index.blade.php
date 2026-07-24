@extends('dashboard.layout.master')
@section('title', __('admin.audits'))
@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Basic Bootstrap Table -->
        <div class="card">
            <h5 class="card-header">{{__('admin.audits')}}</h5>
            <div class="table-responsive text-nowrap">
                {{ $dataTable->table() }}
            </div>
<br>
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

