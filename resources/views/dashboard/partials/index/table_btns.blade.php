<div class="d-flex justify-content-end me-3">
    <div class="dropdown">
        <button class="btn btn-outline-primary dropdown-toggle" type="button" id="exportDropdown"
                data-bs-toggle="dropdown" aria-expanded="false">
            <i class="icon-base ti tabler-download me-2"></i>  {{ __('admin.export') }}
        </button>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="exportDropdown">
            <li>
                <a class="dropdown-item" href="#" id="export-excel">
                    <i class="icon-base ti tabler-file-spreadsheet text-success"></i> Excel
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="#" id="export-csv">
                    <i class="icon-base ti tabler-file text-info"></i> CSV
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="#" id="export-pdf">
                    <i class="icon-base ti tabler-file-text text-danger"></i> PDF
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="#" id="export-print">
                    <i class="icon-base ti tabler-printer text-dark"></i> Print
                </a>
            </li>
        </ul>
    </div>
</div>
