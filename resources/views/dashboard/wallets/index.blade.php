@extends('dashboard.layout.master')
@section('title', __('admin.wallets_transactions'))

@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <h5 class="card-header d-flex justify-content-between border-b">
                <span>
                    {{__('admin.wallets_transactions')}}
                    <br>
                    <small>
                        @if(request('user_id'))
                            @php
                                $user = \App\Models\User::find(request('user_id'));
                                $balance = $user?->wallet?->balance ?? 0;
                            @endphp
                            {{ __('admin.wallet_amount') . ': ' . number_format($balance, 3) }}
                        @endif
                    </small>
                </span>
                <div class="buttons d-flex justify-content-between">
                        @include('dashboard.partials.index.table_btns')
                    <div>
                        <a class="btn btn-primary" href="{{ url('admin/wallets/create') }}"><i
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
