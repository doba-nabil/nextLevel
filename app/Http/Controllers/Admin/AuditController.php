<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\AuditDataTable;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(AuditDataTable $dataTable)
    {
        return $dataTable->render('dashboard.audits.index');
    }

    public function show($id)
    {
        $audit = \OwenIt\Auditing\Models\Audit::with('user')->findOrFail($id);
        return view('dashboard.audits.show', compact('audit'));
    }
}
