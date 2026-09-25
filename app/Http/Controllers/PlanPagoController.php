<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Dompdf\Options;
use DB;
use Carbon\Carbon;
use App\Models\Cuota;
use Mpdf\Mpdf;

use Illuminate\Support\Facades\Auth;
use App\Traits\CalculaAmortizaciones;


class PlanPagoController extends Controller
{
    use CalculaAmortizaciones;
    //
    public function index(){ 
        return view('frmPlanPago');
    }

  
    public function getPlanesPago(Request $request)
    {
        $fechaActual = Carbon::now()->format('Y-m-d');

        $planes_pago = \App\Services\CarteraAccess::query('plan_pago')
            ->join('solicitud', 'solicitud.id', '=', 'plan_pago.id_solicitud')
            ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
            ->join('users', 'users.id', '=', 'solicitud.id_usuario')
            ->select(
                'solicitud.id as id_solicitud',
                'solicitud.importe_solicitud',
                'solicitud.moneda',
                'solicitud.lapso_capital',
                'solicitud.tipo_tasa',
                'solicitud.nro_cuotas',
                'solicitud.tasa',
                'solicitud.fecha_desembolso',
                'solicitud.fecha_primera_cuota',
                'solicitud.destino_prestamo',
                'solicitud.tipo_garantia',
                'solicitud.tipo_desembolso',
                'solicitud.id_cliente',
                'solicitud.id_usuario',
                'solicitud.estado',
                'solicitud.tipo_solicitud',
                'solicitud.id_solicitud_origen',
                'cliente.nombre as cliente',
                'users.personal as asesor',
                // OJO AQUÍ: Verifica si esta columna existe en tu BD, si no, usa 'plan_pago.created_at'
                'plan_pago.fecha_registro', 
                'plan_pago.fecha_inicio as fecha_inicio_plan',
                'plan_pago.fecha_fin as fecha_fin_plan',
                'plan_pago.total_pagar as total_pagar_plan',
                'plan_pago.estado as estado_plan',
                'plan_pago.id',
                'cliente.ci',
                'cliente.lugar_expedicion',
                'cliente.imagen',
                'cliente.sexo',
                'cliente.estado_civil',
                'cliente.vivienda',
                'cliente.ingreso_mensual',
                'cliente.actividad',
                'plan_pago.desembolso',
                'plan_pago.pago_administrativo',
                'plan_pago.fecha_ultima_amortizacion'
            )
            ->where('plan_pago.desembolso', '=', 0); // se muestran solo los que tienen montos desembolsados

        // --- SEGURIDAD: Lista blanca para el buscador ---
        // Define aquí exactamente qué columnas permites que el usuario use para buscar
        $criteriosPermitidos = ['plan_pago.id', 'cliente.nombre', 'cliente.ci', 'solicitud.id'];
        
        if (in_array($request->criterio, $criteriosPermitidos) && !empty($request->buscar)) {
            $planes_pago->where($request->criterio, 'like', '%' . $request->buscar . '%');
        }

        // Filtro por fechas
        if ($request->fecha_inicio && $request->fecha_fin) {
            $planes_pago->whereBetween('plan_pago.fecha_registro', [ // Cambia a created_at si fecha_registro no existe
                $request->fecha_inicio, 
                $request->fecha_fin
            ]);
        }

        // Filtros de estado del crédito
        if ($request->estado_credito == 'vigentes') {
            $planes_pago->where('plan_pago.estado', 1)
                        ->whereDate('plan_pago.fecha_fin', '>=', $fechaActual);
        } elseif ($request->estado_credito == 'vencidos') {
            $planes_pago->where('plan_pago.estado', 1)
                        ->whereDate('plan_pago.fecha_fin', '<', $fechaActual);
        }

        // Filtro de roles (Si no es admin, solo ve los suyos)
        if (\App\Services\CarteraAccess::restricted()) {
            $planes_pago->where('users.id', Auth::id());
        }

        // Filtro por asesor específico
        if ($request->opcion_asesor != 0) {
            $planes_pago->where('users.id', $request->opcion_asesor);
        }

        return $planes_pago->orderBy('plan_pago.id', 'desc')->get();
    }

    public function getPlanesPagoCaja(Request $request)
    {
        $request->validate([
            'criterio' => 'nullable|in:plan_pago.id,cliente.nombre,cliente.ci',
            'fecha_inicio' => 'nullable|date_format:Y-m-d',
            'fecha_fin' => 'nullable|date_format:Y-m-d',
        ]);
        $fechaActual = Carbon::now()->format('Y-m-d');

        $planes_pago = \App\Services\CarteraAccess::query('plan_pago')
            ->join('solicitud', 'solicitud.id', '=', 'plan_pago.id_solicitud')
            ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
            ->join('users', 'users.id', '=', 'solicitud.id_usuario')
            ->select(
                'solicitud.id as id_solicitud',
                'solicitud.importe_solicitud',
                'solicitud.moneda',
                'solicitud.lapso_capital',
                'solicitud.nro_cuotas',
                'solicitud.tasa',
                'solicitud.tipo_tasa',
                'solicitud.fecha_desembolso',
                'solicitud.fecha_primera_cuota',
                'solicitud.destino_prestamo',
                'solicitud.tipo_garantia',
                'solicitud.tipo_desembolso',
                'solicitud.id_cliente',
                'solicitud.id_usuario',
                'cliente.nombre as cliente',
                'users.personal as asesor',
                'solicitud.estado',
                'plan_pago.fecha_inicio as fecha_inicio_plan',
                'plan_pago.fecha_fin as fecha_fin_plan',
                'plan_pago.total_pagar as total_pagar_plan',
                'plan_pago.estado as estado_plan',
                'plan_pago.id',
                'cliente.ci',
                'cliente.lugar_expedicion',
                'cliente.imagen',
                'cliente.vivienda',
                'cliente.actividad',
                'plan_pago.desembolso',
                'plan_pago.pago_administrativo',
                'plan_pago.fecha_ultima_amortizacion',
            )
            ->where('plan_pago.desembolso', 0)
            ->where('plan_pago.estado', 1)
            ->where($request->input('criterio') ?: 'cliente.nombre', 'like', '%'.$request->buscar.'%');

        // Filtro por fechas
        if ($request->filled('fecha_inicio')) $planes_pago->whereDate('plan_pago.fecha_inicio', '>=', $request->fecha_inicio);
        if ($request->filled('fecha_fin')) $planes_pago->whereDate('plan_pago.fecha_inicio', '<=', $request->fecha_fin);

        // Filtros existentes (asesor y estado)
        if ($request->estado_credito == 'vigentes') {
            $planes_pago->where('plan_pago.estado', 1)
                    ->whereDate('plan_pago.fecha_fin', '>=', $fechaActual);
        } elseif ($request->estado_credito == 'vencidos') {
            $planes_pago->where('plan_pago.estado', 1)
                    ->whereDate('plan_pago.fecha_fin', '<', $fechaActual);
        }

        if ($request->opcion_asesor != 0) {
            $planes_pago->where('users.id', $request->opcion_asesor);
        }

        return $planes_pago->orderBy('plan_pago.id', 'desc')->get();
    }

    public function getCuotasPlan(Request $request){
        $cuotas = \App\Services\CarteraAccess::query('cuota')
        ->join('plan_pago', 'cuota.id_plan_pago', '=', 'plan_pago.id')
        // ->select('cuota.*', DB::raw('DATEDIFF(NOW(), cuota.fecha) as dias_pasados'))
        ->select('cuota.*', DB::raw('CASE WHEN cuota.estado = 1 THEN DATEDIFF(NOW(), cuota.fecha) ELSE 0 END as dias_pasados'))
        // ->where('cuota.amortizado', 0)
        ->where('plan_pago.id', $request->id_plan_pago)
        ->get();

        $nuevoArrayConsulta = [];

        // return $nuevoArrayConsulta;
        return $cuotas;
    }

    public function anularPlanPago(Request $request)
    {
        return app(\App\Services\Finance\CancelPlan::class)->cancel($request);
    }

    public function consultarPlanPagoCuotasCanceladas(Request $request){
        $existen_cuotas_canceladas = \App\Services\CarteraAccess::query('plan_pago')
        ->join('cuota', 'cuota.id_plan_pago', '=', 'plan_pago.id')
        ->where('plan_pago.id', $request->id_planpago)
        ->where('cuota.estado', 2)
        ->count();
        //dd($existen_cuotas_canceladas);
        if($existen_cuotas_canceladas>0){
            return ['respuesta'=> 1];
        }else{
            return ['respuesta'=> 0];
        }
    }

    public function ListaCuotasPlanPagoPdf(Request $request)
    {
        // 1. Información General
        $informacion = \App\Services\CarteraAccess::query('solicitud')
            ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
            ->join('users', 'users.id', '=', 'solicitud.id_usuario')
            ->join('plan_pago', 'plan_pago.id_solicitud', '=', 'solicitud.id')
            ->where('plan_pago.id', $request->id_planpago)
            ->select('solicitud.id','solicitud.importe_solicitud', 'solicitud.moneda', 'solicitud.lapso_capital', 'solicitud.nro_cuotas',
            'solicitud.tasa', 'solicitud.fecha_desembolso', 'solicitud.fecha_primera_cuota', 'solicitud.destino_prestamo',
            'solicitud.tipo_garantia','solicitud.tipo_desembolso','solicitud.id_cliente', 'solicitud.id_usuario', 'cliente.nombre as cliente',
            'cliente.ci', 'cliente.lugar_expedicion',
            'users.name as nombre_asesor', 'plan_pago.estado', 'solicitud.fecha')
            ->first();

        // 2. MAGIA: Reutilizamos tu método maestro para obtener los cálculos exactos
        $reqCalc = new \Illuminate\Http\Request();
        $reqCalc->replace(['id_plan_pago' => $request->id_planpago]);
        $calculo = $this->listarAmortizaciones($reqCalc);
        $cuotas = $calculo['cuotas'];

        // 3. Extraemos el historial de pagos para cada cuota
        foreach($cuotas as $cuota) {
            $pagos = \App\Services\CarteraAccess::query('pago')
                ->where('id_cuota', $cuota->id)
                ->where('estado', 1) // Solo pagos activos
                ->orderBy('fecha_pago', 'asc')
                ->get();
            
            $cuota->historial_pagos = $pagos;
        }

        // 4. Carga la vista HTML para el reporte
        $data  = [
            'informacion' => $informacion,
            'detalles' => $cuotas,
            'usuario' => auth()->user()->name ?? 'Sistema',
            'fecha_reporte' => now()->format('d/m/Y'),
            'hora_reporte' => now()->format('H:i'),
        ];

        return $this->generatePDF($data, 'reporte.reporte_cuotas_planpago_nuevo', 'reporte_plan_pago');
    }

    public function activarPlanPago(Request $request)
    {
        return DB::transaction(function () use ($request) {
            \App\Services\Finance\FinancialLock::acquire();
            abort_if(DB::table('reprogramaciones_financieras')->where('id_plan_origen', $request->id_planpago)->whereIn('estado', ['pendiente', 'aprobada'])->exists(), 422, 'El crédito tiene una reprogramación vigente.');
            \App\Services\CarteraAccess::assertRecord('plan_pago', $request->id_planpago);
            $plan = DB::table('plan_pago')->where('id', $request->id_planpago)->lockForUpdate()->first();
            abort_unless($plan && (int) $plan->estado === 0, 422, 'Solo se puede activar un plan anulado.');
            $quotas = DB::table('cuota')->where('id_plan_pago', $plan->id)->pluck('id');
            abort_if(DB::table('pago')->whereIn('id_cuota', $quotas)->where('estado', 1)->exists(), 422, 'El plan conserva pagos activos y requiere revisión.');
            abort_if(DB::table('desembolso')->where('id_plan_pago', $plan->id)->where('estado', 0)->exists(), 422, 'El plan conserva un desembolso activo.');
            DB::table('plan_pago')->where('id', $plan->id)->update(['estado' => 1]);
            DB::table('solicitud')->where('id', $plan->id_solicitud)->update(['estado' => 2]);
            DB::table('cuota')->whereIn('id', $quotas)->update(['estado' => 1]);
            return ['success' => true];
        });
    }

    public function saveAmortizacionNuevo(Request $request)
    {
        return response()->json(['message' => 'Este flujo fue reemplazado. Registre adelantos y liquidaciones desde Caja > Cobros.', 'redirect' => '/caja'], 410);
    }

    public function saveAmortizacion(Request $request)
    {
        return response()->json(['message' => 'Este flujo fue reemplazado. Registre adelantos y liquidaciones desde Caja > Cobros.', 'redirect' => '/caja'], 410);
    }

    public function liquidarDeuda(Request $request)
    {
        return response()->json(['message' => 'Este flujo fue reemplazado. Registre adelantos y liquidaciones desde Caja > Cobros.', 'redirect' => '/caja'], 410);
    }

    public function generarContrato(Request $request){
        // $informacion=\App\Services\CarteraAccess::query('solicitud')
        // ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
        // ->join('users', 'users.id', '=', 'solicitud.id_usuario')
        // ->join('plan_pago', 'plan_pago.id_solicitud', '=', 'solicitud.id')
        // ->join('cuota', 'cuota.id_plan_pago', '=', 'plan_pago.id')
        // ->where('plan_pago.id', $request->id_planpago)
        // ->select('solicitud.id','solicitud.importe_solicitud', 'solicitud.moneda', 'solicitud.lapso_capital', 'solicitud.nro_cuotas',
        // 'solicitud.tasa', 'solicitud.fecha_desembolso', 'solicitud.fecha_primera_cuota', 'solicitud.destino_prestamo',
        // 'solicitud.tipo_garantia','solicitud.tipo_desembolso','solicitud.id_cliente', 'solicitud.id_usuario', 'cliente.nombre as cliente',
        // 'cliente.ci', 'cliente.lugar_expedicion',
        // 'users.name as nombre_asesor', 'plan_pago.estado')
        // ->get();

        $codeudor=DB::table('codeudor')
        ->join('solicitud_codeudor', 'solicitud_codeudor.id_codeudor', '=', 'codeudor.id')
        ->join('solicitud', 'solicitud_codeudor.id_solicitud', '=', 'solicitud.id')
        ->select('codeudor.*')
        ->where('solicitud.id', $request->id_solicitud)
        ->get();

      

       

        //****** */

         // Crea una instancia de Dompdf
         $dompdf = new Dompdf();

         // Opciones de configuración de Dompdf
         $options = new Options(config('dompdf.options', []));
         $options->set('isHtml5ParserEnabled', true); // Habilita el parser HTML5
         $options->set('isPhpEnabled', false);
         $dompdf->setOptions($options);
 
         // Carga la vista HTML para el reporte
         $html = view('reporte.reporte_contrato', [
             //'informacion' => $informacion,
             'cliente' => $request->nombre_cliente,
             'ci' => $request->ci,
             'lugar_expedicion' => $request->lugar_expedicion,
             'monto_total' => $request->monto_total,
             'nro_cuotas' => $request->nro_cuotas,
             'lapso_capital' => $request->lapso_capital,
             'fecha_inicio' => $request->fecha_inicio,
             'codeudores' => $codeudor,
             
         ])->render();
 
         // Carga el contenido HTML en Dompdf
         $dompdf->loadHtml($html);
 
         // Renderiza el PDF (esto puede tomar un tiempo si el contenido es grande)
         $dompdf->render();
 
         // Obtén el contenido del PDF como una cadena
         
 
         // Establece las cabeceras para mostrar el PDF en una nueva pestaña
         return response($dompdf->output())
 
         ->header('Content-Type', 'application/pdf')
         ->header('Content-Disposition', 'inline; filename="contrato.pdf"');
    }

    public function getCantidadCreditos(Request $request){
        $cantidad_creditos = \App\Services\CarteraAccess::query('plan_pago')
        ->join('solicitud', 'solicitud.id', '=', 'plan_pago.id_solicitud')
        ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
        ->join('users', 'users.id', '=', 'solicitud.id_usuario')
        ->join('rol', 'rol.id', '=', 'users.id_rol')
        ->select('users.personal',DB::raw('count(plan_pago.id) as cantidad_creditos'))
        ->where('rol.nombre', 'asesor')
        ->groupBy('users.personal')
        // ->orderBy('plan_pago.id', 'desc')
        ->get();

        return $cantidad_creditos;
    }

    public function getPlanesPagoLigados(Request $request){

        $planes_pago = \App\Services\CarteraAccess::query('plan_pago')
        ->join('solicitud', 'solicitud.id', '=', 'plan_pago.id_solicitud')
        ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
        ->join('users', 'users.id', '=', 'solicitud.id_usuario')
        ->select(
            'solicitud.id as id_solicitud',
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
            'users.personal as asesor',
            'solicitud.estado',
            'plan_pago.fecha_inicio as fecha_inicio_plan',
            'plan_pago.fecha_fin as fecha_fin_plan',
            'plan_pago.total_pagar as total_pagar_plan',
            'plan_pago.estado as estado_plan',
            'plan_pago.id',
            'cliente.ci',
            'cliente.lugar_expedicion',
            'plan_pago.desembolso',
            'plan_pago.pago_administrativo'
        )
        ->where('plan_pago.id_plan_aux', $request->id_plan_pago)
        ->orderBy('plan_pago.id', 'desc')
        ->get();

        return $planes_pago;

    }

    private function generatePDF($data, $url_vista, $nombre_reporte)
    {
        $mpdf = new Mpdf();

        $html = view($url_vista, $data)->render();

        $mpdf->WriteHTML($html);
        return response($mpdf->Output($nombre_reporte, 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="'.$nombre_reporte.'"');
    }

    public function getAmortizacionesPlan(Request $request){
        $amortizaciones=\App\Services\CarteraAccess::query('pago_amortizacion')
        ->join('plan_pago', 'plan_pago.id', '=', 'pago_amortizacion.id_plan_pago')
        ->select('pago_amortizacion.id', 'pago_amortizacion.monto_pago', 'pago_amortizacion.interes_pagado', 'pago_amortizacion.capital_pagado', 'pago_amortizacion.multa_pagada', 'pago_amortizacion.saldo_pendiente',
        'pago_amortizacion.total_seguro', 'pago_amortizacion.forma_pago', 'pago_amortizacion.id_caja', 'pago_amortizacion.id_plan_pago', 'pago_amortizacion.fecha')
        ->where('plan_pago.id', $request->id_plan_pago)
        ->get();

        return $amortizaciones;
    }

    public function getAmortizaciones(Request $request){
        // fecha
        $amortizaciones=\App\Services\CarteraAccess::query('pago_amortizacion')
        ->join('plan_pago', 'plan_pago.id', '=', 'pago_amortizacion.id_plan_pago')
        ->select('pago_amortizacion.id', 'pago_amortizacion.monto_pago', 'pago_amortizacion.interes_pagado', 'pago_amortizacion.capital_pagado', 'pago_amortizacion.multa_pagada', 'pago_amortizacion.saldo_pendiente',
        'pago_amortizacion.total_seguro', 'pago_amortizacion.forma_pago', 'pago_amortizacion.id_caja', 'pago_amortizacion.id_plan_pago', 'pago_amortizacion.fecha')
        ->where('plan_pago.id', $request->id_plan_pago)
        ->get();

        return $amortizaciones;
    }


    public function listarAmortizacionesPlanPago(Request $request)
    {
        // Reutilizamos el método que ya tiene toda la matemática perfecta
        $resultado = $this->listarAmortizaciones($request);
        
        // El frontend antiguo esperaba un arreglo directo de cuotas.
        // Como el nuevo método devuelve un arreglo ['cuotas' => ..., 'dias_pasados_mora' => ...],
        // devolvemos solo la parte de las cuotas para no romper el componente DetallesPlanPago.
        return response()->json($resultado['cuotas']);
    }

    public function listarAmortizaciones(Request $request)
    {
        $request->validate(['fecha_corte' => 'nullable|date_format:Y-m-d']);
        // Toda la matemática vive en el trait CalculaAmortizaciones para que
        // este endpoint y el reporte de créditos en mora usen la misma lógica.
        return $this->calcularAmortizacionesCuotas($request->input('id_plan_pago'));
    }
    
    public function registrarReprogramacion(Request $request)
    {
        // Validaciones (incluyendo el ID original)
        $request->validate([
            'plan_pago' => 'required|array',
            'plan_pago.id_cliente' => 'required|integer|exists:cliente,id',
            'plan_pago.id_solicitud' => 'required|integer|exists:solicitud,id', // ID Original
            'plan_pago.forma_pago_reprogramacion' => 'required|string',
            'plan_pago.numero_cuotas_reprogramacion' => 'required|integer',
            'cuotas' => 'required|array|min:1',
        ]);

        $plan_pago = $request->input('plan_pago');
        $cuotas = $request->input('cuotas');

        DB::beginTransaction();
        try {

            // 1. Obtener la solicitud original para leer sus contadores
            $solicitudOriginal = Solicitud::find($plan_pago['id_solicitud']);
            if (!$solicitudOriginal) {
                throw new \Exception('La solicitud original no fue encontrada.');
            }

            // 2. Definir fechas (basado en la simulación)
            $fechaCalculo = Carbon::today();
            $fechaPrimeraCuota = Carbon::parse($cuotas[0]['fecha']);

            // 3. Crear la NUEVA Solicitud de Reprogramación
            $nuevaSolicitud = Solicitud::create([
                
                // --- Vínculos y Auditoría ---
                'id_cliente' => $plan_pago['id_cliente'],
                'id_usuario' => Auth::id(),
                'id_solicitud_origen' => $solicitudOriginal->id, // El "eslabón perdido"
                
                // --- Tipo de Solicitud y Contadores ---
                'tipo_solicitud' => 'Reprogramacion',
                'cantidad_reprogramaciones' => $solicitudOriginal->cantidad_reprogramaciones + 1,
                'cantidad_refinanciamientos' => $solicitudOriginal->cantidad_refinanciamientos, // Mantiene el de la original
                
                // --- Datos del Préstamo (Originales) ---
                'importe_solicitud' => $plan_pago['importe_solicitud'],
                'moneda' => $plan_pago['moneda'],
                'tasa' => (float)$plan_pago['tasa'], // Tu tabla acepta decimal(11,2)
                'destino_prestamo' => $plan_pago['destino_prestamo'],
                'tipo_garantia' => $plan_pago['tipo_garantia'],
                'tipo_desembolso' => $plan_pago['tipo_desembolso'],
                'tipo_tasa' => $plan_pago['tipo_tasa'],
                
                // --- Datos de la REPROGRAMACIÓN ---
                'lapso_capital' => $plan_pago['forma_pago_reprogramacion'],
                'nro_cuotas' => $plan_pago['numero_cuotas_reprogramacion'],
                
                // --- Fechas y Estado ---
                'fecha' => $fechaCalculo,
                'fecha_desembolso' => $fechaCalculo,
                'fecha_primera_cuota' => $fechaPrimeraCuota,
                'estado' => 1, // 1 = Nuevo (En espera de aprobación)
            ]);
            
            // ... (Opcional: Lógica para guardar las cuotas) ...

            DB::commit();

            return response()->json([
                'message' => 'Solicitud de reprogramación registrada con éxito (ID: ' . $nuevaSolicitud->id . ')',
                'solicitud_id' => $nuevaSolicitud->id
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al registrar la solicitud: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getPagosPorCuota(Request $request)
    {
        $id_cuota = $request->input('id_cuota');
        
        $pagos = \App\Services\CarteraAccess::query('pago')
            ->leftJoin('users', 'pago.id_usuario', '=', 'users.id')
            ->where('pago.id_cuota', $id_cuota)
            ->where('pago.estado', 1)
            ->select('pago.*', 'users.name as nombre_usuario')
            ->orderBy('pago.fecha_pago', 'desc')
            ->orderBy('pago.id', 'desc')
            ->get();
            
        \App\Services\Finance\InterestBreakdown::attach($pagos);
        return response()->json($pagos);
    }
}
