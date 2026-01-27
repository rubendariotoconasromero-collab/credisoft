<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;
use Dompdf\Dompdf;
use Dompdf\Options;
use PDF;
use Mpdf\Mpdf;
use Carbon\Carbon;
use App\Models\OrdenPagoReprogramacion;

class CajaController extends Controller
{
    //
    public function index(){
        return view('frmCaja');
    }

    public function getCaja(Request $request){
        $caja=DB::table('caja')
        ->join('users', 'users.id', '=', 'caja.id_usuario')
        // ->join('pago','pago.id_caja', '=', 'caja.id')
        // ->join('egreso','egreso.id_caja', '=', 'caja.id')
        ->select(
        // DB::raw('sum(pago.monto_pago) as ingreso_total'),
        // DB::raw('sum(egreso.monto) as egreso_total'),
        'caja.id', 'caja.estado','caja.fechahora_apertura', 'caja.fechahora_cierre', 'users.name as usuario', 
        'caja.monto_inicial', 'caja.monto_final', 'caja.efectivo_total', 'caja.deposito_total', 
        'caja.efectivo_venta', 'caja.deposito_venta', 'caja.efectivo_gasto', 'caja.deposito_gasto', 
        'caja.total_ingreso', 'caja.total_egreso', 'caja.diferencia'
        ,
        DB::raw('(SELECT SUM(CASE WHEN pago.forma_pago = "efectivo" THEN pago.monto_pago ELSE 0 END) FROM pago WHERE pago.id_caja = caja.id and pago.estado=1) as suma_efectivo'),
        DB::raw('(SELECT SUM(CASE WHEN pago.forma_pago = "transferencia - QR" THEN pago.monto_pago ELSE 0 END) FROM pago WHERE pago.id_caja = caja.id and pago.estado=1) as suma_transferencia_qr'),
        DB::raw('(SELECT SUM(CASE WHEN pago.forma_pago = "Depósito banco" THEN pago.monto_pago ELSE 0 END) FROM pago WHERE pago.id_caja = caja.id and pago.estado=1) as suma_deposito'),
    
        DB::raw('(SELECT SUM(CASE WHEN pago_amortizacion.forma_pago = "efectivo" THEN pago_amortizacion.monto_pago ELSE 0 END) FROM pago_amortizacion WHERE pago_amortizacion.id_caja = caja.id) as suma_efectivo_amortizacion'),
        DB::raw('(SELECT SUM(CASE WHEN pago_amortizacion.forma_pago = "transferencia - QR" THEN pago_amortizacion.monto_pago ELSE 0 END) FROM pago_amortizacion WHERE pago_amortizacion.id_caja = caja.id) as suma_transferencia_qr_amortizacion'),
        DB::raw('(SELECT SUM(CASE WHEN pago_amortizacion.forma_pago = "Depósito banco" THEN pago_amortizacion.monto_pago ELSE 0 END) FROM pago_amortizacion WHERE pago_amortizacion.id_caja = caja.id) as suma_deposito_amortizacion'),
        )
        ->addSelect(DB::raw('(SELECT SUM(monto_pago) FROM pago WHERE pago.id_caja = caja.id and estado=1) as ingreso_total'))
        ->addSelect(DB::raw('(SELECT SUM(monto_pago) FROM pago_amortizacion WHERE pago_amortizacion.id_caja = caja.id) as ingreso_total_amortizacion'))
        ->addSelect(DB::raw('(SELECT SUM(egreso.monto) FROM egreso WHERE egreso.id_caja = caja.id) as egreso_total'))
        ->addSelect(DB::raw('(SELECT SUM(ingreso.monto) FROM ingreso WHERE ingreso.id_caja = caja.id) as ingreso_total_ingreso'))
        ->addSelect(DB::raw('(SELECT SUM(pago_administrativo.monto) FROM pago_administrativo WHERE pago_administrativo.id_caja = caja.id) as pago_administrativo_total'))
        ->addSelect(DB::raw('(SELECT SUM(desembolso.monto) FROM desembolso WHERE desembolso.id_caja = caja.id) as desembolso_total'))

        ->groupBy('caja.id', 'caja.fechahora_apertura', 'caja.fechahora_cierre', 'users.name', 
        'caja.monto_inicial', 'caja.monto_final', 'caja.efectivo_total', 'caja.deposito_total', 
        'caja.efectivo_venta', 'caja.deposito_venta', 'caja.efectivo_gasto', 'caja.deposito_gasto', 
        'caja.total_ingreso', 'caja.total_egreso', 'caja.diferencia', 'caja.estado')
        ->orderBy('caja.id', 'desc')
        ->where($request->criterio, 'LIKE', '%'.$request->buscar.'%')
        ->paginate(15);
        return $caja;
    }

    public function getCajaFecha(Request $request){
        $caja=DB::table('caja')
        ->join('users', 'users.id', '=', 'caja.id_usuario')
        // ->join('pago','pago.id_caja', '=', 'caja.id')
        // ->join('egreso','egreso.id_caja', '=', 'caja.id')
        ->select(
        // DB::raw('sum(pago.monto_pago) as ingreso_total'),
        // DB::raw('sum(egreso.monto) as egreso_total'),
        'caja.id', 'caja.estado','caja.fechahora_apertura', 'caja.fechahora_cierre', 'users.name as usuario', 
        'caja.monto_inicial', 'caja.monto_final', 'caja.efectivo_total', 'caja.deposito_total', 
        'caja.efectivo_venta', 'caja.deposito_venta', 'caja.efectivo_gasto', 'caja.deposito_gasto', 
        'caja.total_ingreso', 'caja.total_egreso', 'caja.diferencia')
        ->addSelect(DB::raw('(SELECT SUM(monto_pago) FROM pago WHERE pago.id_caja = caja.id) as ingreso_total'))
        ->addSelect(DB::raw('(SELECT SUM(monto_pago) FROM pago_amortizacion WHERE pago_amortizacion.id_caja = caja.id) as ingreso_total_amortizacion'))
        ->addSelect(DB::raw('(SELECT SUM(egreso.monto) FROM egreso WHERE egreso.id_caja = caja.id) as egreso_total'))
        ->addSelect(DB::raw('(SELECT SUM(ingreso.monto) FROM ingreso WHERE ingreso.id_caja = caja.id) as ingreso_total_ingreso'))
        ->addSelect(DB::raw('(SELECT SUM(pago_administrativo.monto) FROM pago_administrativo WHERE pago_administrativo.id_caja = caja.id) as pago_administrativo_total'))
        ->addSelect(DB::raw('(SELECT SUM(desembolso.monto) FROM desembolso WHERE desembolso.id_caja = caja.id) as desembolso_total'))


        ->groupBy('caja.id', 'caja.fechahora_apertura', 'caja.fechahora_cierre', 'users.name', 
        'caja.monto_inicial', 'caja.monto_final', 'caja.efectivo_total', 'caja.deposito_total', 
        'caja.efectivo_venta', 'caja.deposito_venta', 'caja.efectivo_gasto', 'caja.deposito_gasto', 
        'caja.total_ingreso', 'caja.total_egreso', 'caja.diferencia', 'caja.estado')
        ->orderBy('caja.id', 'desc')
        // ->where($request->criterio, 'LIKE', '%'.$request->buscar.'%')
        ->whereDate('caja.fechahora_apertura', '>=', $request->fecha_inicio)
        ->whereDate('caja.fechahora_apertura', '<=', $request->fecha_final)
        ->paginate(15);
        return $caja;
    }
    public function save(Request $request){
        DB::beginTransaction();
        
        try{
            DB::table('caja')->insert([
                'fechahora_apertura'=>$request->fechahora_apertura,
                'monto_inicial'=>(empty($request->monto_inicial) || $request->monto_inicial<0)?0:$request->monto_inicial,
                'monto_final'=>0,
                'efectivo_total'=>0,
                'deposito_total'=>0,
                'efectivo_venta'=>0,
                'deposito_venta'=>0,
                'efectivo_gasto'=>0,
                'deposito_gasto'=>0,
                'total_ingreso'=>0,
                'total_egreso'=>0,
                'diferencia'=>0,
                'id_usuario'=>Auth::id(),
            ]);
            DB::commit();
        }catch(Exception $exception){
            DB::rollback();
        }

    }

    public function cajaAbierta(Request $request){
        $caja=DB::table('caja')
        ->join('users', 'users.id', '=', 'caja.id_usuario')
        ->select('users.name', 'users.id as id_usuario')
        ->where('caja.estado', 1)
        ->get();
        
        if($caja->count()>0){
            if($caja[0]->name==Auth::user()->name){
                $retornar=[
                    'usuario_actual'=>1,
                ];
            }else{
                $retornar=[
                    'usuario_actual'=>0,
                    'usuario'=>$caja[0]->name,
                    // 'usuario_actual'=>$caja[0]->name,
                ];
            }
            return $retornar;
        }else{
            return ['usuario_actual'=>-1];
        }
    }

    public function getMontoTotalPagos(Request $request){

        $monto_total_pagos = DB::table('caja')
        ->join('pago', 'caja.id', '=', 'pago.id_caja')
        ->select('caja.id', DB::raw('sum(pago.monto_pago) as monto_total_pagos'))
        ->where('pago.estado', 1)
        ->where('caja.id', $request->id_caja)
        ->groupBy('caja.id')
        ->get()[0]->monto_total_pagos;

        return ['monto_total_pagos'=>$monto_total_pagos];
    }

    public function closeCaja(Request $request){

        DB::table('caja')->where('id', $request->id_caja)->update([
            'fechahora_apertura'=>$request->fechahora_apertura,
            'fechahora_cierre'=>$request->fechahora_cierre,
            'monto_inicial'=>$request->monto_inicial,
            'monto_final'=>$request->monto_final,
            'efectivo_total'=>$request->efectivo_total,
            'deposito_total'=>$request->deposito_total,
            'efectivo_venta'=>$request->efectivo_venta,
            'deposito_venta'=>$request->deposito_venta,
            'efectivo_gasto'=>$request->efectivo_gasto,
            'deposito_gasto'=>$request->deposito_gasto,
            'total_ingreso'=>$request->total_ingreso,
            'total_egreso'=>$request->total_egreso,
            'diferencia'=>$request->diferencia,
            'id_usuario'=>Auth::id(),
            'estado'=>0,
        ]);
    }

    public function getPagos(Request $request){
        $pagos = DB::table('pago')
        ->join('cuota', 'pago.id_cuota', '=', 'cuota.id')
        ->join('users', 'users.id', '=', 'pago.id_usuario')
        ->join('plan_pago', 'plan_pago.id', '=', 'cuota.id_plan_pago')
        ->join('solicitud', 'plan_pago.id_solicitud', '=', 'solicitud.id')
        ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
        ->select('pago.id', 'pago.fecha_pago', 'pago.monto_pago', 'pago.id_cuota', 'pago.estado',
        'users.name as asesor', 'cliente.nombre as cliente', 'cuota.numero as cuota', 'solicitud.nro_cuotas',
        'plan_pago.id as plan_pago', 'plan_pago.total_pagar as total_pago_credito', 'cuota.saldo_capital', 'cuota.interes')
        ->where('pago.estado', 1)
        ->where('pago.id_caja', $request->id_caja)
        ->where($request->opcion, 'LIKE', '%'.$request->buscar.'%')
        ->whereDate('pago.fecha_pago', '>=', $request->fecha_inicio)
        ->whereDate('pago.fecha_pago', '<=', $request->fecha_final)
        ->orderBy('pago.id', 'desc')
        ->paginate(15);

        $totalPagos=DB::table('pago')
        ->join('cuota', 'pago.id_cuota', '=', 'cuota.id')
        ->join('users', 'users.id', '=', 'pago.id_usuario')
        ->join('plan_pago', 'plan_pago.id', '=', 'cuota.id_plan_pago')
        ->join('solicitud', 'plan_pago.id_solicitud', '=', 'solicitud.id')
        ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
        ->select(DB::raw('sum(pago.monto_pago) as totalPagos'))
        ->where($request->opcion, 'LIKE', '%'.$request->buscar.'%')
        ->whereDate('pago.fecha_pago', '>=', $request->fecha_inicio)
        ->whereDate('pago.fecha_pago', '<=', $request->fecha_final)
        ->where('pago.estado', 1)
        ->where('pago.id_caja', $request->id_caja)
        ->get()[0]->totalPagos;

        return ['pagos'=>$pagos, 'totalPagos'=>$totalPagos];
        //return $pagos;
    }

    public function getPagosAmortizaciones(Request $request){
        $pagos = DB::table('pago_amortizacion')
        // ->join('cuota', 'pago.id_cuota', '=', 'cuota.id')
        ->join('plan_pago', 'plan_pago.id', '=', 'pago_amortizacion.id_plan_pago')
        ->join('solicitud', 'plan_pago.id_solicitud', '=', 'solicitud.id')
        ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
        ->join('users', 'users.id', '=', 'solicitud.id_usuario')

        ->select('pago_amortizacion.id', 'pago_amortizacion.fecha as fecha_pago', 'pago_amortizacion.monto_pago', 
        'users.personal as asesor', 'cliente.nombre as cliente','solicitud.nro_cuotas',
        'plan_pago.id as plan_pago', 'plan_pago.total_pagar as total_pago_credito')

        // ->where('pago.estado', 1)
        ->where('pago_amortizacion.id_caja', $request->id_caja)
        ->where($request->opcion, 'LIKE', '%'.$request->buscar.'%')
        ->orderBy('pago_amortizacion.id', 'desc')
        ->paginate(15);

        $totalPagos=DB::table('pago_amortizacion')
        ->join('plan_pago', 'plan_pago.id', '=', 'pago_amortizacion.id_plan_pago')
        ->join('solicitud', 'plan_pago.id_solicitud', '=', 'solicitud.id')
        ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
        ->join('users', 'users.id', '=', 'solicitud.id_usuario')

        ->select(DB::raw('sum(pago_amortizacion.monto_pago) as totalPagos'))
        ->where($request->opcion, 'LIKE', '%'.$request->buscar.'%')
        // ->where('pago.estado', 1)
        ->where('pago_amortizacion.id_caja', $request->id_caja)
        ->get()[0]->totalPagos;

        return ['pagos'=>$pagos, 'totalPagosAmortizacion'=>$totalPagos];
        //return $pagos;
    }

    public function exportarPagosCaja(Request $request){
        // Crea una instancia de Dompdf
        $pagos = DB::table('pago')
        ->join('cuota', 'pago.id_cuota', '=', 'cuota.id')
        ->join('users', 'users.id', '=', 'pago.id_usuario')
        ->join('plan_pago', 'plan_pago.id', '=', 'cuota.id_plan_pago')
        ->join('solicitud', 'plan_pago.id_solicitud', '=', 'solicitud.id')
        ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
        ->select('pago.id', 'pago.fecha_pago', 'pago.monto_pago', 'pago.id_cuota', 'pago.estado',
        'users.name as asesor', 'cliente.nombre as cliente', 'cuota.numero as cuota', 'solicitud.nro_cuotas',
        'plan_pago.id as plan_pago', 'plan_pago.total_pagar as total_pago_credito', 'cuota.saldo_capital', 'cuota.interes')
        ->where('pago.estado', 1)
        ->where('pago.id_caja', $request->id_caja)
        // ->where($request->opcion, 'LIKE', '%'.$request->buscar.'%')
        ->whereDate('pago.fecha_pago', '>=', $request->fecha_inicio)
        ->whereDate('pago.fecha_pago', '<=', $request->fecha_final)
        ->orderBy('pago.id', 'desc')
        ->get();

        $totalMontoPago = $pagos->sum('monto_pago');


    
        $dompdf = new Dompdf();
        $detalles=json_decode($request->lista_pagos, true);
        // Opciones de configuración de Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true); // Habilita el parser HTML5
        $options->set('isPhpEnabled', true); // Habilita la ejecución de código PHP en la vista (si es necesario)
        $dompdf->setOptions($options);

        // Carga la vista HTML para el reporte
        $html = [
            'lista_pagos' => $pagos,
            'codigo_caja' => $request->id_caja,
            'total_pagos' => $totalMontoPago,
            'usuario' => auth()->user()->name,
            'fecha_reporte' => now()->format('d/m/Y'),
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_final' => $request->fecha_final,
        ];

        /*
        // Carga el contenido HTML en Dompdf
        $dompdf->loadHtml($html);

        // Renderiza el PDF (esto puede tomar un tiempo si el contenido es grande)
        $dompdf->render();

        // Obtén el contenido del PDF como una cadena
        

        // Establece las cabeceras para mostrar el PDF en una nueva pestaña
        return response($dompdf->output())

        ->header('Content-Type', 'application/pdf')
        ->header('Content-Disposition', 'inline; filename="reporte_pagos_caja.pdf"');
        */
        $this->generatePDF($html, 'reporte.reporte_pagos_caja', 'reporte_pagos_caja');
    }
    
    public function exportarPagosCajaAmortizacion(Request $request){
        // Crea una instancia de Dompdf
    
        $dompdf = new Dompdf();
        $detalles=json_decode($request->lista_pagos, true);
        // Opciones de configuración de Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true); // Habilita el parser HTML5
        $options->set('isPhpEnabled', true); // Habilita la ejecución de código PHP en la vista (si es necesario)
        $dompdf->setOptions($options);

        // Carga la vista HTML para el reporte
        $html = view('reporte.reporte_pagos_caja_amortizacion', [
            'lista_pagos' => $detalles,
            'codigo_caja' => $request->id_caja,
            'total_pagos' => $request->total_pagos,
        ])->render();

        // Carga el contenido HTML en Dompdf
        $dompdf->loadHtml($html);

        // Renderiza el PDF (esto puede tomar un tiempo si el contenido es grande)
        $dompdf->render();

        // Obtén el contenido del PDF como una cadena
        

        // Establece las cabeceras para mostrar el PDF en una nueva pestaña
        return response($dompdf->output())

        ->header('Content-Type', 'application/pdf')
        ->header('Content-Disposition', 'inline; filename="reporte_pagos_caja_amortizacion.pdf"');
    }

    public function exportarIngresosCorrientesCaja(Request $request){
        // Crea una instancia de Dompdf

        $ingresos = DB::table('ingreso')
        ->join('users', 'ingreso.id_usuario', '=', 'users.id')
        ->join('caja', 'caja.id', '=', 'ingreso.id_caja')
        ->select('users.name as asesor', 'ingreso.fecha', 'ingreso.monto as monto_ingreso', 'ingreso.id', 'ingreso.estado', 
        'ingreso.descripcion',
        DB::raw('SUM(CASE WHEN ingreso.estado = 1 THEN ingreso.monto ELSE 0 END) as totalIngreso'),)
        ->where('caja.id', $request->id_caja)
        ->whereDate('ingreso.fecha', '>=', $request->fecha_inicio)
        ->whereDate('ingreso.fecha', '<=', $request->fecha_final)
        ->groupBy(
            'users.name',
            'ingreso.fecha',
            'ingreso.monto',
            'ingreso.id',
            'ingreso.estado',
            'ingreso.descripcion',

        )
        ->get();

        $total_ingresos_corrientes=$ingresos->sum('totalIngreso');
    
   

        // Carga la vista HTML para el reporte
        $html = [
            'lista_ingresos_corrientes' => $ingresos,
            'codigo_caja' => $request->id_caja,
            'total_ingresos_corrientes' => $total_ingresos_corrientes,

            'usuario' => auth()->user()->name,
            'fecha_reporte' => now()->format('d/m/Y'),
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_final' => $request->fecha_final,
        ];

        $this->generatePDF($html, 'reporte.reporte_ingresos_corrientes', 'reporte_ingresos_corrientes');


    }

    public function exportarGastosCorrientesCaja(Request $request){
        // Crea una instancia de Dompdf
    
        $gastos = DB::table('egreso')
        ->join('users', 'egreso.id_usuario', '=', 'users.id')
        ->join('caja', 'caja.id', '=', 'egreso.id_caja')
        ->select('users.name as asesor', 'egreso.fecha', 'egreso.monto as monto_gasto', 'egreso.id', 'egreso.estado', 'egreso.descripcion')
        ->where('caja.id', $request->id_caja)
        ->whereDate('egreso.fecha', '>=', $request->fecha_inicio)
        ->whereDate('egreso.fecha', '<=', $request->fecha_final)
        ->get();

        $total_gastos_corrientes=$gastos->sum('monto_gasto');

        // Carga la vista HTML para el reporte
        $html =  [
            'lista_gastos_corrientes' => $gastos,
            'codigo_caja' => $request->id_caja,
            'total_gastos_corrientes' => $total_gastos_corrientes,

            'usuario' => auth()->user()->name,
            'fecha_reporte' => now()->format('d/m/Y'),
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_final' => $request->fecha_final,
        ];

        

        $this->generatePDF($html, 'reporte.reporte_gastos_corrientes', 'reporte_gastos_corrientes');

    }

    public function getPlanesPagoSinDesembolsar(){
        $planes_pago = DB::table('plan_pago')
            ->join('solicitud', 'solicitud.id', '=', 'plan_pago.id_solicitud')
            ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
            ->join('users', 'users.id', '=', 'solicitud.id_usuario')
            ->select('solicitud.id as id_solicitud','solicitud.importe_solicitud', 'solicitud.moneda', 'solicitud.lapso_capital', 'solicitud.nro_cuotas',
            'solicitud.tasa', 'solicitud.fecha_desembolso', 'solicitud.fecha_primera_cuota', 'solicitud.destino_prestamo',
            'solicitud.tipo_garantia','solicitud.tipo_desembolso','solicitud.id_cliente', 'solicitud.id_usuario', 'cliente.nombre as cliente',
            'users.personal as asesor', 'solicitud.estado', 'plan_pago.fecha_inicio as fecha_inicio_plan', 'plan_pago.fecha_fin as fecha_fin_plan',
            'plan_pago.total_pagar as total_pagar_plan', 'plan_pago.estado as estado_plan', 'plan_pago.id',
            'cliente.ci', 'cliente.lugar_expedicion', 'plan_pago.desembolso')
            ->where('plan_pago.desembolso', 1)
            ->orderBy('plan_pago.id', 'desc')
            ->get();
        return $planes_pago;
    }

    public function actualizarDesembolso(Request $request){
        $boveda_abierta=DB::table('boveda')->count();
        $plan_pago= DB::table('plan_pago')->where('plan_pago.id', $request->id_plan_pago)->first();

        if($boveda_abierta<=0){
            return 0;
        }


        DB::beginTransaction();
        try{
            DB::table('plan_pago')->where('id', $request->id_plan_pago)->update([
                'desembolso'=>0,
            ]);

            DB::table('solicitud')->where('id', $plan_pago->id_solicitud)->update([
                'desembolso'=>1,
            ]);
    
            // Registrando desembolsos
            DB::table('desembolso')->insert([
                'monto'=>$request->monto,
                'fecha'=>Carbon::now(),
                'id_plan_pago'=>$request->id_plan_pago,
                'id_caja'=>$request->id_caja,
                'id_usuario'=>Auth::id(),
            ]);

            // Descontamos de boveda

            $id_boveda=DB::table('boveda')->orderBy('boveda.id', 'desc')->get()[0]->id;
    
            DB::table('movimientos_boveda')
            ->insertGetId([
                'tipo_movimiento'=>'salida',
                'monto'=>$request->monto,
                'descripcion'=>'Otorgamiento de préstamo',
                'fecha'=>now(),
                'id_boveda'=>$id_boveda,
                'id_usuario'=>Auth::user()->id,
            ]);



            DB::table('boveda')->where('id', $id_boveda)->update([
                'saldo_actual' => DB::raw('saldo_actual - ' . $request->monto)
            ]);


            DB::commit();
        }catch(Exception $e){
            DB::rollback();
        }
        
    }

    public function getPlanesPagoSinPagoAdm(Request $request){
        $planes_pago = DB::table('plan_pago')
        ->join('solicitud', 'solicitud.id', '=', 'plan_pago.id_solicitud')
        ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
        ->join('users', 'users.id', '=', 'solicitud.id_usuario')
        ->select('solicitud.tipo_solicitud','solicitud.monto_refinanciamiento','solicitud.id as id_solicitud','solicitud.importe_solicitud', 'solicitud.moneda', 'solicitud.lapso_capital', 'solicitud.nro_cuotas',
        'solicitud.tasa', 'solicitud.fecha_desembolso', 'solicitud.fecha_primera_cuota', 'solicitud.destino_prestamo',
        'solicitud.tipo_garantia','solicitud.tipo_desembolso','solicitud.id_cliente', 'solicitud.id_usuario', 'cliente.nombre as cliente',
        'users.personal as asesor', 'solicitud.estado', 'plan_pago.fecha_inicio as fecha_inicio_plan', 'plan_pago.fecha_fin as fecha_fin_plan',
        'plan_pago.total_pagar as total_pagar_plan', 'plan_pago.estado as estado_plan', 'plan_pago.id',
        'cliente.ci', 'cliente.lugar_expedicion', 'plan_pago.desembolso', 'plan_pago.pago_administrativo', 'solicitud.monto_pago_adm')
        ->where('plan_pago.pago_administrativo', 1)
        ->where('plan_pago.desembolso', 1)
        ->orderBy('solicitud.fecha_desembolso', 'desc')
        ->get();
        return $planes_pago;
    }

    public function guardarPagoAdministrativo(Request $request){
        DB::beginTransaction();
        try{
            DB::table('plan_pago')->where('id', $request->id_plan_pago)->update([
                'pago_administrativo'=>0,// pagado
            ]);
    
            // Registrando pagos adm
            DB::table('pago_administrativo')->insert([
                'monto'=>$request->monto,
                'fecha'=>now(),
                'descripcion'=>empty($request->descripcion)?'sin descripcion':$request->descripcion,
                'id_plan_pago'=>$request->id_plan_pago,
                'id_caja'=>$request->id_caja,
                'id_usuario'=>Auth::id(),
            ]);


            // Reg. en mov. caja
            DB::table('movimientos_caja')
            ->insertGetId([
                'tipo_movimiento'=>'ingreso',
                'monto'=>$request->monto,
                'descripcion'=>'Cobro gasto administrativo',
                'fecha'=>now(),
                'id_caja'=>$request->id_caja,
                'id_usuario'=>Auth::user()->id,
            ]);


            DB::commit();
        }catch(Exception $e){
            DB::rollback();
        }
    }

    public function reporteCajasFecha(Request $request){
        // Crea una instancia de Dompdf
    
        $dompdf = new Dompdf();
        $detalles=json_decode($request->lista_caja, true);
        // Opciones de configuración de Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true); // Habilita el parser HTML5
        $options->set('isPhpEnabled', true); // Habilita la ejecución de código PHP en la vista (si es necesario)
        $dompdf->setOptions($options);

        // Carga la vista HTML para el reporte
        $html = view('reporte.reporte_cajas', [
            'lista_caja' => $detalles,

        ])->render();

        // Carga el contenido HTML en Dompdf
        $dompdf->loadHtml($html);

        // Renderiza el PDF (esto puede tomar un tiempo si el contenido es grande)
        $dompdf->render();

        // Obtén el contenido del PDF como una cadena
        

        // Establece las cabeceras para mostrar el PDF en una nueva pestaña
        return response($dompdf->output())

        ->header('Content-Type', 'application/pdf')
        ->header('Content-Disposition', 'inline; filename="reporte_cajas.pdf"');
    }

    public function getDesembolsos(Request $request){
        $desembolsos = DB::table('desembolso')
        ->join('plan_pago', 'plan_pago.id', '=', 'desembolso.id_plan_pago')
        ->join('pago_administrativo', 'plan_pago.id', '=', 'pago_administrativo.id_plan_pago')
        ->join('caja', 'caja.id', '=', 'desembolso.id_caja')
        ->join('solicitud', 'solicitud.id', '=', 'plan_pago.id_solicitud')
        ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
        ->join('users', 'users.id', '=', 'solicitud.id_usuario')
        ->where('caja.id', $request->id_caja)
        ->whereDate('desembolso.fecha', '>=', $request->fecha_inicio)
        ->whereDate('desembolso.fecha', '<=', $request->fecha_final)
        ->select('cliente.nombre as cliente', 'desembolso.fecha', 'plan_pago.id as id_plan_pago', 'desembolso.monto',
        'desembolso.monto', 'users.personal as asesor', 'desembolso.estado', 'pago_administrativo.monto as monto_pago_adm')
        ->orderBy('desembolso.fecha', 'desc')
        ->paginate(50);

        $auxMontos=DB::table('desembolso')
        ->join('plan_pago', 'plan_pago.id', '=', 'desembolso.id_plan_pago')
        ->join('pago_administrativo', 'plan_pago.id', '=', 'pago_administrativo.id_plan_pago')
        ->join('caja', 'caja.id', '=', 'desembolso.id_caja')
        ->join('solicitud', 'solicitud.id', '=', 'plan_pago.id_solicitud')
        ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
        ->join('users', 'users.id', '=', 'solicitud.id_usuario')
        ->where('caja.id', $request->id_caja)
        ->where('desembolso.estado', 0)
        ->whereDate('desembolso.fecha', '>=', $request->fecha_inicio)
        ->whereDate('desembolso.fecha', '<=', $request->fecha_final)
        ->select(DB::raw('sum(desembolso.monto) as totalDesembolsos'), DB::raw('sum(pago_administrativo.monto) as totalPagosAdm'))
        ->first();

        return [
            'desembolsos'=>$desembolsos,
            'totalDesembolsos'=>empty($auxMontos->totalDesembolsos)?0:$auxMontos->totalDesembolsos,
            'totalPagosAdm'=>empty($auxMontos->totalPagosAdm)?0:$auxMontos->totalPagosAdm,
        ];
    }



    public function exportarDesembolsosCaja(Request $request)
    {
        $desembolsos = DB::table('desembolso')
        ->join('plan_pago', 'plan_pago.id', '=', 'desembolso.id_plan_pago')
        ->join('pago_administrativo', 'plan_pago.id', '=', 'pago_administrativo.id_plan_pago')
        ->join('caja', 'caja.id', '=', 'desembolso.id_caja')
        ->join('solicitud', 'solicitud.id', '=', 'plan_pago.id_solicitud')
        ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
        ->join('users', 'users.id', '=', 'solicitud.id_usuario')
        ->where('caja.id', $request->id_caja)
        ->whereDate('desembolso.fecha', '>=', $request->fecha_inicio)
        ->whereDate('desembolso.fecha', '<=', $request->fecha_final)
        ->select(
            'cliente.nombre as cliente',
            'cliente.id as codcli',
            'desembolso.fecha',
            'plan_pago.id as id_plan_pago',
            'desembolso.monto',
            'users.personal as asesor',
            'desembolso.estado',
            'solicitud.lapso_capital',
            'solicitud.tipo_garantia as garantia',
            'pago_administrativo.monto as monto_pago_adm',
            'desembolso.estado',
            DB::raw('SUM(CASE WHEN desembolso.estado = 0 THEN desembolso.monto ELSE 0 END) as totalDesembolsos'),
            DB::raw('SUM(CASE WHEN desembolso.estado = 0 THEN pago_administrativo.monto ELSE 0 END) as totalPagosAdm')
        )
        ->groupBy(
            'cliente.nombre',
            'cliente.id',
            'desembolso.fecha',
            'plan_pago.id',
            'desembolso.monto',
            'users.personal',
            'desembolso.estado',
            'solicitud.lapso_capital',
            'solicitud.tipo_garantia',
            'pago_administrativo.monto'
        )
        ->orderBy('desembolso.fecha', 'desc')
        ->get();

        $totalDesembolsos = $desembolsos->sum('totalDesembolsos');
        $totalPagosAdm = $desembolsos->sum('totalPagosAdm');

        $data = [
            'desembolsos' => $desembolsos,
            'totalDesembolsos' => $totalDesembolsos,
            'totalPagosAdm' => $totalPagosAdm,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_final' => $request->fecha_final,
            'usuario' => auth()->user()->name,
            'fecha_reporte' => now()->format('d/m/Y'),
        ];

        $this->generatePDF($data, 'reporte.reporte_desembolso', 'reporte_desembolso');
    }

    public function historialDesembolsosPagos()
    {
        return view('frmHistorialDesPagos');
    }

    public function historialIngresos()
    {
        return view('frmHistorialIngresos');
    }

    public function historialGastos()
    {
        return view('frmHistorialGastos');
    }

    public function historialPagos()
    {
        return view('frmHistorialPagos');
    }


    public function historialDesembolsosPagosListado(Request $request)
    {
        $fechaInicio = $request->fecha_inicio;
        $fechaFinal = $request->fecha_final;

        $desembolsos = DB::table('desembolso')
        ->select(DB::raw('DISTINCT desembolso.id, cliente.nombre as cliente, desembolso.fecha, 
            plan_pago.id as id_plan_pago, desembolso.monto, users.personal as asesor, 
            desembolso.estado, pago_administrativo.monto as monto_pago_adm, cliente.ci, 
            cliente.id as id_cliente, cliente.lugar_expedicion, solicitud.tipo_garantia, 
            solicitud.lapso_capital, solicitud.nro_cuotas'))
        ->join('plan_pago', 'plan_pago.id', '=', 'desembolso.id_plan_pago')
        ->join('pago_administrativo', 'plan_pago.id', '=', 'pago_administrativo.id_plan_pago')
        ->join('caja', 'caja.id', '=', 'desembolso.id_caja')
        ->join('solicitud', 'solicitud.id', '=', 'plan_pago.id_solicitud')
        ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
        ->join('users', 'users.id', '=', 'solicitud.id_usuario')
        ->whereDate('desembolso.fecha', '>=', $fechaInicio)
        ->whereDate('desembolso.fecha', '<=', $fechaFinal)
        ->where($request->criterio, 'like', '%'.$request->buscar.'%')
        // ->where('desembolso.estado', 0)
        ->orderBy('desembolso.fecha', 'desc')
        ->paginate(50);

        $auxMontos = DB::table('desembolso')
            ->join('plan_pago', 'plan_pago.id', '=', 'desembolso.id_plan_pago')
            ->join('pago_administrativo', 'plan_pago.id', '=', 'pago_administrativo.id_plan_pago')
            ->join('caja', 'caja.id', '=', 'desembolso.id_caja')
            ->join('solicitud', 'solicitud.id', '=', 'plan_pago.id_solicitud')
            ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
            ->join('users', 'users.id', '=', 'solicitud.id_usuario')
            ->whereDate('desembolso.fecha', '>=', $fechaInicio)
            ->whereDate('desembolso.fecha', '<=', $fechaFinal)
            ->where('desembolso.estado', 0)
            ->where('pago_administrativo.estado', 0)
            ->select(DB::raw('sum(desembolso.monto) as totalDesembolsos'), DB::raw('sum(pago_administrativo.monto) as totalPagosAdm'))
            ->first();

        return [
            'desembolsos' => $desembolsos,
            'totalDesembolsos' => empty($auxMontos->totalDesembolsos) ? 0 : $auxMontos->totalDesembolsos,
            'totalPagosAdm' => empty($auxMontos->totalPagosAdm) ? 0 : $auxMontos->totalPagosAdm,
        ];
    }

    public function anularDesembolso(Request $request){
        DB::beginTransaction();
        try{
            $id_plan_pago=DB::table('plan_pago')
            ->join('desembolso', 'desembolso.id_plan_pago', '=', 'plan_pago.id')
            ->where('desembolso.id', $request->id)
            ->select('plan_pago.id as id_plan_pago')
            ->first();

            $id_pago_adm=DB::table('plan_pago')
            ->join('pago_administrativo', 'pago_administrativo.id_plan_pago', '=', 'plan_pago.id')
            ->where('plan_pago.id', $id_plan_pago->id_plan_pago)
            ->select('pago_administrativo.id as id_pago_adm')
            ->first();


            DB::table('plan_pago')->where('id', $id_plan_pago->id_plan_pago)->update([
                'desembolso'=>1,
            ]);

            DB::table('plan_pago')->where('id', $id_plan_pago->id_plan_pago)->update([
                'desembolso'=>1,
                'pago_administrativo'=>1,
            ]);
    
            DB::table('desembolso')->where('id', $request->id)->update([
                'estado'=>1,
            ]);

            DB::table('pago_administrativo')->where('id', $id_pago_adm->id_pago_adm)->update([
                'estado'=>1,
            ]);

            DB::commit();

        }catch(Exception $e){
            DB::rollback();
        }
        
    }

    public function getMovimientosCaja(Request $request){
        $query = DB::table('movimientos_caja')
            ->join('users', 'users.id', '=', 'movimientos_caja.id_usuario')
            ->select('movimientos_caja.*', 'users.personal')
            ->where('id_caja', $request->id_caja);

        // Filtrar por tipo de movimiento (ingreso/salida/todos)
        if ($request->has('tipo') && $request->tipo != 'todos') {
            $query->where('movimientos_caja.tipo_movimiento', $request->tipo);
        }

        // Filtrar por rango de fechas
        if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
            $query->whereDate('movimientos_caja.fecha', '>=', $request->fecha_inicio)
                ->whereDate('movimientos_caja.fecha', '<=', $request->fecha_fin);
        }

        // Clonar el query para usar en los cálculos de totales antes de paginar
        $queryTotalesIngreso = clone $query;
        $queryTotalesSalida = clone $query;

        // Calcular totales
        $salidas = $queryTotalesSalida->where('movimientos_caja.tipo_movimiento', 'salida')->sum('monto');
        $ingresos = $queryTotalesIngreso->where('movimientos_caja.tipo_movimiento', 'ingreso')->sum('monto');

        // Paginación
        $registros = $query->orderBy('movimientos_caja.id', 'desc')->paginate(30);

        return response()->json([
            'movimientos' => $registros,
            'totales' => [
                'ingresos' => $ingresos,
                'salidas' => $salidas,
            ],
        ]);
    }


    public function generarComprobanteCliente(Request $request)
    {
        try {
            // Validate inputs
            $request->validate([
                'id_cliente' => 'required|integer|exists:cliente,id',
                'id_plan_pago' => 'required|integer|exists:plan_pago,id',
            ]);

            $idCliente = $request->id_cliente;
            $idPlanPago = $request->id_plan_pago;

            // Fetch disbursement data
            $desembolso = DB::table('desembolso')
                ->join('plan_pago', 'plan_pago.id', '=', 'desembolso.id_plan_pago')
                ->join('pago_administrativo', 'plan_pago.id', '=', 'pago_administrativo.id_plan_pago')
                ->join('caja', 'caja.id', '=', 'desembolso.id_caja')
                ->join('solicitud', 'solicitud.id', '=', 'plan_pago.id_solicitud')
                ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
                ->join('users', 'users.id', '=', 'solicitud.id_usuario')
                ->where('cliente.id', $idCliente)
                ->where('plan_pago.id', $idPlanPago)
                ->select([
                    'cliente.nombre as cliente',
                    'cliente.actividad',
                    'cliente.id as codcli',
                    'desembolso.fecha as fecha_desembolso',
                    'plan_pago.id as id_plan_pago',
                    'cliente.ci as ci_cliente',
                    'cliente.lugar_expedicion',
                    'desembolso.monto',
                    'users.personal as asesor',
                    'desembolso.estado',
                    'solicitud.lapso_capital',
                    'solicitud.tipo_garantia as garantia',
                    'plan_pago.fecha_inicio as fecha_credito',
                    'solicitud.importe_solicitud as importe_prestamo',
                    'plan_pago.fecha_fin as fecha_max_devolucion',
                    'solicitud.nro_cuotas as cuotas',
                    'pago_administrativo.monto as monto_pago_adm',
                ])
                ->first();

            if (!$desembolso) {
                \Log::warning("No disbursement found for client ID: {$idCliente}, plan_pago ID: {$idPlanPago}");
                return response()->json(['error' => 'No se encontraron datos para el cliente o plan de pago'], 404);
            }

            // Fetch first cuota
            $cuota = DB::table('cuota')
                ->where('id_plan_pago', $idPlanPago)
                ->select('fecha', 'total')
                ->orderBy('fecha', 'asc')
                ->first();

            if (!$cuota) {
                \Log::warning("No cuotas found for plan_pago ID: {$idPlanPago}");
                return response()->json(['error' => 'No se encontraron cuotas para el plan de pago'], 404);
            }

            // Fetch client telephones and addresses
            $telefonos = DB::table('telefono')
                ->where('id_cliente', $idCliente)
                // ->select('tipo', 'numero')
                ->get();

            $direcciones = DB::table('direccion')
                ->where('id_cliente', $idCliente)
                // ->select('tipo', 'descripcion')
                ->get();

            // Fetch company details
            $empresa = DB::table('mi_empresa')
                ->select('nombre', 'direccion', 'telefono', 'email', 'logo')
                ->first();

            if (!$empresa) {
                \Log::warning("No company data found in mi_empresa table");
                return response()->json(['error' => 'No se encontraron datos de la empresa'], 500);
            }

            // Prepare logo URL
            $url = $empresa->logo ?? 'logo_sistema_codesoft.png';
            $logoPath = public_path('img/' . $url);
            if (!file_exists($logoPath)) {
                \Log::warning("Logo file not found at: {$logoPath}, using fallback");
                $url = 'logo_sistema_codesoft.png';
            }

            // Prepare data for PDF
            $data = [
                'desembolso' => $desembolso,
                'telefonos' => $telefonos,
                'direcciones' => $direcciones,
                'usuario' => auth()->user()->name ?? 'Sistema',
                'empresa' => $empresa,
                'url' => $url,
                'fecha_reporte' => now()->format('d/m/Y'),
                'monto_primera_cuota' => number_format($cuota->total, 2, ',', '.'),
                'fecha_primera_cuota' => date('d/m/Y', strtotime($cuota->fecha)),
            ];

            // \Log data for debugging
            \Log::debug('PDF data prepared', ['data' => $data]);

            // Generate PDF
            return $this->generatePDFHorizontalOficio($data, 'reporte.comprobante_desembolso_cliente', 'comprobante_desembolso');

        } catch (\Exception $e) {
            \Log::error('Error generating client disbursement receipt: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'No se pudo generar el comprobante.'], 500);
        }
    }

    public function getComprobanteClienteData(Request $request)
    {
        try {


        
            $idCliente = $request->id_cliente;
            $idPlanPago = $request->id_plan_pago;

            // Fetch disbursement data
             $desembolso = DB::table('desembolso')
                ->join('plan_pago', 'plan_pago.id', '=', 'desembolso.id_plan_pago')
                ->join('pago_administrativo', 'plan_pago.id', '=', 'pago_administrativo.id_plan_pago')
                ->join('caja', 'caja.id', '=', 'desembolso.id_caja')
                ->join('solicitud', 'solicitud.id', '=', 'plan_pago.id_solicitud')
                ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
                ->join('users', 'users.id', '=', 'solicitud.id_usuario')
                ->where('cliente.id', $idCliente)
                ->where('plan_pago.id', $idPlanPago)
                ->select([
                    'cliente.nombre as cliente',
                    'cliente.actividad',
                    'cliente.imagen',
                    'cliente.id as codcli',
                    'desembolso.fecha as fecha_desembolso',
                    'plan_pago.id as id_plan_pago',
                    'cliente.ci as ci_cliente',
                    'cliente.lugar_expedicion',
                    'desembolso.monto',
                    'users.personal as asesor',
                    'desembolso.estado',
                    'solicitud.lapso_capital',
                    'solicitud.tipo_garantia as garantia',
                    'plan_pago.fecha_inicio as fecha_credito',
                    'solicitud.importe_solicitud as importe_prestamo',
                    'plan_pago.fecha_fin as fecha_max_devolucion',
                    'solicitud.nro_cuotas as cuotas',
                    'pago_administrativo.monto as monto_pago_adm',
                ])
                ->first();

            if (!$desembolso) {
                return response()->json(['error' => 'No se encontraron datos para el cliente o plan de pago'], 404);
            }

             // Fetch first cuota
            $cuota = DB::table('cuota')
                ->where('id_plan_pago', $idPlanPago)
                ->select('fecha', 'total')
                ->orderBy('fecha', 'asc')
                ->first();

            if (!$cuota) {
                return response()->json(['error' => 'No se encontraron cuotas para el plan de pago'], 404);
            }

            // Fetch client telephones and addresses
            $telefonos = DB::table('telefono')
                ->where('id_cliente', $idCliente)
                // ->select('tipo', 'numero')
                ->get();

            $direcciones = DB::table('direccion')
                ->where('id_cliente', $idCliente)
                // ->select('tipo', 'descripcion')
                ->get();

            $empresa = DB::table('mi_empresa')
                ->select('nombre', 'direccion', 'telefono', 'email', 'logo')
                ->first();

            if (!$empresa) {
                return response()->json(['error' => 'No se encontraron datos de la empresa'], 500);
            }

            $url = $empresa->logo ?? 'logo_sistema_codesoft.png';
            $logoPath = public_path('img/' . $url);
            if (!file_exists($logoPath)) {
                $url = 'logo_sistema_codesoft.png';
            }

            $data = [
                'desembolso' => $desembolso,
                'telefonos' => $telefonos,
                'direcciones' => $direcciones,
                'usuario' => auth()->user()->name ?? 'Sistema',
                'empresa' => $empresa,
                'url' => $url,
                'fecha_reporte' => now()->format('d/m/Y'),
                'monto_primera_cuota' => number_format($cuota->total, 2, ',', '.'),
                'fecha_primera_cuota' => date('d/m/Y', strtotime($cuota->fecha)),
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            \Log::error('Error fetching client disbursement data: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'No se pudo obtener los datos.'], 500);
        }
    }

    private function generatePDF($data, $url_vista, $nombre_reporte)
    {
        try {
            // Initialize MPDF with optimized settings
            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'letter',
                'orientation' => 'P',
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 10,
                'margin_bottom' => 10,

                
            ]);

            // Set footer with page numbers
            $mpdf->SetHTMLFooter('
                <div style="text-align: center; font-size: 10px; color: #666;">
                    Página {PAGENO} de {nbpg}
                </div>
            ');

            // Render view to HTML
            $html = view($url_vista, $data)->render();

            // Write HTML to PDF
            $mpdf->WriteHTML($html);

            // Output PDF to browser
            $mpdf->Output($nombre_reporte . '.pdf', 'I');

        } catch (\Exception $e) {
            \Log::error('Error generating PDF: ' . $e->getMessage());
            return response()->json(['error' => 'No se pudo generar el PDF.'], 500);
        }
    }

    private function generatePDFHorizontalOficio($data, $url_vista, $nombre_reporte)
    {
        try {
            // Initialize MPDF with landscape orientation
            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4-L', // Set to A4 landscape
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 10,
                'margin_bottom' => 10,
            ]);

            // Set footer with page numbers
            $mpdf->SetHTMLFooter('
                <div style="text-align: center; font-size: 8pt; color: #666;">
                    Página {PAGENO} de {nbpg}
                </div>
            ');

            // Render view to HTML
            $html = view($url_vista, $data)->render();

            // Write HTML to PDF
            $mpdf->WriteHTML($html);

            // Output PDF to browser
            $mpdf->Output($nombre_reporte . '.pdf', 'I');

        } catch (\Exception $e) {
            \Log::error('Error generating PDF: ' . $e->getMessage());
            return response()->json(['error' => 'No se pudo generar el PDF.'], 500);
        }
    }

    public function ListaCuotasPdfCaja(Request $request){
        $informacion=DB::table('solicitud')
        ->join('plan_pago', 'solicitud.id', '=', 'plan_pago.id_solicitud')
        ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
        ->join('users', 'users.id', '=', 'solicitud.id_usuario')
        ->where('plan_pago.id', $request->id_plan_pago)
        ->select('solicitud.id','solicitud.importe_solicitud', 'solicitud.moneda', 'solicitud.lapso_capital', 'solicitud.nro_cuotas',
        'solicitud.tasa', 'solicitud.fecha_desembolso', 'solicitud.fecha_primera_cuota', 'solicitud.destino_prestamo',
        'solicitud.tipo_garantia','solicitud.tipo_desembolso','solicitud.id_cliente', 'solicitud.id_usuario', 'cliente.nombre as cliente',
        'cliente.ci', 'cliente.lugar_expedicion', 'solicitud.monto_pago_adm',
        'users.name as asesor', 'solicitud.estado', 'solicitud.fecha as fecha_solicitud')
        ->get();
       
        $detalles = json_decode($request->detalles, true);

        // Carga la vista HTML para el reporte
        $html = [
            'informacion' => $informacion,
            'detalles' => $detalles,
            'usuario' => auth()->user()->name,
            'fecha_reporte' => now()->format('d/m/Y'),
        ];


        $this->generatePDF($html, 'reporte.reporte_cuotas_plan_caja', 'plan_de_pagos');

    }

    public function getOrdenesReprogramacion(Request $request)
    {
        $criterio = $request->get('criterio');
        $buscar = $request->get('buscar');

        $query = OrdenPagoReprogramacion::select(
                'orden_pago_reprogramaciones.*',
                'cliente.nombre as cliente_nombre',
                'cliente.ci as cliente_ci',
                'plan_pago.id as id_credito_ref'
            )
            ->join('plan_pago', 'orden_pago_reprogramaciones.id_plan_pago_origen', '=', 'plan_pago.id')
            ->join('solicitud', 'plan_pago.id_solicitud', '=', 'solicitud.id')
            ->join('cliente', 'solicitud.id_cliente', '=', 'cliente.id')
            ->whereIn('orden_pago_reprogramaciones.estado', [
                OrdenPagoReprogramacion::ESTADO_POR_PAGAR, 
                // OrdenPagoReprogramacion::ESTADO_PAGADO // Descomenta si quieres ver historial
            ]);

        if (!empty($buscar)) {
            if ($criterio == 'plan_pago.id') {
                $query->where('plan_pago.id', $buscar);
            } else {
                $query->where($criterio, 'like', '%' . $buscar . '%');
            }
        }

        $ordenes = $query->orderBy('orden_pago_reprogramaciones.created_at', 'desc')->get();

        $data = $ordenes->map(function($orden) {
            return [
                'id' => $orden->id,
                'created_at_fmt' => \Carbon\Carbon::parse($orden->created_at)->format('d/m/Y H:i'),
                'cliente_nombre' => $orden->cliente_nombre,
                'id_plan_pago_origen' => $orden->id_plan_pago_origen,
                
                // --- MONTOS CALCULADOS (DEUDA ORIGINAL) ---
                'monto_interes_calculado' => number_format($orden->monto_interes_calculado, 2),
                'monto_mora_calculado' => number_format($orden->monto_mora_calculado, 2),
                
                // --- MONTOS CONDONADOS (LO QUE SE PERDONÓ) ---
                'monto_condonado_interes' => number_format($orden->monto_condonado_interes, 2),
                'monto_condonado_mora' => number_format($orden->monto_condonado_mora, 2),
                
                // --- TOTAL FINAL ---
                'total_a_pagar' => number_format($orden->total_a_pagar, 2),
                
                'motivo_condonacion' => $orden->motivo_condonacion, // Para mostrar en tooltip
                'estado' => $orden->estado,
                'raw_data' => $orden 
            ];
        });

        return response()->json($data);
    }
}
