@extends('dashboard.layout.master')
@section('title', __('admin.product_notes'))
@section('dashboard-main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Basic Bootstrap Table -->
        <div class="card">
            <h5 class="card-header d-flex justify-content-between border-b">
                {{__('admin.product_notes')}}
                <div class="buttons d-flex justify-content-between">
                    @include('dashboard.partials.index.table_btns')
                </div>
            </h5>
            <div class="table-responsive text-nowrap">
                {{ $dataTable->table() }}
            </div>
        </div>
    </div>

    <!-- Note Detail Modal -->
    <div class="modal fade" id="noteDetailModal" tabindex="-1" aria-labelledby="noteDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="noteDetailModalLabel">{{ __('admin.note_details') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="note-detail-content">
                        <p class="text-muted">
                            <i class="fa-solid fa-spinner fa-spin"></i> {{ __('admin.loading') }}
                        </p>
                    </div>
                </div>
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
    <script>
        $(document).on('click', '.view-note', function () {
            let noteId = $(this).data('id');
            $('#noteDetailModalLabel').text("{{ __('admin.note_details') }}");
            $('#note-detail-content').html('<p class="text-muted"><i class="fa-solid fa-spinner fa-spin"></i> {{ __("admin.loading") }}</p>');
            $('#noteDetailModal').modal('show');
            $.ajax({
                url: '{{ route("product-notes.show", ":id") }}'.replace(':id', noteId),
                method: 'GET',
                success: function (response) {
                    $('#note-detail-content').html(response);
                },
                error: function () {
                    $('#note-detail-content').html('<p class="text-danger">{{ __("admin.error") }}</p>');
                }
            });
        });
    </script>
@endsection
