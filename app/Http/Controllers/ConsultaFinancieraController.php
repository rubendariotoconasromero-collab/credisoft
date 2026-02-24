<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ConsultaFinancieraController extends Controller
{
    public function index()
    {
        return view('frmConsultaFinanciera');
    }
}
