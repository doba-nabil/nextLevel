@extends('dashboard.layout.master')
@section('title', __('admin.audit_details'))

@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <h5 class="card-header">{{ __('admin.audit_details') }}</h5>
                    <div class="card-body">

                        <div class="mb-3">
                            <strong>{{ __('admin.model') }}:</strong> {{ class_basename($audit->auditable_type) }}
                        </div>
                        <div class="mb-3">
                            <strong>{{ __('admin.user') }}:</strong> {{ $audit->user ? $audit->user->name : __('admin.system') }}
                        </div>
                        <div class="mb-3">
                            <strong>{{ __('admin.event') }}:</strong> {{ __('audit.' . $audit->event, ['entity' => class_basename($audit->auditable_type)]) }}
                        </div>
                        <div class="mb-3">
                            <strong>{{ __('admin.date') }}:</strong> {{ $audit->created_at->format('Y-m-d H:i') }}
                        </div>

                        <h6 class="mt-4">{{ __('admin.old_values') }}</h6>
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>{{ __('admin.field') }}</th>
                                <th>{{ __('admin.value') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($audit->old_values as $field => $value)
                                <tr>
                                    <td>{{ $field }}</td>
                                    <td class="bg-light text-muted">{{ is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) : $value }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        <h6 class="mt-4">{{ __('admin.new_values') }}</h6>
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>{{ __('admin.field') }}</th>
                                <th>{{ __('admin.value') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($audit->new_values as $field => $value)
                                @php
                                    $old = $audit->old_values[$field] ?? null;
                                    $changed = $old != $value;
                                @endphp
                                <tr>
                                    <td>{{ $field }}</td>
                                    <td @if($changed) class="bg-warning fw-bold" @endif>
                                        {{ is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) : $value }}
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        <div class="mt-3">
                            <a href="{{ route('audits.index') }}" class="btn btn-secondary">{{ __('admin.back') }}</a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
