<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\DataTables\OrdersDataTable;

class TestController extends Controller
{


    public function orders(OrdersDataTable $dataTable)
    {
        return $dataTable->render('dashboard.orders');
    }
}
