<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class AdministracionController extends Controller
{
    //

    public function cantidadClientes(){
        $cantidad_clientes = DB::table('cliente')->count();
        return ['cantidad_clientes'=>$cantidad_clientes];
    }

    public function cantidadSolicitudes(){
        $cantidad_solicitudes = DB::table('solicitud')->count();
        return ['cantidad_solicitudes'=>$cantidad_solicitudes];
    }

    public function cantidadPlanes(){
        $cantidad_planes = DB::table('plan_pago')->count();
        return ['cantidad_planes'=>$cantidad_planes];
    }

    public function datosGraficoPagos(){
        $datos_pagos=DB::table('pago')->where('estado', 1)
        ->select(DB::raw('sum(monto_pago) as monto_total_pago'), 'pago.fecha_pago')
        ->groupBy('pago.fecha_pago')
        ->get();

     
        return $datos_pagos;
    }

    public function cantidadPlanesPagoUsuarios(){
        $planes_pago = DB::table('users')
        ->join('solicitud', 'solicitud.id_usuario', '=', 'users.id')
        ->join('plan_pago', 'plan_pago.id_solicitud', '=', 'solicitud.id')
        ->select('users.personal', DB::raw('count(plan_pago.id) as cantidad_creditos'))
        ->groupBy('users.personal')
        ->get();

        $solicitudes = DB::table('users')
        ->join('solicitud', 'solicitud.id_usuario', '=', 'users.id')
        // ->join('plan_pago', 'plan_pago.id_solicitud', '=', 'solicitud.id')
        ->select('users.personal', DB::raw('count(solicitud.id) as cantidad_solicitudes'))
        ->groupBy('users.personal')
        ->get();

        return ['planes_pago'=>$planes_pago, 'solicitudes'=>$solicitudes];
    }


    public function getCantidadClientes()
    {
        $cantidad_clientes = DB::table('cliente')->count();
        return response()->json(['cantidad_clientes' => $cantidad_clientes]);
    }

    public function getCantidadSolicitudes()
    {
        $cantidad_solicitudes = DB::table('solicitud')->count();
        return response()->json(['cantidad_solicitudes' => $cantidad_solicitudes]);
    }

    public function getCantidadPlanes()
    {
        $cantidad_planes = DB::table('plan_pago')->count();
        return response()->json(['cantidad_planes' => $cantidad_planes]);
    }

    public function getCreditosPorUsuario()
    {
        $planes_pago = DB::table('users')
            ->select('users.personal', DB::raw('COUNT(plan_pago.id) as cantidad_creditos'))
            ->leftJoin('solicitud', 'users.id', '=', 'solicitud.id_usuario')
            ->leftJoin('plan_pago', 'solicitud.id', '=', 'plan_pago.id_solicitud')
            ->groupBy('users.id', 'users.personal')
            ->get();

        $solicitudes = DB::table('users')
            ->select('users.personal', DB::raw('COUNT(solicitud.id) as cantidad_solicitudes'))
            ->leftJoin('solicitud', 'users.id', '=', 'solicitud.id_usuario')
            ->groupBy('users.id', 'users.personal')
            ->get();

        return response()->json([
            'planes_pago' => $planes_pago,
            'solicitudes' => $solicitudes
        ]);
    }

    public function getDatosPagos()
    {
        $pagos = DB::table('pago')
            ->select(DB::raw("DATE_FORMAT(fecha_pago, '%Y-%m') as mes"), DB::raw('SUM(monto_pago) as ingresos'))
            ->where('estado', 1)
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        return response()->json($pagos);
    }

    public function getEstadisticasPrestamos()
    {
        $activos = DB::table('solicitud')
            ->join('plan_pago', 'solicitud.id', '=', 'plan_pago.id_solicitud')
            ->join('cuota', 'plan_pago.id', '=', 'cuota.id_plan_pago')
            ->where('solicitud.estado', 1)
            ->where('cuota.estado', 1)
            ->distinct('solicitud.id')
            ->count();

        $pagados = DB::table('solicitud')
            ->join('plan_pago', 'solicitud.id', '=', 'plan_pago.id_solicitud')
            ->where('plan_pago.saldo_pendiente', 0)
            ->distinct('solicitud.id')
            ->count();

        $morosos = DB::table('solicitud')
            ->join('plan_pago', 'solicitud.id', '=', 'plan_pago.id_solicitud')
            ->join('cuota', 'plan_pago.id', '=', 'cuota.id_plan_pago')
            ->where('cuota.fecha', '<', now())
            ->where('cuota.estado', 1)
            ->distinct('solicitud.id')
            ->count();

        $monto_desembolsado = DB::table('solicitud')
            ->where('desembolso', 1)
            ->sum('importe_solicitud');

        $saldo_pendiente = DB::table('plan_pago')
            ->sum('saldo_pendiente');

        $total_recaudado = DB::table('pago')
            ->where('estado', 1)
            ->sum('monto_pago');

        $ingresos_dia = DB::table('pago')
            ->where('estado', 1)
            ->whereDate('fecha_pago', now())
            ->sum('monto_pago');

        $ingresos_mes = DB::table('pago')
            ->where('estado', 1)
            ->whereYear('fecha_pago', now()->year)
            ->whereMonth('fecha_pago', now()->month)
            ->sum('monto_pago');

        $ingresos_anio = DB::table('pago')
            ->where('estado', 1)
            ->whereYear('fecha_pago', now()->year)
            ->sum('monto_pago');

        return response()->json([
            'activos' => $activos,
            'pagados' => $pagados,
            'morosos' => $morosos,
            'monto_desembolsado' => $monto_desembolsado,
            'saldo_pendiente' => $saldo_pendiente,
            'total_recaudado' => $total_recaudado,
            'ingresos_dia' => $ingresos_dia,
            'ingresos_mes' => $ingresos_mes,
            'ingresos_anio' => $ingresos_anio
        ]);
    }

    public function getTopClientes()
    {
        $top_clientes = DB::table('cliente')
            ->select('cliente.id', 'cliente.nombre', DB::raw('SUM(plan_pago.saldo_pendiente) as cartera_activa'))
            ->join('solicitud', 'cliente.id', '=', 'solicitud.id_cliente')
            ->join('plan_pago', 'solicitud.id', '=', 'plan_pago.id_solicitud')
            ->where('solicitud.estado', 1)
            ->groupBy('cliente.id', 'cliente.nombre')
            ->orderBy('cartera_activa', 'desc')
            ->limit(5)
            ->get();

        return response()->json($top_clientes);
    }

   
}
