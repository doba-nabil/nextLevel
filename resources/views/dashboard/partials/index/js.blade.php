<script src="https://cdn.datatables.net/2.3.3/js/dataTables.js"></script>
<script src="http://cdn.datatables.net/2.3.3/js/dataTables.bootstrap5.js"></script>
<script src="{{ asset('dashboard') }}/assets/js/tables-datatables-extensions.js"></script>
<script src="{{ asset('dashboard') }}/assets/vendor/libs/sweetalert2/sweetalert2.js"></script>
<script src="https://cdn.datatables.net/buttons/1.0.3/js/dataTables.buttons.min.js"></script>
<script src="/vendor/datatables/buttons.server-side.js"></script>
<script>
    $(function () {
        if (typeof window.LaravelDataTables !== 'undefined') {
            Object.keys(window.LaravelDataTables).forEach(function (tableId) {
                let table = window.LaravelDataTables[tableId];
                $('#export-excel').on('click', function () {
                    table.button('.buttons-excel').trigger();
                });
                $('#export-csv').on('click', function () {
                    table.button('.buttons-csv').trigger();
                });
                $('#export-pdf').on('click', function () {
                    table.button('.buttons-pdf').trigger();
                });
                $('#export-print').on('click', function () {
                    table.button('.buttons-print').trigger();
                });
            });
        }
    });
</script>
<script>
    $(document).on('click', '.delete-btn', function() {
        let tableSelector = $(this).data('table');
        let url = $(this).data('url');

        Swal.fire({
            title: '{{ __("admin.sure") }}',
            text: "{{ __("admin.cant") }}",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: '{{ __("admin.yes_sure") }}',
            cancelButtonText: '{{ __("admin.cancel") }}'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire(
                            '{{ __("admin.delete_success") }}',
                            response.message,
                            'success'
                        );
                        $(tableSelector).DataTable().ajax.reload();
                    },
                    error: function(xhr) {
                        let message = '{{ __("admin.delete_error") }}';
                        if(xhr.responseJSON && xhr.responseJSON.message){
                            message = xhr.responseJSON.message;
                        }
                        Swal.fire('{{ __("admin.delete_error") }}', message, 'error');
                    }
                });
            }
        });
    });
</script>
<script>
    // Handle status toggle switch for all admin tables
    $(document).ready(function() {
        $(document).on('change', '.status-toggle', function() {
            const $toggle = $(this);
            const itemId = $toggle.data('id');
            const url = $toggle.data('url');
            const isChecked = $toggle.is(':checked');
            
            // Disable toggle during request
            $toggle.prop('disabled', true);
            
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    active: isChecked ? 1 : 0,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.status === 'success') {
                        // Success - toggle stays in new position
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: '{{ __("admin.success") }}',
                                text: response.message || '{{ __("admin.update_success") }}',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                    } else {
                        // Revert toggle on error
                        $toggle.prop('checked', !isChecked);
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: '{{ __("admin.error") }}',
                                text: response.message || '{{ __("admin.update_error") }}'
                            });
                        }
                    }
                },
                error: function(xhr) {
                    // Revert toggle on error
                    $toggle.prop('checked', !isChecked);
                    const message = xhr.responseJSON?.message || '{{ __("admin.error_occurred") }}';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: '{{ __("admin.error") }}',
                            text: message
                        });
                    }
                },
                complete: function() {
                    // Re-enable toggle after request
                    $toggle.prop('disabled', false);
                }
            });
        });
    });
</script>