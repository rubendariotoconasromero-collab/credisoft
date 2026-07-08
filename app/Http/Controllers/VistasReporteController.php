<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Mpdf\Mpdf;
use App\Traits\CalculaAmortizaciones;


class VistasReporteController extends Controller
{
    use CalculaAmortizaciones;
    //

    public function indexExtracto(){
        return view('vistasReportes.repExtracto');
    }

    public function getClientesRep(){
        return DB::table('cliente')->get();
    }

    public function getCreditosRep(Request $request){
        $query = DB::table('plan_pago')
            ->join('solicitud', 'solicitud.id', '=', 'plan_pago.id_solicitud')
            ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
            ->join('users', 'users.id', '=', 'solicitud.id_usuario')
            ->select(
                'plan_pago.id',
                'plan_pago.total_pagar',
                'plan_pago.fecha_inicio',
                'plan_pago.fecha_fin',
                'plan_pago.estado',
                'plan_pago.fecha_registro',
                'solicitud.importe_solicitud',
                'solicitud.moneda',
                'solicitud.tasa',
                'solicitud.nro_cuotas',
                'solicitud.lapso_capital',
                'cliente.nombre as cliente_nombre',
                'cliente.ci as cliente_ci',
                'cliente.id as id_cliente',
                'users.personal as asesor_nombre'
            )
            ->whereIn('plan_pago.estado', [1, 2]); // Solo Vigente (1) o Terminado (2)

        // Filtro por código de crédito
        if ($request->filled('id_credito')) {
            $query->where('plan_pago.id', $request->id_credito);
        }

        // Filtro por cliente (nombre o CI)
        if ($request->filled('buscar_cliente')) {
            $busqueda = '%' . $request->buscar_cliente . '%';
            $query->where(function($q) use ($busqueda) {
                $q->where('cliente.nombre', 'like', $busqueda)
                  ->orWhere('cliente.ci', 'like', $busqueda);
            });
        }

        // Filtro por cliente específico (dropdown popover fallback)
        if ($request->filled('id_cliente') && $request->id_cliente != 0) {
            $query->where('cliente.id', $request->id_cliente);
        }

        // Filtro por estado
        if ($request->filled('estado_plan') && $request->estado_plan != 'todos') {
            if ($request->estado_plan == 'vigente') {
                $query->where('plan_pago.estado', 1);
            } elseif ($request->estado_plan == 'terminado') {
                $query->where('plan_pago.estado', 2);
            }
        }

        // Filtro por rango de fechas
        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->whereBetween('plan_pago.fecha_registro', [$request->fecha_inicio, $request->fecha_fin]);
        }

        return $query->orderBy('plan_pago.id', 'desc')->get();
    }

    public function getDetalleCreditoExtracto(Request $request){
        $id_plan_pago = $request->id_plan_pago;

        // 1. Información del crédito y cliente
        $credito = DB::table('plan_pago')
            ->join('solicitud', 'solicitud.id', '=', 'plan_pago.id_solicitud')
            ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
            ->join('users', 'users.id', '=', 'solicitud.id_usuario')
            ->select(
                'plan_pago.id',
                'plan_pago.total_pagar',
                'plan_pago.fecha_inicio',
                'plan_pago.fecha_fin',
                'plan_pago.estado',
                'plan_pago.fecha_registro',
                'plan_pago.desembolso',
                'plan_pago.pago_administrativo',
                'plan_pago.fecha_ultima_amortizacion',
                'solicitud.importe_solicitud',
                'solicitud.moneda',
                'solicitud.tasa',
                'solicitud.nro_cuotas',
                'solicitud.lapso_capital',
                'solicitud.destino_prestamo',
                'solicitud.tipo_garantia',
                'solicitud.tipo_desembolso',
                'cliente.nombre as cliente_nombre',
                'cliente.ci as cliente_ci',
                'cliente.lugar_expedicion as cliente_expedicion',
                'cliente.actividad as cliente_actividad',
                'cliente.vivienda as cliente_vivienda',
                'cliente.ingreso_mensual as cliente_ingreso',
                'users.personal as asesor_nombre'
            )
            ->where('plan_pago.id', $id_plan_pago)
            ->first();

        if (!$credito) {
            return response()->json(['message' => 'Crédito no encontrado'], 404);
        }

        // 2. Cuotas con sus pagos combinados
        $cuotas = DB::table('cuota')
            ->where('id_plan_pago', $id_plan_pago)
            ->orderBy('numero', 'asc')
            ->get();

        foreach ($cuotas as $cuota) {
            $cuota->pagos = DB::table('pago')
                ->join('users', 'users.id', '=', 'pago.id_usuario')
                ->select(
                    'pago.id',
                    'pago.codigo_transaccion',
                    'pago.fecha_pago',
                    'pago.monto_pago',
                    'pago.multa_total',
                    'pago.pago_capital',
                    'pago.pago_interes',
                    'pago.pago_mora',
                    'pago.forma_pago',
                    'users.personal as cajero_nombre'
                )
                ->where('pago.id_cuota', $cuota->id)
                ->where('pago.estado', 1) // Activo
                ->orderBy('pago.fecha_pago', 'asc')
                ->get();
        }

        return response()->json([
            'credito' => $credito,
            'cuotas' => $cuotas
        ]);
    }

    public function getCreditosMoraRep(Request $request)
    {
        return response()->json($this->buildCreditosMoraQuery($request));
    }

    public function exportarCreditosMoraPdf(Request $request)
    {
        $creditos = $this->buildCreditosMoraQuery($request);
        $empresa  = DB::table('mi_empresa')->first();
        $data = [
            'creditos'       => $creditos,
            'empresa'        => $empresa,
            'usuario'        => auth()->user()->name,
            'fecha_reporte'  => now()->format('d/m/Y'),
        ];
        $this->generatePDF($data, 'vistasReportes.pdf_creditos_mora', 'Creditos_Mora_' . date('Y-m-d'), 'L');
    }

    public function exportarCreditosMoraExcel(Request $request)
    {
        $creditos = $this->buildCreditosMoraQuery($request);
        $empresa  = DB::table('mi_empresa')->first();
        $data = [
            'creditos'      => $creditos,
            'empresa'       => $empresa,
            'usuario'       => auth()->user()->name,
            'fecha_reporte' => now()->format('d/m/Y'),
        ];
        return response(view('vistasReportes.excel_creditos_mora', $data))
            ->header('Content-Type', 'application/vnd.ms-excel; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="Creditos_Mora_' . date('Y-m-d') . '.xls"');
    }

    private function buildCreditosMoraQuery(Request $request)
    {
        $id_credito    = $request->id_credito;
        $buscar_cliente = $request->buscar_cliente;
        $id_asesor     = $request->id_asesor;
        $dias_mora_min = $request->dias_mora_min;

        $query = DB::table('plan_pago')
            ->join('solicitud', 'solicitud.id', '=', 'plan_pago.id_solicitud')
            ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
            ->join('users', 'users.id', '=', 'solicitud.id_usuario')
            ->join('cuota', 'cuota.id_plan_pago', '=', 'plan_pago.id')
            ->whereIn('cuota.estado', [1, 3])
            ->whereDate('cuota.fecha', '<', now())
            ->whereIn('plan_pago.estado', [1, 2])
            ->select(
                'plan_pago.id as plan_pago_id',
                'solicitud.id as credito_id',
                'solicitud.id_cliente',
                'solicitud.importe_solicitud',
                'solicitud.moneda',
                'solicitud.tasa',
                'solicitud.nro_cuotas',
                'solicitud.lapso_capital',
                'plan_pago.saldo_pendiente',
                'cliente.nombre as cliente_nombre',
                'cliente.ci as cliente_ci',
                'users.personal as asesor_nombre',
                'users.id as id_asesor',
                DB::raw('COUNT(cuota.id) as cuotas_mora_count'),
                DB::raw('MAX(DATEDIFF(NOW(), cuota.fecha)) as dias_mora_max'),
                DB::raw('SUM(cuota.capital - cuota.capital_pagado) as total_capital_mora'),
                DB::raw('SUM(cuota.interes - cuota.interes_pagado) as total_interes_mora'),
                DB::raw('SUM(cuota.total - (cuota.capital_pagado + cuota.interes_pagado)) as total_cuota_mora'),
                DB::raw('SUM(CASE WHEN DATEDIFF(NOW(), cuota.fecha) > 0 THEN (DATEDIFF(NOW(), cuota.fecha) * 3) - cuota.mora_pagada ELSE 0 END) as total_multas_mora')
            )
            ->groupBy(
                'plan_pago.id', 'solicitud.id', 'solicitud.id_cliente',
                'solicitud.importe_solicitud', 'solicitud.moneda', 'solicitud.tasa',
                'solicitud.nro_cuotas', 'solicitud.lapso_capital', 'plan_pago.saldo_pendiente',
                'cliente.nombre', 'cliente.ci', 'users.personal', 'users.id'
            );

        if (!empty($id_credito)) {
            $query->where('solicitud.id', $id_credito);
        }
        if (!empty($buscar_cliente)) {
            $query->where(function ($q) use ($buscar_cliente) {
                $q->where('cliente.nombre', 'LIKE', '%' . $buscar_cliente . '%')
                  ->orWhere('cliente.ci', 'LIKE', '%' . $buscar_cliente . '%');
            });
        }
        if (!empty($id_asesor) && is_numeric($id_asesor)) {
            $query->where('solicitud.id_usuario', (int) $id_asesor);
        }
        if (!empty($dias_mora_min) && is_numeric($dias_mora_min)) {
            $query->havingRaw('MAX(DATEDIFF(NOW(), cuota.fecha)) >= ?', [(int) $dias_mora_min]);
        }

        $creditos = $query->orderBy('dias_mora_max', 'desc')->get();

        foreach ($creditos as $credito) {
            $telefonos = DB::table('telefono')
                ->where('id_cliente', $credito->id_cliente)
                ->select('numero', 'tipo', 'relacion')
                ->get()
                ->map(function ($t) {
                    $label = $t->numero;
                    if (!empty($t->tipo) || !empty($t->relacion)) {
                        $sub = [];
                        if (!empty($t->tipo)) $sub[] = $t->tipo;
                        if (!empty($t->relacion)) $sub[] = $t->relacion;
                        $label .= ' (' . implode('-', $sub) . ')';
                    }
                    return $label;
                })
                ->toArray();

            $credito->cliente_telefonos = empty($telefonos) ? 'Sin teléfonos' : implode(', ', $telefonos);
            $credito->telefonos_list    = $telefonos;

            // Amortización en tiempo real (misma lógica que la pantalla de cobros):
            // calcula interés devengado, moratorio, acumulado, mora fija y total a pagar.
            $amortizacion = $this->calcularAmortizacionesCuotas($credito->plan_pago_id);

            // Solo las cuotas vencidas (estado pendiente/parcial con fecha vencida).
            $hoy = now()->startOfDay();
            $cuotasMora = $amortizacion['cuotas']->filter(function ($cuota) use ($hoy) {
                return in_array($cuota->estado, [1, 3])
                    && \Carbon\Carbon::parse($cuota->fecha)->startOfDay()->lt($hoy);
            })->values();

            $credito->cuotas_mora = $cuotasMora;

            // Totales del crédito recalculados desde la amortización para que
            // coincidan con la suma de las cuotas mostradas (incluye moratorio + mora fija).
            $credito->cuotas_mora_count  = $cuotasMora->count();
            $credito->total_capital_mora = round($cuotasMora->sum('capital_neto'), 2);
            $credito->total_interes_mora = round($cuotasMora->sum('interes_acumulado_neto'), 2);
            $credito->total_multas_mora  = round($cuotasMora->sum('mora_fija_neta'), 2);
            $credito->total_cuota_mora   = round($cuotasMora->sum('total_a_pagar'), 2);
            $credito->dias_mora_max      = (int) $cuotasMora->max('dias_pasados');
        }

        return $creditos;
    }

    public function generarReporteExtracto(Request $request){
        $informacion=DB::table('solicitud')
        ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
        ->join('users', 'users.id', '=', 'solicitud.id_usuario')
        ->join('plan_pago', 'plan_pago.id_solicitud', '=', 'solicitud.id')
        ->join('cuota', 'cuota.id_plan_pago', '=', 'plan_pago.id')
        ->where('plan_pago.id', $request->id_plan_pago)
        ->select('solicitud.id','solicitud.importe_solicitud', 'solicitud.moneda', 'solicitud.lapso_capital', 'solicitud.nro_cuotas',
        'solicitud.tasa', 'solicitud.fecha_desembolso', 'solicitud.fecha_primera_cuota', 'solicitud.destino_prestamo',
        'solicitud.tipo_garantia','solicitud.tipo_desembolso','solicitud.id_cliente', 'solicitud.id_usuario', 'cliente.nombre as cliente',
        'cliente.ci', 'cliente.lugar_expedicion',
        'users.name as nombre_asesor', 'plan_pago.estado', 'users.personal')
        ->get();


        $cuotas = DB::table('plan_pago')
        ->join('cuota', 'plan_pago.id', '=', 'cuota.id_plan_pago')
        ->select('cuota.*')
        ->where('plan_pago.id', $request->id_plan_pago)
        ->orderBy('cuota.numero', 'asc')
        ->get();

        foreach ($cuotas as $cuota) {
            $cuota->pagos = DB::table('pago')
                ->where('pago.id_cuota', $cuota->id)
                ->where('pago.estado', 1) // Activo
                ->orderBy('pago.fecha_pago', 'asc')
                ->get();
        }



         // Carga la vista HTML para el reporte
         $data  = [
             'informacion' => $informacion,
             'id_plan_pago' => $request->id_plan_pago,
             'detalles' => $cuotas,
             'usuario' => auth()->user()->name,
             'fecha_reporte' => now()->format('d/m/Y'),
            //  'cantidad_planes'=>$cantidad_registros,
            //  'planes_pago'=>$planes_pago,
             
         ];

         $this->generatePDF($data, 'reporte_vistas.reporte_extracto_credito', 'reporte_extrato_credito');
    }

    public function indexHistCreditoMora(){
        return view('vistasReportes.repHistCreditoMora');
    }

    public function generarReporteCreditosMora(Request $request){
        $fechaFiltro = $request->fecha_fin;  // O la fecha que quieras usar, puede ser una variable dinámica

        $planesConPrimeraCuota = DB::table('plan_pago')
        ->join('cuota', 'plan_pago.id', '=', 'cuota.id_plan_pago')
        ->join('solicitud', 'solicitud.id', '=', 'plan_pago.id_solicitud')
        ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
        ->select('solicitud.lapso_capital','cliente.nombre as nombre_cliente', 'cliente.ci', 'cliente.lugar_expedicion','solicitud.nro_cuotas','plan_pago.id as id_plan_pago','plan_pago.*', 'cuota.*', 
        DB::raw("CASE WHEN cuota.estado = 1 THEN DATEDIFF(?, cuota.fecha) ELSE 0 END as dias_pasados"))
        
        ->whereRaw('cuota.id = (
            SELECT c.id 
            FROM cuota as c 
            WHERE c.id_plan_pago = plan_pago.id
            AND DATEDIFF(?, c.fecha) > 0 
            AND c.estado = 1 
            ORDER BY c.fecha ASC 
            LIMIT 1
        )', [$fechaFiltro, $fechaFiltro])  // Pasas la fecha dos veces, una para cada DATEDIFF
        ->orderBy('plan_pago.fecha_inicio', 'asc')   // Ordenar por la fecha de inicio del plan de pago
        ->get();

        // Carga la vista HTML para el reporte
        $data  = [
            'detalles' => $planesConPrimeraCuota,
            'fecha_fin'=>$fechaFiltro,
            'usuario' => auth()->user()->name,
            'fecha_reporte' => now()->format('d/m/Y'),
            
        ];

        $this->generatePDF($data, 'reporte_vistas.reporte_creditos_mora', 'reporte_creditos_mora');

    }

    public function indexClientesMora(){
        return view('vistasReportes.repClientesMora');
    }

    public function generarReporteClientesMora(Request $request){
        $fechaFiltro = $request->fecha_fin;  // O la fecha que quieras usar, puede ser una variable dinámica

        $planesConPrimeraCuota = DB::table('plan_pago')
        ->join('cuota', 'plan_pago.id', '=', 'cuota.id_plan_pago')
        ->join('solicitud', 'solicitud.id', '=', 'plan_pago.id_solicitud')
        ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
        ->select('solicitud.lapso_capital','cliente.nombre as nombre_cliente', 'cliente.ci', 'cliente.lugar_expedicion','solicitud.nro_cuotas','plan_pago.id as id_plan_pago','plan_pago.*', 'cuota.*', 
        DB::raw("CASE WHEN cuota.estado = 1 THEN DATEDIFF(?, cuota.fecha) ELSE 0 END as dias_pasados"))
        
        ->whereRaw('cuota.id = (
            SELECT c.id 
            FROM cuota as c 
            WHERE c.id_plan_pago = plan_pago.id
            AND DATEDIFF(?, c.fecha) > 0 
            AND c.estado = 1 
            ORDER BY c.fecha ASC 
            LIMIT 1
        )', [$fechaFiltro, $fechaFiltro])  // Pasas la fecha dos veces, una para cada DATEDIFF
        ->orderBy('dias_pasados', 'asc')   // Ordenar por la fecha de inicio del plan de pago
        ->get();

        // Carga la vista HTML para el reporte
        $data  = [
            'detalles' => $planesConPrimeraCuota,
            'fecha_fin'=>$fechaFiltro,
            'usuario' => auth()->user()->name,
            'fecha_reporte' => now()->format('d/m/Y'),
            
        ];

        $this->generatePDF($data, 'reporte_vistas.reporte_clientes_mora', 'reporte_clientes_mora');
    }

    public function indexPagosRealizados(Request $request){
        return view('vistasReportes.repPagosRealizados');
    }

    public function generarReportePagosRealizados(Request $request){
        $pagos = DB::table('pago')
        ->join('cuota', 'pago.id_cuota', '=', 'cuota.id')
        ->join('users', 'users.id', '=', 'pago.id_usuario')
        ->join('plan_pago', 'plan_pago.id', '=', 'cuota.id_plan_pago')
        ->join('solicitud', 'plan_pago.id_solicitud', '=', 'solicitud.id')
        ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
        ->select('pago.imagen','pago.id', 'pago.fecha_pago', 'pago.forma_pago', 'pago.monto_pago', 'pago.id_cuota', 'pago.estado',
        'users.name as asesor', 'users.personal as nombre_asesor', 'cliente.nombre as cliente', 'cuota.numero as cuota', 'solicitud.nro_cuotas',
        'plan_pago.id as plan_pago', 'plan_pago.total_pagar as total_pago_credito', 'cuota.saldo_capital', 'cuota.interes', 
        'cuota.capital')
        ->where('pago.estado', 1)
        // ->where($request->opcion, 'LIKE', '%'.$request->buscar.'%')
        ->whereDate('pago.fecha_pago', '>=', $request->fecha_inicial)
        ->whereDate('pago.fecha_pago', '<=', $request->fecha_final)
        ->orderBy('pago.id', 'desc')
        ->get();

        $totalPagos=DB::table('pago')
        ->join('cuota', 'pago.id_cuota', '=', 'cuota.id')
        ->join('users', 'users.id', '=', 'pago.id_usuario')
        ->join('plan_pago', 'plan_pago.id', '=', 'cuota.id_plan_pago')
        ->join('solicitud', 'plan_pago.id_solicitud', '=', 'solicitud.id')
        ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
        ->select(DB::raw('sum(pago.monto_pago) as totalPagos'))
        // ->where($request->opcion, 'LIKE', '%'.$request->buscar.'%')
        ->whereDate('pago.fecha_pago', '>=', $request->fecha_inicial)
        ->whereDate('pago.fecha_pago', '<=', $request->fecha_final)
        ->where('pago.estado', 1)
        ->get()[0]->totalPagos;


        // Carga la vista HTML para el reporte
        $data  = [
            'detalles' => $pagos,
            'total_pagos' => $totalPagos,
            'fecha_inicio'=>$request->fecha_inicial,
            'fecha_fin'=>$request->fecha_final,

            'usuario' => auth()->user()->name,
            'fecha_reporte' => now()->format('d/m/Y'),
            
        ];

        $this->generatePDF($data, 'reporte_vistas.reporte_pagos_realizados', 'reporte_pagos_realizados');


    }

    public function indexPagosProgramados(){
        return view('vistasReportes.repPagosProgramados');
    }

    public function getPagosProgramadosRep(Request $request)
    {
        return response()->json($this->buildPagosProgramadosQuery($request));
    }

    public function exportarPagosProgramadosPdf(Request $request)
    {
        $cuotas  = $this->buildPagosProgramadosQuery($request);
        $empresa = DB::table('mi_empresa')->first();
        $data = [
            'cuotas'        => $cuotas,
            'empresa'       => $empresa,
            'usuario'       => auth()->user()->name,
            'fecha_reporte' => now()->format('d/m/Y'),
            'filtros'       => $request->all(),
        ];
        $this->generatePDF($data, 'vistasReportes.pdf_pagos_programados', 'Pagos_Programados_' . date('Y-m-d'));
    }

    public function exportarPagosProgramadosExcel(Request $request)
    {
        $cuotas  = $this->buildPagosProgramadosQuery($request);
        $empresa = DB::table('mi_empresa')->first();
        $data = [
            'cuotas'        => $cuotas,
            'empresa'       => $empresa,
            'usuario'       => auth()->user()->name,
            'fecha_reporte' => now()->format('d/m/Y'),
            'filtros'       => $request->all(),
        ];
        return response(view('vistasReportes.excel_pagos_programados', $data))
            ->header('Content-Type', 'application/vnd.ms-excel; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="Pagos_Programados_' . date('Y-m-d') . '.xls"');
    }

    private function buildPagosProgramadosQuery(Request $request)
    {
        $query = DB::table('cuota')
            ->join('plan_pago', 'plan_pago.id', '=', 'cuota.id_plan_pago')
            ->join('solicitud', 'solicitud.id', '=', 'plan_pago.id_solicitud')
            ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
            ->join('users', 'users.id', '=', 'solicitud.id_usuario')
            ->whereIn('cuota.estado', [1, 3])
            ->where('plan_pago.estado', 1)
            ->select(
                'solicitud.id as credito_id',
                'plan_pago.id as plan_pago_id',
                'solicitud.id_cliente',
                'cliente.nombre as cliente_nombre',
                'cliente.ci as cliente_ci',
                'users.personal as asesor_nombre',
                'users.id as id_asesor',
                'solicitud.nro_cuotas',
                'solicitud.lapso_capital',
                'solicitud.moneda',
                'cuota.id as cuota_id',
                'cuota.numero as nro_cuota',
                'cuota.fecha as fecha_vencimiento',
                'cuota.capital',
                'cuota.interes',
                'cuota.total',
                'cuota.capital_pagado',
                'cuota.interes_pagado',
                'cuota.estado as cuota_estado',
                DB::raw('DATEDIFF(cuota.fecha, CURDATE()) as dias_para_pago'),
                DB::raw('cuota.capital - cuota.capital_pagado as capital_pendiente'),
                DB::raw('cuota.interes - cuota.interes_pagado as interes_pendiente'),
                DB::raw('cuota.total - (cuota.capital_pagado + cuota.interes_pagado) as monto_pendiente')
            );

        if ($request->filled('fecha_inicio')) {
            $query->whereDate('cuota.fecha', '>=', $request->fecha_inicio);
        }
        if ($request->filled('fecha_fin')) {
            $query->whereDate('cuota.fecha', '<=', $request->fecha_fin);
        }
        if ($request->filled('buscar_cliente')) {
            $busqueda = '%' . $request->buscar_cliente . '%';
            $query->where(function ($q) use ($busqueda) {
                $q->where('cliente.nombre', 'LIKE', $busqueda)
                  ->orWhere('cliente.ci', 'LIKE', $busqueda);
            });
        }
        if ($request->filled('id_asesor') && is_numeric($request->id_asesor)) {
            $query->where('solicitud.id_usuario', (int) $request->id_asesor);
        }
        if ($request->filled('lapso_capital')) {
            $query->where('solicitud.lapso_capital', $request->lapso_capital);
        }

        return $query->orderBy('cuota.fecha', 'asc')->orderBy('solicitud.id', 'asc')->get();
    }

    public function generarReportesPagosProgramados(Request $request){

            // $fechaActual = Carbon::now(); // Obtiene la fecha y hora actual

            $totalMontoAPagar = DB::table('cuota')
            ->join('plan_pago', 'cuota.id_plan_pago', '=', 'plan_pago.id')
            ->join('solicitud', 'plan_pago.id_solicitud', '=', 'solicitud.id')
            ->join('cliente', 'solicitud.id_cliente', '=', 'cliente.id')
            ->join('users', 'solicitud.id_usuario', '=', 'users.id')
            ->where('cuota.estado', 1) // Estado 1: no pagada
            ->where('cuota.amortizado', 0) // No amortizada
            ->whereDate('cuota.fecha', '>=', $request->fecha_inicial) // Filtrar por fecha de inicio
            ->whereDate('cuota.fecha', '<=', $request->fecha_final)   // Filtrar por fecha final
            ->whereIn('cuota.id', function ($query) {
                $query->select(DB::raw('MIN(c.id)'))
                    ->from('cuota as c')
                    ->join('plan_pago as pp', 'c.id_plan_pago', '=', 'pp.id')
                    ->where('c.estado', 1) // Solo cuotas no pagadas
                    ->where('c.amortizado', 0) // No amortizadas
                    ->groupBy('pp.id'); // Agrupar por plan de pago
            })
            ->sum('cuota.total'); // Obtener la suma del campo 'cuota.total'


            $pagos = DB::table('cuota')
            ->join('plan_pago', 'cuota.id_plan_pago', '=', 'plan_pago.id')
            ->join('solicitud', 'plan_pago.id_solicitud', '=', 'solicitud.id')
            ->join('cliente', 'solicitud.id_cliente', '=', 'cliente.id')
            ->join('users', 'solicitud.id_usuario', '=', 'users.id')
            ->select(
                'cuota.estado',
                'cuota.id',
                'cuota.numero as cuota',
                'cliente.nombre as cliente',
                'solicitud.nro_cuotas',
                'plan_pago.id as plan_pago',
                'plan_pago.total_pagar as total_pago_credito',
                'cuota.fecha as fecha_a_pagar',
                'cuota.total as monto_a_pagar',
                'cuota.saldo_capital',
                'cuota.interes',
                'cuota.capital',
                'users.personal as nombre_asesor',
                'users.name as asesor',
                DB::raw('DATEDIFF(NOW(), cuota.fecha) as dias_pasados') // Calcula la diferencia en días desde la fecha de pago
            )
            ->where('cuota.estado', 1) // Estado 1: no pagada
            ->where('cuota.amortizado', 0) // No amortizada
            ->whereDate('cuota.fecha', '>=', $request->fecha_inicial) // Filtrar por fecha de inicio
            ->whereDate('cuota.fecha', '<=', $request->fecha_final)   // Filtrar por fecha final
            ->whereIn('cuota.id', function ($query) {
                $query->select(DB::raw('MIN(c.id)'))
                    ->from('cuota as c')
                    ->join('plan_pago as pp', 'c.id_plan_pago', '=', 'pp.id')
                    ->where('c.estado', 1) // Solo cuotas no pagadas
                    ->where('c.amortizado', 0) // No amortizadas
                    ->groupBy('pp.id'); // Agrupar por plan de pago
            })
            ->orderBy('cuota.fecha', 'asc') // Ordena por fecha ascendente
            ->get();


            

            // Carga la vista HTML para el reporte
            $data  = [
                'detalles' => $pagos,
                'total_pagos_programados' => $totalMontoAPagar,
                'fecha_inicio'=>$request->fecha_inicial,
                'fecha_fin'=>$request->fecha_final,
                'usuario' => auth()->user()->name,
                'fecha_reporte' => now()->format('d/m/Y'),
                
            ];

            $this->generatePDF($data, 'reporte_vistas.reporte_pagos_programados', 'reporte_pagos_programados');


    }

    public function reporteExtractoMovimientos(Request $request){
        $informacion=DB::table('solicitud')
        ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
        ->join('users', 'users.id', '=', 'solicitud.id_usuario')
        ->join('plan_pago', 'plan_pago.id_solicitud', '=', 'solicitud.id')
        ->join('cuota', 'cuota.id_plan_pago', '=', 'plan_pago.id')
        ->where('plan_pago.id', $request->id_plan_pago)
        ->select('solicitud.id','solicitud.importe_solicitud', 'solicitud.moneda', 'solicitud.lapso_capital', 'solicitud.nro_cuotas',
        'solicitud.tasa', 'solicitud.fecha_desembolso', 'solicitud.fecha_primera_cuota', 'solicitud.destino_prestamo',
        'solicitud.tipo_garantia','solicitud.tipo_desembolso','solicitud.id_cliente', 'solicitud.id_usuario', 'cliente.nombre as cliente',
        'cliente.ci', 'cliente.lugar_expedicion',
        'users.name as nombre_asesor', 'plan_pago.estado', 'users.personal')
        ->get();

        // 1. Desembolsos
        $desembolsos = DB::table('desembolso')
        ->join('plan_pago', 'plan_pago.id', '=', 'desembolso.id_plan_pago')
        ->where('plan_pago.id', $request->id_plan_pago)
        ->select(
            'desembolso.fecha as fecha', 
            'desembolso.monto as debe', // Desembolso es un gasto (debe)
            DB::raw("'Desembolso' as tipo"), // Tipo de movimiento
            DB::raw("CONCAT('Desembolso, Crédito: ', plan_pago.id) as descripcion"), // Descripción corregida
            DB::raw('0 as haber') // No hay haber en desembolsos
        )
        ->get();

        // 2. Pagos Administrativos
        $pagos_adm = DB::table('pago_administrativo')
        ->join('plan_pago', 'plan_pago.id', '=', 'pago_administrativo.id_plan_pago')
        ->where('plan_pago.id', $request->id_plan_pago)
        ->select(
            'pago_administrativo.fecha as fecha', 
            'pago_administrativo.monto as haber', // Pago Administrativo es un ingreso (haber)
            DB::raw("'Pago Administrativo' as tipo"), // Tipo de movimiento
            DB::raw("CONCAT('Pago Administrativo, Crédito: ', plan_pago.id) as descripcion"), // Descripción corregida
            DB::raw('0 as debe') // No hay debe en pagos administrativos
        )
        ->get();

        // 3. Intereses
        $intereses = DB::table('plan_pago')
        ->join('cuota', 'cuota.id_plan_pago', '=', 'plan_pago.id')
        ->join('pago', 'pago.id_cuota', '=', 'cuota.id')
        ->where('plan_pago.id', $request->id_plan_pago)
        ->select(
            'pago.fecha_pago as fecha', 
            'cuota.interes as haber', // El cliente paga interés (haber)
            DB::raw("'Interés' as tipo"), // Tipo de movimiento
            DB::raw("CONCAT('Interés, Crédito: ', plan_pago.id, ', Cuota: ', cuota.numero) as descripcion"), // Descripción corregida
            DB::raw('0 as debe') // No hay debe en el pago de intereses
        )
        ->get();

        // 4. Capital
        $capitales = DB::table('plan_pago')
        ->join('cuota', 'cuota.id_plan_pago', '=', 'plan_pago.id')
        ->join('pago', 'pago.id_cuota', '=', 'cuota.id')
        ->where('plan_pago.id', $request->id_plan_pago)
        ->select(
            'pago.fecha_pago as fecha', 
            'cuota.capital as haber', // El cliente paga capital (haber)
            DB::raw("'Capital' as tipo"), // Tipo de movimiento
            DB::raw("CONCAT('Capital, Crédito: ', plan_pago.id, ', Cuota: ', cuota.numero) as descripcion"), // Descripción corregida
            DB::raw('0 as debe') // No hay debe en el pago de capital
        )
        ->get();

        // Combinar todos los resultados en un solo arreglo
        $movimientos = collect($desembolsos)
        ->merge($pagos_adm) // Pagos Administrativos como ingreso (haber)
        ->merge($intereses)
        ->merge($capitales);

        // Ordenar los movimientos por fecha
        $movimientos = $movimientos->sortBy('fecha')->values()->all();

        // Carga la vista HTML para el reporte
        $data  = [
            'informacion' => $informacion,
            'id_plan_pago' => $request->id_plan_pago,
            'movimientos' => $movimientos,
            'usuario' => auth()->user()->name,
            'fecha_reporte' => now()->format('d/m/Y'),    
            'hora_reporte' => now()->format('H:i:s'),
        ];

        $this->generatePDF($data, 'reporte_vistas.reporte_movimientos_credito', 'reporte_movimientos_credito');


    }
    
    public function indexMovimientosCredito(){
        return view('vistasReportes.repMovimientosCredito');
    }

    public function indexPorcentajesPagos(){
        return view('vistasReportes.repPorcentajesPagos');
    }

    public function getPorcentajesCreditosRep(Request $request)
    {
        return response()->json($this->buildAvanceCreditosQuery($request));
    }

    public function exportarAvanceCreditosPdf(Request $request)
    {
        $registros = $this->buildAvanceCreditosQuery($request);
        $empresa   = DB::table('mi_empresa')->first();
        $data = [
            'registros'     => $registros,
            'empresa'       => $empresa,
            'usuario'       => auth()->user()->name,
            'fecha_reporte' => now()->format('d/m/Y'),
            'filtros'       => $request->all(),
        ];
        $this->generatePDF($data, 'vistasReportes.pdf_avance_creditos', 'Avance_Creditos_' . date('Y-m-d'));
    }

    public function exportarAvanceCreditosExcel(Request $request)
    {
        $registros = $this->buildAvanceCreditosQuery($request);
        $empresa   = DB::table('mi_empresa')->first();
        $data = [
            'registros'     => $registros,
            'empresa'       => $empresa,
            'usuario'       => auth()->user()->name,
            'fecha_reporte' => now()->format('d/m/Y'),
            'filtros'       => $request->all(),
        ];
        return response(view('vistasReportes.excel_avance_creditos', $data))
            ->header('Content-Type', 'application/vnd.ms-excel; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="Avance_Creditos_' . date('Y-m-d') . '.xls"');
    }

    private function buildAvanceCreditosQuery(Request $request)
    {
        $query = DB::table('plan_pago')
            ->join('solicitud', 'solicitud.id', '=', 'plan_pago.id_solicitud')
            ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
            ->join('users', 'users.id', '=', 'solicitud.id_usuario')
            ->join('cuota', 'cuota.id_plan_pago', '=', 'plan_pago.id')
            ->select(
                'plan_pago.id as plan_pago_id',
                'solicitud.id as credito_id',
                'solicitud.id_cliente',
                'solicitud.importe_solicitud',
                'solicitud.moneda',
                'solicitud.lapso_capital',
                'solicitud.nro_cuotas',
                'plan_pago.total_pagar',
                'plan_pago.saldo_pendiente',
                'plan_pago.estado as estado_plan',
                'cliente.nombre as cliente_nombre',
                'cliente.ci as cliente_ci',
                'users.personal as asesor_nombre',
                'users.id as id_asesor',
                DB::raw('SUM(cuota.capital_pagado) as capital_pagado'),
                DB::raw('SUM(cuota.interes_pagado) as interes_pagado'),
                DB::raw('COUNT(CASE WHEN cuota.estado = 2 THEN 1 END) as cuotas_pagadas'),
                DB::raw('ROUND(CASE WHEN plan_pago.total_pagar > 0 THEN (SUM(cuota.capital_pagado) / plan_pago.total_pagar) * 100 ELSE 0 END, 2) as porcentaje_pagado')
            )
            ->groupBy(
                'plan_pago.id', 'solicitud.id', 'solicitud.id_cliente',
                'solicitud.importe_solicitud', 'solicitud.moneda', 'solicitud.lapso_capital',
                'solicitud.nro_cuotas', 'plan_pago.total_pagar', 'plan_pago.saldo_pendiente',
                'plan_pago.estado', 'cliente.nombre', 'cliente.ci', 'users.personal', 'users.id'
            );

        // Estado del plan
        if ($request->filled('estado_plan') && $request->estado_plan !== 'todos') {
            $query->where('plan_pago.estado', (int) $request->estado_plan);
        } else {
            $query->whereIn('plan_pago.estado', [1, 2]);
        }

        // Rango de porcentaje
        if ($request->filled('pct_inicio')) {
            $query->havingRaw('ROUND(CASE WHEN plan_pago.total_pagar > 0 THEN (SUM(cuota.capital_pagado) / plan_pago.total_pagar) * 100 ELSE 0 END, 2) >= ?', [(float) $request->pct_inicio]);
        }
        if ($request->filled('pct_fin')) {
            $query->havingRaw('ROUND(CASE WHEN plan_pago.total_pagar > 0 THEN (SUM(cuota.capital_pagado) / plan_pago.total_pagar) * 100 ELSE 0 END, 2) <= ?', [(float) $request->pct_fin]);
        }

        // Cliente
        if ($request->filled('buscar_cliente')) {
            $busqueda = '%' . $request->buscar_cliente . '%';
            $query->where(function ($q) use ($busqueda) {
                $q->where('cliente.nombre', 'LIKE', $busqueda)
                  ->orWhere('cliente.ci', 'LIKE', $busqueda);
            });
        }

        // Asesor
        if ($request->filled('id_asesor') && is_numeric($request->id_asesor)) {
            $query->where('solicitud.id_usuario', (int) $request->id_asesor);
        }

        // Frecuencia
        if ($request->filled('lapso_capital')) {
            $query->where('solicitud.lapso_capital', $request->lapso_capital);
        }

        return $query->orderBy('porcentaje_pagado', 'desc')->get();
    }
    private function generatePDF($data, $url_vista, $nombre_reporte, $orientation = 'P')
    {
        $mpdf = new Mpdf([
            'mode'        => 'utf-8',
            'format'      => 'A4',
            'orientation' => $orientation, // 'P' vertical (default) | 'L' horizontal
        ]);

        // Configurar el pie de página con el número de página
        $mpdf->SetFooter('Página {PAGENO} de {nbpg}');  // {PAGENO} es el número de la página actual y {nbpg} es el número total de páginas

        $html = view($url_vista, $data)->render();

        // Temporarily suppress PHP warnings and notices for PHP 8 compatibility with mPDF's CSS/OTL parser
        $prev_reporting = error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
        try {
            $mpdf->WriteHTML($html);
        } finally {
            error_reporting($prev_reporting);
        }

        $mpdf->Output($nombre_reporte, 'I');
    }

    public function reportePorcentajesCreditos(Request $request){
        $registros = DB::table('solicitud')
        ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
        ->join('users', 'users.id', '=', 'solicitud.id_usuario')
        ->join('plan_pago', 'plan_pago.id_solicitud', '=', 'solicitud.id')
        ->join('cuota', 'cuota.id_plan_pago', '=', 'plan_pago.id')
        ->join('pago', 'pago.id_cuota', '=', 'cuota.id')
        ->select(
            'solicitud.id', 
            'solicitud.importe_solicitud', 
            'solicitud.moneda', 
            'solicitud.lapso_capital', 
            'solicitud.nro_cuotas',
            'solicitud.tasa', 
            'solicitud.fecha_desembolso', 
            'solicitud.fecha_primera_cuota', 
            'solicitud.destino_prestamo',
            'solicitud.tipo_garantia',
            'solicitud.tipo_desembolso',
            'solicitud.id_cliente', 
            'solicitud.id_usuario', 
            'cliente.nombre as cliente',
            'cliente.ci', 
            'cliente.lugar_expedicion', 
            'plan_pago.id as id_plan_pago',
            'users.personal as nombre_asesor', 
            'plan_pago.estado', 
            'users.personal', 
            'plan_pago.total_pagar',
            DB::raw('sum(cuota.capital) as sum_capital'),
            DB::raw('(sum(cuota.capital) / plan_pago.total_pagar) * 100 as porcentaje_pagado') // Cálculo del porcentaje pagado
        )
        ->groupBy(
            'solicitud.id', 
            'solicitud.importe_solicitud', 
            'solicitud.moneda', 
            'solicitud.lapso_capital', 
            'solicitud.nro_cuotas',
            'solicitud.tasa', 
            'solicitud.fecha_desembolso', 
            'solicitud.fecha_primera_cuota', 
            'solicitud.destino_prestamo',
            'solicitud.tipo_garantia', 
            'solicitud.tipo_desembolso', 
            'solicitud.id_cliente', 
            'solicitud.id_usuario', 
            'cliente.nombre',
            'cliente.ci', 
            'cliente.lugar_expedicion', 
            'plan_pago.id', 
            'users.name', 
            'plan_pago.estado', 
            'users.personal', 
            'plan_pago.total_pagar'
        )
        ->having(DB::raw('(sum(cuota.capital) / plan_pago.total_pagar) * 100'), '>=', $request->inicio)
        ->having(DB::raw('(sum(cuota.capital) / plan_pago.total_pagar) * 100'), '<=', $request->fin)
        ->get();


        $data  = [
            'registros' => $registros,
            'inicio' => $request->inicio,
            'fin' => $request->fin,
            'usuario' => auth()->user()->name,
            'fecha_reporte' => now()->format('d/m/Y'),    
            'hora_reporte' => now()->format('H:i:s'),
        ];

        $this->generatePDF($data, 'reporte_vistas.reporte_porcentajes_creditos', 'reporte_porcentajes_creditos');
    }


    public function indexDesembolsos(Request $request){
        return view('vistasReportes.repDesembolsos');
    }

    public function getDesembolsosRep(Request $request)
    {
        return response()->json($this->buildDesembolsosQuery($request));
    }

    public function exportarDesembolsosPdf(Request $request)
    {
        $registros         = $this->buildDesembolsosQuery($request);
        $total_desembolso  = $registros->sum('monto');
        $total_pago_adm    = $registros->sum('monto_pago_adm');
        $empresa           = DB::table('mi_empresa')->first();
        $data = [
            'registros'        => $registros,
            'total_desembolso' => $total_desembolso,
            'total_pago_adm'   => $total_pago_adm,
            'empresa'          => $empresa,
            'usuario'          => auth()->user()->name,
            'fecha_reporte'    => now()->format('d/m/Y'),
            'filtros'          => $request->all(),
        ];
        $this->generatePDF($data, 'vistasReportes.pdf_desembolsos', 'Desembolsos_' . date('Y-m-d'));
    }

    public function exportarDesembolsosExcel(Request $request)
    {
        $registros         = $this->buildDesembolsosQuery($request);
        $total_desembolso  = $registros->sum('monto');
        $total_pago_adm    = $registros->sum('monto_pago_adm');
        $empresa           = DB::table('mi_empresa')->first();
        $data = [
            'registros'        => $registros,
            'total_desembolso' => $total_desembolso,
            'total_pago_adm'   => $total_pago_adm,
            'empresa'          => $empresa,
            'usuario'          => auth()->user()->name,
            'fecha_reporte'    => now()->format('d/m/Y'),
            'filtros'          => $request->all(),
        ];
        return response(view('vistasReportes.excel_desembolsos', $data))
            ->header('Content-Type', 'application/vnd.ms-excel; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="Desembolsos_' . date('Y-m-d') . '.xls"');
    }

    private function buildDesembolsosQuery(Request $request)
    {
        $query = DB::table('desembolso')
            ->join('plan_pago', 'plan_pago.id', '=', 'desembolso.id_plan_pago')
            ->join('solicitud', 'solicitud.id', '=', 'plan_pago.id_solicitud')
            ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
            ->join('users', 'users.id', '=', 'solicitud.id_usuario')
            ->select(
                'plan_pago.id as id_plan_pago',
                'solicitud.id as credito_id',
                'desembolso.fecha as fecha_desembolso',
                'cliente.nombre as cliente_nombre',
                'cliente.ci as cliente_ci',
                'users.personal as asesor_nombre',
                'users.id as id_asesor',
                'solicitud.estado as estado_credito',
                'solicitud.tipo_garantia',
                'solicitud.lapso_capital',
                'solicitud.nro_cuotas',
                'solicitud.moneda',
                'solicitud.monto_pago_adm',
                'desembolso.monto'
            );

        if ($request->filled('fecha_inicio')) {
            $query->whereDate('desembolso.fecha', '>=', $request->fecha_inicio);
        }
        if ($request->filled('fecha_fin')) {
            $query->whereDate('desembolso.fecha', '<=', $request->fecha_fin);
        }
        if ($request->filled('buscar_cliente')) {
            $busqueda = '%' . $request->buscar_cliente . '%';
            $query->where(function ($q) use ($busqueda) {
                $q->where('cliente.nombre', 'LIKE', $busqueda)
                  ->orWhere('cliente.ci', 'LIKE', $busqueda);
            });
        }
        if ($request->filled('id_asesor') && is_numeric($request->id_asesor)) {
            $query->where('solicitud.id_usuario', (int) $request->id_asesor);
        }
        if ($request->filled('lapso_capital')) {
            $query->where('solicitud.lapso_capital', $request->lapso_capital);
        }

        return $query->orderBy('desembolso.fecha', 'desc')->get();
    }

    public function reporteDesembolsos(Request $request){
        $registros = DB::table('desembolso')
        ->join('plan_pago', 'plan_pago.id', '=', 'desembolso.id_plan_pago')
        ->join('solicitud', 'solicitud.id', '=', 'plan_pago.id_solicitud')
        ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
        ->whereDate('desembolso.fecha', '>=', $request->fecha_inicial)
        ->whereDate('desembolso.fecha', '<=', $request->fecha_final)
        ->select('plan_pago.id as id_plan_pago', 'desembolso.fecha as fecha_desembolso', 'cliente.nombre as cliente', 
        'cliente.ci', 'solicitud.estado as estado_credito', 'solicitud.tipo_garantia', 'solicitud.lapso_capital', 
        'solicitud.nro_cuotas', 'solicitud.monto_pago_adm', 'desembolso.monto')
        ->get();

        $total_desembolso = DB::table('desembolso')
        ->join('plan_pago', 'plan_pago.id', '=', 'desembolso.id_plan_pago')
        ->join('solicitud', 'solicitud.id', '=', 'plan_pago.id_solicitud')
        ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
        ->whereDate('desembolso.fecha', '>=', $request->fecha_inicial)
        ->whereDate('desembolso.fecha', '<=', $request->fecha_final)
        ->sum('desembolso.monto');

        $data  = [
            'registros' => $registros,
            'total_desembolso' => $total_desembolso,
            'fecha_inicio' => $request->fecha_inicial,
            'fecha_fin' => $request->fecha_final,
            'usuario' => auth()->user()->name,
            'fecha_reporte' => now()->format('d/m/Y'),    
            'hora_reporte' => now()->format('H:i:s'),
        ];

        $this->generatePDF($data, 'reporte_vistas.reporte_desembolsos', 'reporte_desembolsos');

    }



    public function indexDesembolsosOficial(Request $request){
        return view('vistasReportes.repDesembolsosOficial');
    }


    public function reporteDesembolsosOficial(Request $request){
        $registros = DB::table('desembolso')
        ->join('plan_pago', 'plan_pago.id', '=', 'desembolso.id_plan_pago')
        ->join('solicitud', 'solicitud.id', '=', 'plan_pago.id_solicitud')
        ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
        ->join('users', 'users.id', '=', 'solicitud.id_usuario')
        ->where('users.id', $request->id_asesor)
        ->whereDate('desembolso.fecha', '>=', $request->fecha_inicial)
        ->whereDate('desembolso.fecha', '<=', $request->fecha_final)
        ->select('plan_pago.id as id_plan_pago', 'desembolso.fecha as fecha_desembolso', 'cliente.nombre as cliente', 
        'cliente.ci', 'solicitud.estado as estado_credito', 'solicitud.tipo_garantia', 'solicitud.lapso_capital', 
        'solicitud.nro_cuotas', 'solicitud.monto_pago_adm', 'desembolso.monto')
        ->get();

        $total_desembolso = DB::table('desembolso')
        ->join('plan_pago', 'plan_pago.id', '=', 'desembolso.id_plan_pago')
        ->join('solicitud', 'solicitud.id', '=', 'plan_pago.id_solicitud')
        ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
        ->join('users', 'users.id', '=', 'solicitud.id_usuario')
        ->where('users.id', $request->id_asesor)
        ->whereDate('desembolso.fecha', '>=', $request->fecha_inicial)
        ->whereDate('desembolso.fecha', '<=', $request->fecha_final)
        ->sum('desembolso.monto');

        $asesor=DB::table('users')
        ->where('users.id', $request->id_asesor)
        ->first();

        $data  = [
            'registros' => $registros,
            'total_desembolso' => $total_desembolso,
            'fecha_inicio' => $request->fecha_inicial,
            'fecha_fin' => $request->fecha_final,
            'usuario' => auth()->user()->name,
            'fecha_reporte' => now()->format('d/m/Y'),    
            'hora_reporte' => now()->format('H:i:s'),
            'nombre_asesor'=>$asesor->personal,
            'ci_asesor'=>$asesor->ci,
            // 'lugar_expedicion'=>$asesor->lugar_expedicion,
        ];

        $this->generatePDF($data, 'reporte_vistas.reporte_desembolsos_oficial', 'reporte_desembolsos_oficial');
    }


    public function indexDesembolsosPendientes(){
        return view('vistasReportes.repDesembolsosPendientes');
    }

    public function reporteDesembolsosPendientes(Request $request){
        $registros = DB::table('plan_pago')
        // ->join('plan_pago', 'plan_pago.id', '=', 'desembolso.id_plan_pago')
        ->join('solicitud', 'solicitud.id', '=', 'plan_pago.id_solicitud')
        ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
        ->join('users', 'users.id', '=', 'solicitud.id_usuario')
        ->where('plan_pago.desembolso', 1)
        // ->where('users.id', $request->id_asesor)
        ->whereDate('solicitud.fecha_desembolso', '>=', $request->fecha_inicial)
        ->whereDate('solicitud.fecha_desembolso', '<=', $request->fecha_final)
        ->select('plan_pago.id as id_plan_pago', 'solicitud.fecha_desembolso', 'cliente.nombre as cliente', 
        'cliente.ci', 'solicitud.estado as estado_credito', 'solicitud.tipo_garantia', 'solicitud.lapso_capital', 
        'solicitud.nro_cuotas', 'solicitud.monto_pago_adm', 'plan_pago.total_pagar as monto')
        ->get();

        $total_desembolso_pendiente = DB::table('plan_pago')
        // ->join('plan_pago', 'plan_pago.id', '=', 'desembolso.id_plan_pago')
        ->join('solicitud', 'solicitud.id', '=', 'plan_pago.id_solicitud')
        ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
        ->join('users', 'users.id', '=', 'solicitud.id_usuario')
        ->where('plan_pago.desembolso', 1)
        ->whereDate('solicitud.fecha_desembolso', '>=', $request->fecha_inicial)
        ->whereDate('solicitud.fecha_desembolso', '<=', $request->fecha_final)
        ->sum('plan_pago.total_pagar');

        // $asesor=DB::table('users')
        // ->where('users.id', $request->id_asesor)
        // ->first();

        $data  = [
            'registros' => $registros,
            'total_desembolso' => $total_desembolso_pendiente,
            'fecha_inicio' => $request->fecha_inicial,
            'fecha_fin' => $request->fecha_final,
            'usuario' => auth()->user()->name,
            'fecha_reporte' => now()->format('d/m/Y'),    
            'hora_reporte' => now()->format('H:i:s'),
            // 'nombre_asesor'=>$asesor->personal,
            // 'ci_asesor'=>$asesor->ci,
            // 'lugar_expedicion'=>$asesor->lugar_expedicion,
        ];

        $this->generatePDF($data, 'reporte_vistas.reporte_desembolsos_pendientes', 'reporte_desembolsos_pendientes');
    }





    
}
