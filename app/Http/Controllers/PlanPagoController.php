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


class PlanPagoController extends Controller
{
    //
    public function index(){ 
        return view('frmPlanPago');
    }

  
    public function getPlanesPago(Request $request)
    {
        $fechaActual = Carbon::now()->format('Y-m-d');

        $planes_pago = DB::table('plan_pago')
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
        $criteriosPermitidos = ['cliente.nombre', 'cliente.ci', 'solicitud.id']; 
        
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
        if (Auth::user()->id_rol != 1) {
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
        $fechaActual = Carbon::now()->format('Y-m-d');

        $planes_pago = DB::table('plan_pago')
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
                'plan_pago.pago_administrativo',
                'plan_pago.fecha_ultima_amortizacion',
            )
            ->where('plan_pago.desembolso', 0)
            ->where($request->criterio, 'like', '%'.$request->buscar.'%');

        // Filtro por fechas
        if ($request->fecha_inicio && $request->fecha_fin) {
            $planes_pago->whereBetween('plan_pago.fecha_inicio', [
                $request->fecha_inicio, 
                $request->fecha_fin
            ]);
        }

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
        $cuotas = DB::table('cuota')
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

    public function anularPlanPago(Request $request){
        DB::beginTransaction();
        try{
            DB::commit();
        }catch(Exception $e){
            DB::rollback();
        }

        DB::table('plan_pago')->where('id', $request->id_planpago)
        ->update([
            'estado'=>0
            //'solicitud.desembolso'=>0,/// 1->sin desembolsar; 0->desembolsado
        ]);

        // anulamos tambien la solicitud
        DB::table('solicitud')
        ->join('plan_pago', 'plan_pago.id_solicitud', '=', 'solicitud.id')
        ->where('plan_pago.id', $request->id_planpago)
        ->update([
            'solicitud.estado'=>0,
            //'solicitud.desembolso'=>0,/// 0->sin desembolsar; 1->desembolsado
        ]);


        // we are cancelling the disbursement of the request - payment plan -> Anulamos desembolso de la solicitud - Plan pago
        DB::table('desembolso')
        ->where('id_plan_pago', $request->id_planpago)
        ->update([
            'estado'=>1
        ]);

        // we record the income -> Registramos el ingreso
        $id_caja=DB::table('caja')->where('estado', 1)->get()[0]->id;

        $info_plan_pago = DB::table('plan_pago')->where('id', $request->id_planpago)->first();

        DB::table('ingreso')->insert([
            'monto'=>$info_plan_pago->total_pagar,
            'descripcion'=>'Ingreso por concepto de anulación de plan de pago. Devolucón del monto desembolsado del plan de pago #'. $info_plan_pago->id,
            'id_usuario'=>Auth::id(),
            'fecha'=>now(),
            'id_caja'=>$id_caja
        ]);
        
        // Reg. en mov. caja
        DB::table('movimientos_caja')
        ->insertGetId([
            'tipo_movimiento'=>'ingreso',
            'monto'=>$info_plan_pago->total_pagar,
            'descripcion'=>'Ingreso por concepto de anulación de plan de pago. Devolucón del monto desembolsado del plan de pago #'. $info_plan_pago->id,
            'fecha'=>now(),
            'id_caja'=>$id_caja,
            'id_usuario'=>Auth::user()->id,
        ]);


        // anulamos las cuotas
        DB::table('plan_pago')
        ->join('cuota', 'plan_pago.id', '=', 'cuota.id_plan_pago')
        ->where('cuota.id_plan_pago', $request->id_planpago)
        ->update([
            'cuota.estado'=>0
            
        ]);

    }

    public function consultarPlanPagoCuotasCanceladas(Request $request){
        $existen_cuotas_canceladas = DB::table('plan_pago')
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
        $informacion = DB::table('solicitud')
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
            $pagos = DB::table('pago')
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

    public function activarPlanPago(Request $request){
        DB::table('plan_pago')->where('id', $request->id_planpago)
        ->update([
            'estado'=>1
        ]);
        // aprobamos tambien la solicitud

        DB::table('solicitud')
        ->join('plan_pago', 'plan_pago.id_solicitud', '=', 'solicitud.id')
        ->where('plan_pago.id', $request->id_planpago)
        ->update([
            'solicitud.estado'=>2
        ]);

        // anulamos las cuotas

        DB::table('plan_pago')
        ->join('cuota', 'plan_pago.id', '=', 'cuota.id_plan_pago')
        ->where('cuota.id_plan_pago', $request->id_planpago)
        ->update([
            'cuota.estado'=>1
        ]);
    }

    public function saveAmortizacionNuevo(Request $request){
        DB::beginTransaction();
        //dd($request);
        try{

            $detalles = json_decode($request->detalles, true);// lista cuotas
            $monto_multa=empty($request->monto_multa)?0:$request->monto_multa;// monto de multa total
            $monto_interes=empty($request->monto_interes)?0:$request->monto_interes;// Monto solo interes acumulado
            $monto_capital=empty($request->monto_capital)?0:$request->monto_capital;// monto capital, se descontara de saldo_pendiente
            $id_plan_pago=$request->id_plan_pago;
            $id_caja=DB::table('caja')->where('estado', 1)->get()[0]->id;

            /*aqui se debera cambiar el estado de las cuotas que aun no se han pagado
            las cuotas por pagar se ocultan*/
            
            $cuotas = DB::table('cuota')
                ->where('id_plan_pago', $id_plan_pago)
                ->where('estado', 1)
                ->where('amortizado', 0)
                ->get();


            DB::table('cuota')->where('id_plan_pago', $id_plan_pago)
                ->where('cuota.estado', 1)
                ->update([
                    'amortizado'=>1,
                ]);

            $id_primer_cuota_sin_pagar=0;

            if ($cuotas->isEmpty()) {
                // Manejar el caso donde no hay cuotas pendientes
            } else {
                $id_primer_cuota_sin_pagar = $cuotas[0]->id;
            }

            // se crean las cuotas
            $numero_cuota_inicio=$request->numero_cuota_inicio;
            $numero_cuota_inicio_aux=$numero_cuota_inicio;
            $id_cuota_pago_amortizacion=0;

            foreach($detalles as $detalle){
                $id_cuota_insertada=DB::table('cuota')->insertGetId([
                    'numero'=>$numero_cuota_inicio,
                    'fecha'=>$detalle['fecha'],
                    'capital'=>$detalle['capital'],
                    'interes'=>$detalle['interes'],
                    'saldo_capital'=>$detalle['saldo_capital'],
                    'ahorro'=>empty($detalle['ahorro'])?0:$detalle['ahorro'],
                    'seguro'=>empty($detalle['seguro'])?0:$detalle['seguro'],
                    'total'=>$detalle['total_cuota'],
                    'id_plan_pago'=>$id_plan_pago,

                ]);

                if($numero_cuota_inicio_aux==$numero_cuota_inicio){
                    $id_cuota_pago_amortizacion=$id_cuota_insertada;
                }
                $numero_cuota_inicio=$numero_cuota_inicio+1;
            }

            // actualizando id_cuota para las amortizaciones
            DB::table('pago_amortizacion')
            ->where('pago_amortizacion.id_cuota', $id_primer_cuota_sin_pagar)
            ->update(['id_cuota' => $id_cuota_pago_amortizacion]);

            
            // pago amortizacion tiene que estar ligado a la ultima cuota pagados, para que aparezca en el listado
            DB::table('pago_amortizacion')->insert([
                'fecha'=> now(),
                'monto_pago'=> $monto_multa + $monto_interes + $monto_capital,
                'capital_pagado'=>$monto_capital,
                'interes_pagado'=>$monto_interes,
                'multa_pagada'=>$monto_multa,
                'saldo_pendiente'=>$request->saldo_pendiente,
                'forma_pago'=>$request->forma_pago,
                'id_caja'=>$id_caja,
                'id_plan_pago'=>$id_plan_pago,// plan de pago del credito
                'saldo_pendiente'=>$request->saldo_pendiente,
                'id_cuota'=>$id_cuota_pago_amortizacion,
                'total_seguro'=>$request->total_seguro,
            ]);

            DB::table('plan_pago')->where('plan_pago.id', $id_plan_pago)->update([
                'fecha_ultima_amortizacion'=>now(),
            ]);

            DB::commit();
       
        }catch(Exception $e){
            DB::rollback();
        }
    }

    public function saveAmortizacion(Request $request){
        DB::beginTransaction();
        try{
            //id_cuota
            $detalles = json_decode($request->detalles, true);
            $monto_multa=empty($request->monto_multa)?0:$request->monto_multa;
            $monto_interes=empty($request->monto_interes)?0:$request->monto_interes;
            $monto_capital=empty($request->monto_capital)?0:$request->monto_capital;
            $id_caja=DB::table('caja')->where('estado', 1)->get()[0]->id;

            // para id_plan_aux

            $plan_pago_aux=DB::table('plan_pago')
            ->where('plan_pago.id_plan_aux', $request->id_plan_pago)// tiene que ser el principal
            ->where('plan_pago.estado', 0)
            ->get();

            $cantidad_planes_aux=$plan_pago_aux->count();

            $id_plan_pago_amortizacion=($cantidad_planes_aux>0)?$plan_pago_aux[0]->id:0;

            $id_plan_pago_definitivo=($cantidad_planes_aux>0)?$id_plan_pago_amortizacion:$request->id_plan_pago;// tiene que ser el principal





            // cambiamos el valor de amortizado para las cuotas anteriores al plan de pago
            // de aquellas que no han sido pagadas
            // las cuotas deben pertenecer al nuevo plan de pago
            DB::table('cuota')->where('id_plan_pago', $id_plan_pago_definitivo)
            ->where('cuota.estado', 1)
            ->update([
                'amortizado'=>1,
            ]);



            // id_cuota ultima pagada
            $ultima_cuota=DB::table('cuota')
            ->select(DB::raw('max(cuota.id) as ultima_pagada'))
            ->where('id_plan_pago', $id_plan_pago_definitivo)
            ->where('cuota.estado', 2)
            ->first();


            $id_cuota_ult_pagada=($ultima_cuota && $ultima_cuota->ultima_pagada)?$ultima_cuota->ultima_pagada:0;
           
                // pago amortizacion tiene que estar ligado al plan de pago nuevo
            DB::table('pago_amortizacion')->insert([
                // 'fecha'=> Carbon::now()->toDateString(),
                'fecha'=> now(),
                'monto_pago'=> $monto_multa + $monto_interes + $monto_capital,
                'capital_pagado'=>$monto_capital,
                'interes_pagado'=>$monto_interes,
                'multa_pagada'=>$monto_multa,
                'saldo_pendiente'=>$request->saldo_pendiente,
                'forma_pago'=>$request->forma_pago,
                'id_caja'=>$id_caja,

                'id_plan_pago'=>$id_plan_pago_definitivo,
                'saldo_pendiente'=>$request->saldo_pendiente,
                // 'id_cuota'=>$id_cuota_ult_pagada,
                'id_cuota'=>$request->id_cuota,
                'total_seguro'=>$request->total_seguro,


            ]);

            $numero_cuota_inicio=$request->numero_cuota_inicio;

            // INFORMACION PLAN PAGO
            $plan_pago_info=DB::table('plan_pago')
            ->where('id', $id_plan_pago_definitivo)
            ->first();

            // INFORMACION TOTAL PAGADO | CANT CUOTAS
            $plan_pago_info_pagado=DB::table('plan_pago')
            ->join('cuota', 'cuota.id_plan_pago', '=', 'plan_pago.id')
            ->where('cuota.id_plan_pago', $id_plan_pago_definitivo)
            ->select(DB::raw('sum(cuota.total) as total_cuotas_plan'))
            ->first();

            // INFORMACION SOLICITUD LIGADA
            $solicitud_ligada=DB::table('solicitud')
            ->where('id', $plan_pago_info->id_solicitud)
            ->first();

            // CREAR NUEVA SOLICITUD INTERNA 
            $id_solicitud=DB::table('solicitud')->insertGetId([
                'importe_solicitud'=>$request->saldo_pendiente,
                'moneda'=>$solicitud_ligada->moneda,
                'lapso_capital'=>$request->lapso_capital,
                'nro_cuotas'=>$request->nro_cuotas,
                'tasa'=>$request->tasa,
                'fecha_desembolso'=>now(),
                'fecha'=>now(),
                'fecha_primera_cuota'=>$request->fecha_primera_cuota,
                'destino_prestamo'=>'Amortización del credito',
                'monto_pago_adm'=>0,
                'tipo_garantia'=>$solicitud_ligada->tipo_garantia,
                'tipo_desembolso'=>$request->tipo_desembolso,
                'id_cliente'=>$solicitud_ligada->id_cliente,
                'id_usuario'=>Auth::id(),
                'estado'=>10,

            ]);

            // CREAR PLAN PAGO INTERNO

            // Obtener el último elemento del array
            $ultimo_detalle = end($detalles);
            // Acceder a la propiedad 'fecha' del último elemento
            $fecha_ultima_cuota = $ultimo_detalle['fecha'];



            $plan_pagoId = DB::table('plan_pago')->insertGetId([
                //'fecha_inicio'=>$request->fecha_inicio_plan_pago,
                'fecha_inicio'=>now(),
                'fecha_fin'=>$fecha_ultima_cuota,
                'total_pagar'=>$request->saldo_pendiente,
                'id_solicitud'=>$id_solicitud,

                // 'id_plan_aux'=>$request->id_plan_pago,// aqui debe estar ligado al plan de pago principal
                'id_plan_aux'=>$request->id_plan_pago_principal,// aqui debe estar ligado al plan de pago principal
            ]);



            // CREAR CUOTAS
            $numero_cuota_inicio=1;
            foreach($detalles as $detalle){
                DB::table('cuota')->insertGetId([
                    'numero'=>$numero_cuota_inicio,
                    'fecha'=>$detalle['fecha'],
                    'capital'=>$detalle['capital'],
                    'interes'=>$detalle['interes'],
                    'saldo_capital'=>$detalle['saldo_capital'],
                    'ahorro'=>empty($detalle['ahorro'])?0:$detalle['ahorro'],
                    'seguro'=>empty($detalle['seguro'])?0:$detalle['seguro'],
                    'total'=>$detalle['total_cuota'],
                    'id_plan_pago'=>$plan_pagoId,

                ]);
                $numero_cuota_inicio=$numero_cuota_inicio+1;
            }


            // ACTUALIZAMOS PLAN PAGO | ESTADO | MONTO 

            DB::table('plan_pago')
            ->where('id', $id_plan_pago_definitivo)
            ->update([
                'estado'=>10,
                //'total_pagar'=>$plan_pago_info_pagado->total_cuotas_plan,
            ]);

            // ACTUALIZAMOS ESTADO DE LA SOLICITUD



            // DB::table('solicitud')
            // ->where('id', $plan_pago_info->id_solicitud)
            // ->update([
            //     'estado'=>10,
            // ]);

        
            // actualizamos estado de plan de pago
            

        
            DB::commit();
            // return [
            //     'nro_cuotas'=> $solicitud->nro_cuotas, 
            //     'tipo_desembolso'=>$solicitud->tipo_desembolso,
            //     'fecha_fin'=>$plan_pago->fecha_fin, 
            //     'fecha_inicio_plan'=>$plan_pago->fecha_inicio
            // ];

        }catch(Exception $e){
            DB::rollback();
        }

    }

    public function liquidarDeuda(Request $request){
        DB::beginTransaction();
        try{
            
            $monto_multa=empty($request->monto_multa)?0:$request->monto_multa;
            $monto_interes=empty($request->monto_interes)?0:$request->monto_interes;
            $monto_capital=empty($request->monto_capital)?0:$request->monto_capital;
      
            $id_caja=DB::table('caja')->where('estado', 1)->get()[0]->id;


            // cambiamos el valor de amortizado para las cuotas anteriores al plan de pago
            DB::table('cuota')->where('id_plan_pago', $request->id_plan_pago)
            ->update([
                'amortizado'=>1
            ]);
            // cambiamos estados de las cuotas posteriores al pago de la amortizacion

            $cuotas = DB::table('cuota')
            ->join('plan_pago', 'cuota.id_plan_pago','=', 'plan_pago.id')
            ->select('cuota.*')
            ->where('plan_pago.id', $request->id_plan_pago)
            ->where('cuota.estado', 1)
            ->get();

            //dd($cuotas);
            foreach($cuotas as $cuota){
                DB::table('cuota')->where('id', $cuota->id)
                ->update(
                    [
                        'estado'=>3
                    ]
                );
            }
    
            DB::table('pago_amortizacion')->insert([
                'fecha'=> Carbon::now()->toDateString(),
                'monto_pago'=> $monto_multa + $monto_interes + $monto_capital,
                'capital_pagado'=>$monto_capital,
                'interes_pagado'=>$monto_interes,
                'multa_pagada'=>$monto_multa,
                'saldo_pendiente'=>$request->saldo_pendiente,
                'forma_pago'=>$request->forma_pago,
                'id_caja'=>$id_caja,
                'id_plan_pago'=>$request->id_plan_pago,
                'saldo_pendiente'=>$request->saldo_pendiente,
                'id_cuota'=>$request->id_cuota,
                'total_seguro'=>$request->total_seguro,

            ]);
            /*$numero_cuota_inicio=$request->numero_cuota_inicio;
            foreach($detalles as $detalle){
                DB::table('cuota')->insert([
                    'numero'=>$numero_cuota_inicio,
                    'fecha'=>$detalle['fecha'],
                    'capital'=>$detalle['capital'],
                    'interes'=>$detalle['interes'],
                    'saldo_capital'=>$detalle['saldo_capital'],
                    'ahorro'=>empty($detalle['ahorro'])?0:$detalle['ahorro'],
                    'seguro'=>empty($detalle['seguro'])?0:$detalle['seguro'],
                    'total'=>$detalle['total_cuota'],
                    'id_plan_pago'=>$request->id_plan_pago,

                ]);
                $numero_cuota_inicio=$numero_cuota_inicio+1;
            }*/

            // actualizar cantidad de cuotas
            /*DB::table('solicitud')->where('id',$request->id_solicitud)->update([
                'nro_cuotas'=> ($request->numero_cuota_inicio-1)+($numero_cuota_inicio-$request->numero_cuota_inicio),
                'tipo_desembolso'=> $request->tipo_desembolso,
             
                
            ]);*/

            DB::table('plan_pago')->where('id', $request->id_plan_pago)->update([
                // 'fecha_fin'=>$request->fecha_fin,
                // 'fecha_inicio'=>$request->fecha_primera_cuota,
                'estado'=>2
            ]);

            // $solicitud=DB::table('solicitud')
            // ->where('id',$request->id_solicitud)
            // ->get()[0];

            $plan_pago=DB::table('plan_pago')
            ->where('id',$request->id_plan_pago)
            ->get()[0];


            // actualizamos estado de plan de pago
            

        
            DB::commit();
            return ['estado_plan'=> $plan_pago->estado];

        }catch(Exception $e){
            DB::rollback();
        }

    }

    public function generarContrato(Request $request){
        // $informacion=DB::table('solicitud')
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
         $options = new Options();
         $options->set('isHtml5ParserEnabled', true); // Habilita el parser HTML5
         $options->set('isPhpEnabled', true); // Habilita la ejecución de código PHP en la vista (si es necesario)
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
        $cantidad_creditos = DB::table('plan_pago')
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

        $planes_pago = DB::table('plan_pago')
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
        $mpdf->Output($nombre_reporte, 'I');
    }

    public function getAmortizacionesPlan(Request $request){
        $amortizaciones=DB::table('pago_amortizacion')
        ->join('plan_pago', 'plan_pago.id', '=', 'pago_amortizacion.id_plan_pago')
        ->select('pago_amortizacion.id', 'pago_amortizacion.monto_pago', 'pago_amortizacion.interes_pagado', 'pago_amortizacion.capital_pagado', 'pago_amortizacion.multa_pagada', 'pago_amortizacion.saldo_pendiente',
        'pago_amortizacion.total_seguro', 'pago_amortizacion.forma_pago', 'pago_amortizacion.id_caja', 'pago_amortizacion.id_plan_pago', 'pago_amortizacion.fecha')
        ->where('plan_pago.id', $request->id_plan_pago)
        ->get();

        return $amortizaciones;
    }

    public function getAmortizaciones(Request $request){
        // fecha
        $amortizaciones=DB::table('pago_amortizacion')
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

    /*public function listarAmortizaciones(Request $request)
    {
        $id_plan_pago = $request->input('id_plan_pago');

        $cuotas = DB::table('cuota')
            ->join('plan_pago', 'cuota.id_plan_pago', '=', 'plan_pago.id')
            ->select(
                'cuota.*',
                'plan_pago.fecha_inicio',
                'plan_pago.lapso_capital',
                'plan_pago.tasa',
                DB::raw('(SELECT MAX(fecha_pago) FROM pago WHERE pago.id_cuota = cuota.id) as fecha_pago_real'),
                DB::raw('CASE 
                    WHEN cuota.estado IN (1, 3) AND cuota.id = (
                        SELECT MIN(id) FROM cuota 
                        WHERE id_plan_pago = ? AND estado IN (1, 3)
                    ) THEN 
                        GREATEST(0, DATEDIFF(NOW(), GREATEST(cuota.fecha, COALESCE(plan_pago.fecha_ultima_amortizacion, cuota.fecha))))
                    ELSE 0 
                END as dias_pasados')
            )
            ->where('cuota.id_plan_pago', $id_plan_pago)
            ->addBinding($id_plan_pago, 'select')
            ->orderBy('cuota.numero', 'asc')
            ->get();

        $lapso_capital = $cuotas->first()->lapso_capital ?? 'Mensual';
        $totalDiasCuota = match ($lapso_capital) {
            'Semanal' => 7,
            'Quincenal' => 15,
            'Mensual' => 30,
            default => 30,
        };

        $firstUnpaidIndex = $cuotas->search(function ($cuota) {
            return in_array($cuota->estado, [1, 3]);
        });

        $dias_pasados_mora = 0;

        $cuotas = $cuotas->map(function ($cuota, $index) use ($cuotas, $firstUnpaidIndex, $totalDiasCuota, &$dias_pasados_mora) {
            $fechaCuota = Carbon::parse($cuota->fecha);
            
            if ($cuota->estado == 2 && !empty($cuota->fecha_pago_real)) {
                $fechaCalculo = Carbon::parse($cuota->fecha_pago_real);
            } else {
                $fechaCalculo = Carbon::now();
            }
            
            $diasTranscurridosNormales = 0;
            $diasRetrasoCuota = 0; 
            
            $fechaInicioCuota = $index === 0
                ? Carbon::parse($cuotas->first()->fecha_inicio)
                : Carbon::parse($cuotas[$index - 1]->fecha);

            // 1. DÍAS TRANSCURRIDOS
            if ($index === $firstUnpaidIndex && in_array($cuota->estado, [1, 3])) {
                $diasTranscurridosNormales = ($fechaCalculo->diffInDays($fechaInicioCuota) > $totalDiasCuota) ? $totalDiasCuota : $fechaCalculo->diffInDays($fechaInicioCuota);
                $dias_pasados_mora = $cuota->dias_pasados > 0 ? $cuota->dias_pasados : 0;
            } else {
                if ($fechaCalculo->isAfter($fechaCuota)) {
                    $diasTranscurridosNormales = $totalDiasCuota;
                } elseif ($fechaCalculo->isBetween($fechaInicioCuota, $fechaCuota, true)) {
                    $diasTranscurridosNormales = $fechaCalculo->diffInDays($fechaInicioCuota);
                }
            }

            // 2. DÍAS DE RETRASO ESTRICTO
            if ($fechaCalculo->isAfter($fechaCuota)) {
                $diasRetrasoCuota = $fechaCalculo->diffInDays($fechaCuota);
            }

            // 3. VARIABLES DE PAGO ACUMULADO
            $cap_pagado = isset($cuota->capital_pagado) ? (float)$cuota->capital_pagado : 0;
            $int_pagado = isset($cuota->interes_pagado) ? (float)$cuota->interes_pagado : 0;
            $mora_pagada = isset($cuota->mora_pagada) ? (float)$cuota->mora_pagada : 0;

            // 4. CÁLCULO BRUTO HISTÓRICO (El Total Real Generado)
            $interesPorDiaNormal = $cuota->interes / $totalDiasCuota;
            $interesDevengadoBruto = $interesPorDiaNormal * $diasTranscurridosNormales;

            $capitalParaMora = in_array($cuota->estado, [1, 3]) ? max(0, $cuota->capital - $cap_pagado) : $cuota->capital;
            $interesPorDiaMora = 0;

            if ($cuota->lapso_capital == 'Mensual') {
                $baseCalculo = $cuota->saldo_capital + $capitalParaMora;
                $tasaPorcentaje = $cuota->tasa / 100;
                $interesPorDiaMora = ($baseCalculo * $tasaPorcentaje) / 30;
            } else if (in_array($cuota->lapso_capital, ['Semanal', 'Quincenal'])) {
                $interesBaseMora = ($cuota->numero == 1 && isset($cuotas[$index + 1])) ? $cuotas[$index + 1]->interes : $cuota->interes;
                $proporcionDeuda = ($cuota->capital > 0) ? ($capitalParaMora / $cuota->capital) : 1;
                $interesPorDiaMora = ($interesBaseMora * $proporcionDeuda) / $totalDiasCuota;
            } else {
                $proporcionDeuda = ($cuota->capital > 0) ? ($capitalParaMora / $cuota->capital) : 1;
                $interesPorDiaMora = ($cuota->interes * $proporcionDeuda) / $totalDiasCuota;
            }

            $interesMoratorioBruto = $interesPorDiaMora * $diasRetrasoCuota;
            $interesTotalAcumuladoBruto = $interesDevengadoBruto + $interesMoratorioBruto; // EL TOTAL GLOBAL DE INTERÉS

            // 5. CÁLCULO DE PORCENTAJES AVANZADOS
            $porcentaje_capital = ($cuota->capital > 0) ? round(($cap_pagado / $cuota->capital) * 100, 1) : 0;
            $porcentaje_interes = ($interesTotalAcumuladoBruto > 0) ? round(($int_pagado / $interesTotalAcumuladoBruto) * 100, 1) : 0;

            // 6. CÁLCULO DE RESTANTES (NETOS)
            $capitalRestante = max(0, $cuota->capital - $cap_pagado);
            $intDevengadoRestante = max(0, $interesDevengadoBruto - $int_pagado);
            $excesoInt = max(0, $int_pagado - $interesDevengadoBruto);
            $intMoratorioRestante = max(0, $interesMoratorioBruto - $excesoInt);
            $interesTotalAcumuladoRestante = $intDevengadoRestante + $intMoratorioRestante;

            $moraFijaBruta = (in_array($cuota->estado, [1, 3]) && $cuota->dias_pasados > 0) ? ($cuota->dias_pasados * 3) : 0;
            $moraFijaRestante = max(0, $moraFijaBruta - $mora_pagada);

            // 7. ASIGNACIÓN AL OBJETO (Nuevas variables enviadas a Vue)
            $cuota->dias_transcurridos = round($diasTranscurridosNormales);
            
            // Stats de pago
            $cuota->capital_pagado_total = round($cap_pagado, 2);
            $cuota->interes_pagado_total = round($int_pagado, 2);
            $cuota->porcentaje_capital_pagado = $porcentaje_capital;
            $cuota->porcentaje_interes_pagado = $porcentaje_interes;

            // Stats de Deuda Neta
            $cuota->capital_neto = round($capitalRestante, 2);
            $cuota->interes_devengado_neto = round($intDevengadoRestante, 2);
            $cuota->interes_moratorio_neto = round($intMoratorioRestante, 2);
            $cuota->interes_acumulado_neto = round($interesTotalAcumuladoRestante, 2);
            $cuota->mora_fija_neta = round($moraFijaRestante, 2);

            return $cuota;
        });

        return [
            'cuotas' => $cuotas,
            'dias_pasados_mora' => $dias_pasados_mora
        ];
    }*/
    
    /*public function listarAmortizaciones(Request $request)
    {
        $id_plan_pago = $request->input('id_plan_pago');

        $cuotas = DB::table('cuota')
            ->join('plan_pago', 'cuota.id_plan_pago', '=', 'plan_pago.id')
            ->select(
                'cuota.*',
                'plan_pago.fecha_inicio',
                'plan_pago.lapso_capital',
                'plan_pago.tasa',
                DB::raw('(SELECT MAX(fecha_pago) FROM pago WHERE pago.id_cuota = cuota.id) as fecha_pago_real'),
                DB::raw('CASE 
                    WHEN cuota.estado IN (1, 3) AND cuota.id = (
                        SELECT MIN(id) FROM cuota 
                        WHERE id_plan_pago = ? AND estado IN (1, 3)
                    ) THEN 
                        GREATEST(0, DATEDIFF(NOW(), GREATEST(cuota.fecha, COALESCE(plan_pago.fecha_ultima_amortizacion, cuota.fecha))))
                    ELSE 0 
                END as dias_pasados')
            )
            ->where('cuota.id_plan_pago', $id_plan_pago)
            ->addBinding($id_plan_pago, 'select')
            ->orderBy('cuota.numero', 'asc')
            ->get();

        // Base fija solo se mantiene como referencia para las fórmulas de mora bancaria estándar
        $lapso_capital = $cuotas->first()->lapso_capital ?? 'Mensual';
        $diasBaseMora = match ($lapso_capital) {
            'Semanal' => 7,
            'Quincenal' => 15,
            'Mensual' => 30,
            default => 30,
        };

        $firstUnpaidIndex = $cuotas->search(function ($cuota) {
            return in_array($cuota->estado, [1, 3]);
        });

        $dias_pasados_mora = 0;

        $cuotas = $cuotas->map(function ($cuota, $index) use ($cuotas, $firstUnpaidIndex, $diasBaseMora, &$dias_pasados_mora) {
            $fechaCuota = Carbon::parse($cuota->fecha);
            
            $fechaInicioCuota = $index === 0
                ? Carbon::parse($cuotas->first()->fecha_inicio)
                : Carbon::parse($cuotas[$index - 1]->fecha);

            // CORRECCIÓN CLAVE: Calculamos cuántos días reales dura exactamente ESTA cuota
            $diasPeriodoCuota = max(1, $fechaInicioCuota->diffInDays($fechaCuota));
            
            if ($cuota->estado == 2 && !empty($cuota->fecha_pago_real)) {
                $fechaCalculo = Carbon::parse($cuota->fecha_pago_real);
            } else {
                $fechaCalculo = Carbon::now();
            }
            
            $diasTranscurridosNormales = 0;
            $diasRetrasoCuota = 0; 

            // 1. DÍAS TRANSCURRIDOS (Ahora el tope es $diasPeriodoCuota, no 30)
            if ($index === $firstUnpaidIndex && in_array($cuota->estado, [1, 3])) {
                $diasTranscurridosNormales = ($fechaCalculo->diffInDays($fechaInicioCuota) > $diasPeriodoCuota) 
                    ? $diasPeriodoCuota 
                    : $fechaCalculo->diffInDays($fechaInicioCuota);
                $dias_pasados_mora = $cuota->dias_pasados > 0 ? $cuota->dias_pasados : 0;
            } else {
                if ($fechaCalculo->isAfter($fechaCuota)) {
                    $diasTranscurridosNormales = $diasPeriodoCuota;
                } elseif ($fechaCalculo->isBetween($fechaInicioCuota, $fechaCuota, true)) {
                    $diasTranscurridosNormales = $fechaCalculo->diffInDays($fechaInicioCuota);
                }
            }

            // 2. DÍAS DE RETRASO ESTRICTO
            if ($fechaCalculo->isAfter($fechaCuota)) {
                $diasRetrasoCuota = $fechaCalculo->diffInDays($fechaCuota);
            }

            // 3. VARIABLES DE PAGO ACUMULADO
            $cap_pagado = isset($cuota->capital_pagado) ? (float)$cuota->capital_pagado : 0;
            $int_pagado = isset($cuota->interes_pagado) ? (float)$cuota->interes_pagado : 0;
            $mora_pagada = isset($cuota->mora_pagada) ? (float)$cuota->mora_pagada : 0;

            // 4. CÁLCULO BRUTO HISTÓRICO
            // Ahora el interés normal se divide entre los días REALES del mes, no entre 30.
            $interesPorDiaNormal = $cuota->interes / $diasPeriodoCuota;
            $interesDevengadoBruto = $interesPorDiaNormal * $diasTranscurridosNormales;

            $capitalParaMora = in_array($cuota->estado, [1, 3]) ? max(0, $cuota->capital - $cap_pagado) : $cuota->capital;
            $interesPorDiaMora = 0;

            // La mora bancaria suele seguir usando base 30 por convención legal
            if ($cuota->lapso_capital == 'Mensual') {
                $baseCalculo = $cuota->saldo_capital + $capitalParaMora;
                $tasaPorcentaje = $cuota->tasa / 100;
                $interesPorDiaMora = ($baseCalculo * $tasaPorcentaje) / 30;
            } else if (in_array($cuota->lapso_capital, ['Semanal', 'Quincenal'])) {
                $interesBaseMora = ($cuota->numero == 1 && isset($cuotas[$index + 1])) ? $cuotas[$index + 1]->interes : $cuota->interes;
                $proporcionDeuda = ($cuota->capital > 0) ? ($capitalParaMora / $cuota->capital) : 1;
                $interesPorDiaMora = ($interesBaseMora * $proporcionDeuda) / $diasBaseMora;
            } else {
                $proporcionDeuda = ($cuota->capital > 0) ? ($capitalParaMora / $cuota->capital) : 1;
                $interesPorDiaMora = ($cuota->interes * $proporcionDeuda) / $diasBaseMora;
            }

            $interesMoratorioBruto = $interesPorDiaMora * $diasRetrasoCuota;
            $interesTotalAcumuladoBruto = $interesDevengadoBruto + $interesMoratorioBruto;

            // 5. CÁLCULO DE PORCENTAJES
            $porcentaje_capital = ($cuota->capital > 0) ? round(($cap_pagado / $cuota->capital) * 100, 1) : 0;
            $porcentaje_interes = ($interesTotalAcumuladoBruto > 0) ? round(($int_pagado / $interesTotalAcumuladoBruto) * 100, 1) : 0;

            // 6. CÁLCULO DE RESTANTES (NETOS)
            $capitalRestante = max(0, $cuota->capital - $cap_pagado);
            $intDevengadoRestante = max(0, $interesDevengadoBruto - $int_pagado);
            $excesoInt = max(0, $int_pagado - $interesDevengadoBruto);
            $intMoratorioRestante = max(0, $interesMoratorioBruto - $excesoInt);
            $interesTotalAcumuladoRestante = $intDevengadoRestante + $intMoratorioRestante;

            $moraFijaBruta = (in_array($cuota->estado, [1, 3]) && $cuota->dias_pasados > 0) ? ($cuota->dias_pasados * 3) : 0;
            $moraFijaRestante = max(0, $moraFijaBruta - $mora_pagada);

            // 7. ASIGNACIÓN AL OBJETO
            $cuota->dias_transcurridos = round($diasTranscurridosNormales); // <<-- ¡AQUÍ ESTARÁ EL ARREGLO!
            
            $cuota->capital_pagado_total = round($cap_pagado, 2);
            $cuota->interes_pagado_total = round($int_pagado, 2);
            $cuota->porcentaje_capital_pagado = $porcentaje_capital;
            $cuota->porcentaje_interes_pagado = $porcentaje_interes;

            $cuota->capital_neto = round($capitalRestante, 2);
            $cuota->interes_devengado_neto = round($intDevengadoRestante, 2);
            $cuota->interes_moratorio_neto = round($intMoratorioRestante, 2);
            $cuota->interes_acumulado_neto = round($interesTotalAcumuladoRestante, 2);
            $cuota->mora_fija_neta = round($moraFijaRestante, 2);

            return $cuota;
        });

        return [
            'cuotas' => $cuotas,
            'dias_pasados_mora' => $dias_pasados_mora
        ];
    }*/

    /*public function listarAmortizaciones(Request $request)
    {
        $id_plan_pago = $request->input('id_plan_pago');

        // 1. OBTENER CUOTAS
        $cuotas = DB::table('cuota')
            ->join('plan_pago', 'cuota.id_plan_pago', '=', 'plan_pago.id')
            ->select(
                'cuota.*',
                'plan_pago.fecha_inicio',
                'plan_pago.lapso_capital',
                'plan_pago.tasa',
                DB::raw('(SELECT MAX(fecha_pago) FROM pago WHERE pago.id_cuota = cuota.id) as fecha_pago_real'),
                // Esta subconsulta estricta solo sacará días para la PRIMERA cuota vencida.
                // Le damos el alias 'dias_mora_cobro' para aislarla del dato informativo.
                DB::raw('CASE 
                    WHEN cuota.estado IN (1, 3) AND cuota.id = (
                        SELECT MIN(id) FROM cuota 
                        WHERE id_plan_pago = ? AND estado IN (1, 3)
                    ) THEN 
                        GREATEST(0, DATEDIFF(NOW(), GREATEST(cuota.fecha, COALESCE(plan_pago.fecha_ultima_amortizacion, cuota.fecha))))
                    ELSE 0 
                END as dias_mora_cobro')
            )
            ->where('cuota.id_plan_pago', $id_plan_pago)
            ->addBinding($id_plan_pago, 'select')
            ->orderBy('cuota.numero', 'asc')
            ->get();

        $lapso_capital = $cuotas->first()->lapso_capital ?? 'Mensual';
        $diasBaseMora = match ($lapso_capital) {
            'Semanal' => 7,
            'Quincenal' => 15,
            'Mensual' => 30,
            default => 30,
        };

        $firstUnpaidIndex = $cuotas->search(function ($cuota) {
            return in_array($cuota->estado, [1, 3]);
        });

        $dias_pasados_mora = 0; // KPI global superior

        $cuotas = $cuotas->map(function ($cuota, $index) use ($cuotas, $firstUnpaidIndex, $diasBaseMora, &$dias_pasados_mora) {
            $fechaCuota = Carbon::parse($cuota->fecha);
            
            $fechaInicioCuota = $index === 0
                ? Carbon::parse($cuotas->first()->fecha_inicio)
                : Carbon::parse($cuotas[$index - 1]->fecha);

            // CORRECCIÓN MATEMÁTICA: Los días reales de duración del periodo (Ej: los 31 días)
            $diasPeriodoCuota = max(1, $fechaInicioCuota->diffInDays($fechaCuota));
            
            if ($cuota->estado == 2 && !empty($cuota->fecha_pago_real)) {
                $fechaCalculo = Carbon::parse($cuota->fecha_pago_real);
            } else {
                $fechaCalculo = Carbon::now();
            }
            
            $diasTranscurridosNormales = 0;
            $diasRetrasoCuota = 0; 

            // ==========================================
            // DÍAS PASADOS INFORMATIVOS (Para TODAS las cuotas)
            // ==========================================
            if ($fechaCalculo->isAfter($fechaCuota)) {
                $diasRetrasoCuota = $fechaCalculo->diffInDays($fechaCuota);
            }
            // Aquí inyectamos la variable que tu frontend espera leer
            $cuota->dias_pasados = in_array($cuota->estado, [1, 3]) ? $diasRetrasoCuota : 0;


            // ==========================================
            // DÍAS TRANSCURRIDOS NORMALES (Límite al periodo)
            // ==========================================
            if ($index === $firstUnpaidIndex && in_array($cuota->estado, [1, 3])) {
                $diasTranscurridosNormales = ($fechaCalculo->diffInDays($fechaInicioCuota) > $diasPeriodoCuota) 
                    ? $diasPeriodoCuota 
                    : $fechaCalculo->diffInDays($fechaInicioCuota);
                
                // Guardamos la mora FINANCIERA (la oficial que se cobra) para la cabecera
                $dias_pasados_mora = $cuota->dias_mora_cobro; 
            } else {
                if ($fechaCalculo->isAfter($fechaCuota)) {
                    $diasTranscurridosNormales = $diasPeriodoCuota;
                } elseif ($fechaCalculo->isBetween($fechaInicioCuota, $fechaCuota, true)) {
                    $diasTranscurridosNormales = $fechaCalculo->diffInDays($fechaInicioCuota);
                }
            }

            // PAGOS ACUMULADOS
            $cap_pagado = isset($cuota->capital_pagado) ? (float)$cuota->capital_pagado : 0;
            $int_pagado = isset($cuota->interes_pagado) ? (float)$cuota->interes_pagado : 0;
            $mora_pagada = isset($cuota->mora_pagada) ? (float)$cuota->mora_pagada : 0;

            // INTERÉS BRUTO HISTÓRICO (Usando diasPeriodoCuota para evitar saltos en pagos adelantados)
            $interesPorDiaNormal = $cuota->interes / $diasPeriodoCuota;
            $interesDevengadoBruto = $interesPorDiaNormal * $diasTranscurridosNormales;

            // INTERÉS MORATORIO (Se aplica el porcentaje a todas las cuotas vencidas independientemente)
            $capitalParaMora = in_array($cuota->estado, [1, 3]) ? max(0, $cuota->capital - $cap_pagado) : $cuota->capital;
            $interesPorDiaMora = 0;

            if ($cuota->lapso_capital == 'Mensual') {
                $baseCalculo = $cuota->saldo_capital + $capitalParaMora;
                $tasaPorcentaje = $cuota->tasa / 100;
                $interesPorDiaMora = ($baseCalculo * $tasaPorcentaje) / 30; // La base bancaria suele quedar en 30 para mora
            } else if (in_array($cuota->lapso_capital, ['Semanal', 'Quincenal'])) {
                $interesBaseMora = ($cuota->numero == 1 && isset($cuotas[$index + 1])) ? $cuotas[$index + 1]->interes : $cuota->interes;
                $proporcionDeuda = ($cuota->capital > 0) ? ($capitalParaMora / $cuota->capital) : 1;
                $interesPorDiaMora = ($interesBaseMora * $proporcionDeuda) / $diasBaseMora;
            } else {
                $proporcionDeuda = ($cuota->capital > 0) ? ($capitalParaMora / $cuota->capital) : 1;
                $interesPorDiaMora = ($cuota->interes * $proporcionDeuda) / $diasBaseMora;
            }

            $interesMoratorioBruto = $interesPorDiaMora * $diasRetrasoCuota;
            $interesTotalAcumuladoBruto = $interesDevengadoBruto + $interesMoratorioBruto;

            // PORCENTAJES AVANZADOS
            $porcentaje_capital = ($cuota->capital > 0) ? round(($cap_pagado / $cuota->capital) * 100, 1) : 0;
            $porcentaje_interes = ($interesTotalAcumuladoBruto > 0) ? round(($int_pagado / $interesTotalAcumuladoBruto) * 100, 1) : 0;

            // CÁLCULO DE RESTANTES (CASCADA)
            $capitalRestante = max(0, $cuota->capital - $cap_pagado);
            $intDevengadoRestante = max(0, $interesDevengadoBruto - $int_pagado);
            $excesoInt = max(0, $int_pagado - $interesDevengadoBruto);
            $intMoratorioRestante = max(0, $interesMoratorioBruto - $excesoInt);
            $interesTotalAcumuladoRestante = $intDevengadoRestante + $intMoratorioRestante;

            // ==========================================
            // MULTA FIJA (Solo afectará la cuota 1 económicamente)
            // ==========================================
            $moraFijaBruta = ($cuota->dias_mora_cobro > 0) ? ($cuota->dias_mora_cobro * 3) : 0;
            $moraFijaRestante = max(0, $moraFijaBruta - $mora_pagada);

            // ASIGNACIÓN AL OBJETO FINAL
            $cuota->dias_transcurridos = round($diasTranscurridosNormales);
            
            $cuota->capital_pagado_total = round($cap_pagado, 2);
            $cuota->interes_pagado_total = round($int_pagado, 2);
            $cuota->porcentaje_capital_pagado = $porcentaje_capital;
            $cuota->porcentaje_interes_pagado = $porcentaje_interes;

            $cuota->capital_neto = round($capitalRestante, 2);
            $cuota->interes_devengado_neto = round($intDevengadoRestante, 2);
            $cuota->interes_moratorio_neto = round($intMoratorioRestante, 2);
            $cuota->interes_acumulado_neto = round($interesTotalAcumuladoRestante, 2);
            
            // La multa monetaria será > 0 solo en la cuota más vieja impaga
            $cuota->mora_fija_neta = round($moraFijaRestante, 2);

            return $cuota;
        });

        return [
            'cuotas' => $cuotas,
            'dias_pasados_mora' => $dias_pasados_mora
        ];
    }*/


    public function listarAmortizaciones(Request $request)
    {
        $id_plan_pago = $request->input('id_plan_pago');

        // 1. OBTENER CUOTAS
        $cuotas = DB::table('cuota')
            ->join('plan_pago', 'cuota.id_plan_pago', '=', 'plan_pago.id')
            ->select(
                'cuota.*',
                'plan_pago.fecha_inicio',
                'plan_pago.lapso_capital',
                'plan_pago.tasa',
                DB::raw('(SELECT MAX(fecha_pago) FROM pago WHERE pago.id_cuota = cuota.id) as fecha_pago_real'),
                DB::raw('CASE 
                    WHEN cuota.estado IN (1, 3) AND cuota.id = (
                        SELECT MIN(id) FROM cuota 
                        WHERE id_plan_pago = ? AND estado IN (1, 3)
                    ) THEN 
                        GREATEST(0, DATEDIFF(NOW(), GREATEST(cuota.fecha, COALESCE(plan_pago.fecha_ultima_amortizacion, cuota.fecha))))
                    ELSE 0 
                END as dias_mora_cobro')
            )
            ->where('cuota.id_plan_pago', $id_plan_pago)
            ->addBinding($id_plan_pago, 'select')
            ->orderBy('cuota.numero', 'asc')
            ->get();

        $firstUnpaidIndex = $cuotas->search(function ($cuota) {
            return in_array($cuota->estado, [1, 3]);
        });

        $dias_pasados_mora = 0;

        // Saldo capital base para interés moratorio = saldo_capital de la última cuota pagada (estado = 2).
        // saldo_capital almacena el saldo pendiente DESPUÉS de pagar esa cuota (generado al crear el plan).
        // Si ninguna cuota ha sido pagada (primera cuota en mora), se usa el monto total del crédito.
        $lastPaidCuota = $cuotas->last(fn($c) => $c->estado == 2);
        $saldoCapitalMora = $lastPaidCuota
            ? (float) $lastPaidCuota->saldo_capital
            : (float) $cuotas->sum('capital');

        $cuotas = $cuotas->map(function ($cuota, $index) use ($cuotas, $firstUnpaidIndex, &$dias_pasados_mora, $saldoCapitalMora) {
            
            // NORMALIZACIÓN DE FECHAS A 00:00:00 
            $fechaCuota = Carbon::parse($cuota->fecha)->startOfDay();
            
            $fechaInicioCuota = $index === 0
                ? Carbon::parse($cuotas->first()->fecha_inicio)->startOfDay()
                : Carbon::parse($cuotas[$index - 1]->fecha)->startOfDay();

            // DÍAS REALES DEL PERIODO 
            $diasPeriodoCuota = max(1, $fechaInicioCuota->diffInDays($fechaCuota, false));
            
            if ($cuota->estado == 2 && !empty($cuota->fecha_pago_real)) {
                $fechaCalculo = Carbon::parse($cuota->fecha_pago_real)->startOfDay();
            } else {
                $fechaCalculo = Carbon::now()->startOfDay();
            }
            
            $diasTranscurridosNormales = 0;
            $diasRetrasoCuota = 0; 

            // CÁLCULO ESTRICTO DE DÍAS PASADOS (Informativo para Vue)
            if ($fechaCalculo->isAfter($fechaCuota)) {
                $diasRetrasoCuota = $fechaCuota->diffInDays($fechaCalculo, false);
            }
            $cuota->dias_pasados = in_array($cuota->estado, [1, 3]) ? $diasRetrasoCuota : 0;


            // DÍAS TRANSCURRIDOS (Para interés normal)
            if ($index === $firstUnpaidIndex && in_array($cuota->estado, [1, 3])) {
                $diasDesdeInicio = $fechaInicioCuota->diffInDays($fechaCalculo, false);
                $diasTranscurridosNormales = max(0, min($diasDesdeInicio, $diasPeriodoCuota));
                $dias_pasados_mora = $cuota->dias_mora_cobro; 
            } else {
                if ($fechaCalculo->isAfter($fechaCuota) || $fechaCalculo->isSameDay($fechaCuota)) {
                    $diasTranscurridosNormales = $diasPeriodoCuota;
                } else {
                    $diasDesdeInicio = $fechaInicioCuota->diffInDays($fechaCalculo, false);
                    $diasTranscurridosNormales = max(0, min($diasDesdeInicio, $diasPeriodoCuota));
                }
            }

            // PAGOS ACUMULADOS
            $cap_pagado = isset($cuota->capital_pagado) ? (float)$cuota->capital_pagado : 0;
            $int_pagado = isset($cuota->interes_pagado) ? (float)$cuota->interes_pagado : 0;
            $mora_pagada = isset($cuota->mora_pagada) ? (float)$cuota->mora_pagada : 0;

            // INTERÉS DIARIO DE LA CUOTA — base común para devengado y moratorio
            $interesPorDiaNormal = ($diasPeriodoCuota > 0) ? ($cuota->interes / $diasPeriodoCuota) : 0;

            // INTERÉS DEVENGADO — interés diario × días transcurridos del período
            $interesDevengadoBruto = $interesPorDiaNormal * $diasTranscurridosNormales;

            // INTERÉS MORATORIO
            // Fórmula: saldo_capital_última_cuota_pagada × (tasa/100) / diasPeriodo × diasMora
            // - Base: saldo_capital de la última cuota pagada (deuda vigente tras el último pago).
            //         Si ninguna cuota fue pagada, es el monto total del crédito.
            // - Tasa: la tasa periódica del plan (plan_pago.tasa, en porcentaje).
            // - Solo aplica a la primera cuota pendiente de pago.
            if ($index === $firstUnpaidIndex && in_array($cuota->estado, [1, 3]) && $diasRetrasoCuota > 0) {
                $tasaDiariaMora = ($diasPeriodoCuota > 0)
                    ? ((float)$cuota->tasa / 100) / $diasPeriodoCuota
                    : 0;
                $interesMoratorioBruto = $saldoCapitalMora * $tasaDiariaMora * $diasRetrasoCuota;
            } else {
                $interesMoratorioBruto = 0;
            }

            $interesTotalAcumuladoBruto = $interesDevengadoBruto + $interesMoratorioBruto;

            // PORCENTAJES AVANZADOS
            $porcentaje_capital = ($cuota->capital > 0) ? round(($cap_pagado / $cuota->capital) * 100, 1) : 0;
            $porcentaje_interes = ($interesTotalAcumuladoBruto > 0) ? round(($int_pagado / $interesTotalAcumuladoBruto) * 100, 1) : 0;

            // CÁLCULO DE RESTANTES (NETOS CASCADA)
            $capitalRestante = max(0, $cuota->capital - $cap_pagado);
            $intDevengadoRestante = max(0, $interesDevengadoBruto - $int_pagado);
            $excesoInt = max(0, $int_pagado - $interesDevengadoBruto);
            $intMoratorioRestante = max(0, $interesMoratorioBruto - $excesoInt);
            $interesTotalAcumuladoRestante = $intDevengadoRestante + $intMoratorioRestante;

            // MULTA FIJA ECONÓMICA
            $moraFijaBruta = ($cuota->dias_mora_cobro > 0) ? ($cuota->dias_mora_cobro * 3) : 0;
            $moraFijaRestante = max(0, $moraFijaBruta - $mora_pagada);

            // ASIGNACIÓN AL OBJETO FINAL
            $cuota->dias_transcurridos = round($diasTranscurridosNormales);
            
            $cuota->capital_pagado_total = round($cap_pagado, 2);
            $cuota->interes_pagado_total = round($int_pagado, 2);
            $cuota->porcentaje_capital_pagado = $porcentaje_capital;
            $cuota->porcentaje_interes_pagado = $porcentaje_interes;

            $cuota->capital_neto = round($capitalRestante, 2);
            $cuota->interes_devengado_neto = round($intDevengadoRestante, 2);
            $cuota->interes_moratorio_neto = round($intMoratorioRestante, 2);
            $cuota->interes_acumulado_neto = round($interesTotalAcumuladoRestante, 2);
            $cuota->mora_fija_neta = round($moraFijaRestante, 2);

            return $cuota;
        });

        return [
            'cuotas' => $cuotas,
            'dias_pasados_mora' => $dias_pasados_mora
        ];
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
        
        $pagos = DB::table('pago')
            ->where('id_cuota', $id_cuota)
            ->where('estado', 1)
            ->orderBy('fecha_pago', 'desc')
            ->orderBy('id', 'desc')
            ->get();
            
        return response()->json($pagos);
    }
}
