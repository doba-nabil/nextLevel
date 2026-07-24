@extends('dashboard.layout.master')
@section('title', __('admin.notifications'))

@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <h5 class="card-header d-flex justify-content-between">
                        {{ __('admin.notifications') }}
                        <button type="button" class="btn btn-sm btn-primary" id="mark-all-read-btn">
                            <i class="icon-base ti tabler-mail-opened me-1"></i>
                            {{ __('admin.mark_all_as_read') }}
                        </button>
                    </h5>
                    <div class="card-body">
                        @if($notifications->count() > 0)
                            <div class="list-group">
                                @foreach($notifications as $notification)
                                    <div class="list-group-item list-group-item-action {{ $notification->is_read ? '' : 'bg-light' }}">
                                        <div class="d-flex w-100 justify-content-between">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">
                                                    @if(!$notification->is_read)
                                                        <span class="badge bg-primary me-2">{{ __('admin.new') }}</span>
                                                    @endif
                                                    {{ $notification->title }}
                                                </h6>
                                                <p class="mb-1">{{ $notification->message }}</p>
                                                <small class="text-body-secondary">{{ $notification->created_at->diffForHumans() }}</small>
                                            </div>
                                            <div class="flex-shrink-0">
                                                @if($notification->order_id)
                                                    <a href="{{ route('orders.show', $notification->order_id) }}" class="btn btn-sm btn-primary me-2">
                                                        <i class="icon-base ti tabler-eye"></i> {{ __('admin.view_order') }}
                                                    </a>
                                                @endif
                                                @if(!$notification->is_read)
                                                    <button type="button" class="btn btn-sm btn-secondary mark-read-btn" data-id="{{ $notification->id }}">
                                                        <i class="icon-base ti tabler-mail-opened"></i> {{ __('admin.mark_as_read') }}
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-3">
                                {{ $notifications->links() }}
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="icon-base ti tabler-bell-off icon-48px text-muted mb-3"></i>
                                <p class="text-muted">{{ __('admin.no_notifications') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('dashboard-footer')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mark as read
            document.querySelectorAll('.mark-read-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    fetch(`{{ url('admin/notifications') }}/${id}/mark-as-read`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    }).then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        }
                    });
                });
            });

            // Mark all as read
            document.getElementById('mark-all-read-btn').addEventListener('click', function() {
                fetch('{{ route("notifications.mark-all-as-read") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                }).then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                });
            });
        });
    </script>
@endsection
