<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\PlanPagoExport;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    //
    public function exportPlanPago()
    {
        return Excel::download(new PlanPagoExport, 'planes_pago.xlsx');
    }
}
