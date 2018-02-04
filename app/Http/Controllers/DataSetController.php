<?php

namespace App\Http\Controllers;

use App\DataTables\DataSetDataTable;
use App\Models\DataSet;
use Illuminate\Http\Request;
use Yajra\Datatables\Facades\Datatables;

class DataSetController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(DataSetDataTable $dataTable)
    {
        $title = 'Service implementation with footer column search.';
        return $dataTable->render('datatables.service.footer');
    }
}
