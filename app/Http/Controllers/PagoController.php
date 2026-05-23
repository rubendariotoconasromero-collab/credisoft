<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use DB;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;

class PagoController extends Controller
{

    public function save(Request $request){
        DB::beginTransaction();

        $id_caja=DB::table('caja')->where('estado', 1)->get()[0]->id;

        try{
           

            $multa_mora = $request->dias_pasados > 0 ? ($request->multa_mora ?? 0) : 0;
            $monto_condonado = $request->monto_condonado ?? 0;
            $datos_pago = [
                'fecha_pago' => $request->fecha_pago,
                'monto_pago' => $request->dias_pasados > 0 
                    ? ($request->monto_pago + ($multa_mora * $request->dias_pasados)) - $monto_condonado 
                    : $request->monto_pago,
                'monto_cuota' => $request->monto_pago,
                'id_usuario' => Auth::id(),
                'id_cuota' => $request->id_cuota,
                'id_caja' => $id_caja,
                'monto_condonado' => $monto_condonado,
                'forma_pago' => $request->forma_pago,
            ];

            if ($request->dias_pasados > 0) {
                $datos_pago['dias_retrasados'] = $request->dias_pasados;
                $datos_pago['multa_dia'] = $multa_mora;
                $datos_pago['multa_total'] = $multa_mora * $request->dias_pasados;
                $datos_pago['motivo_condonacion'] = $request->motivo_condonacion ?? 'no se ingreso motivo';
            }

            DB::table('pago')->insert($datos_pago);
            

            DB::table('cuota')->where('id', $request->id_cuota)->update([
                'estado'=>2,
            ]);

            $no_hay_sin_pagar = DB::table('cuota')
            ->join('plan_pago', 'plan_pago.id', '=', 'cuota.id_plan_pago')
            ->where('cuota.estado', 1)
            ->where('plan_pago.id', $request->id_plan_pago)
            ->doesntExist();

            if($no_hay_sin_pagar){
                DB::table('plan_pago')->where('id', $request->id_plan_pago)->update([
                    'estado'=>2
                ]);
            }

            // Reg. en mov. caja
            DB::table('movimientos_caja')
            ->insertGetId([
                'tipo_movimiento'=>'ingreso',
                'monto'=>$datos_pago['monto_pago'],
                'descripcion'=>'Pago de cuota',
                'fecha'=>now(),
                'id_caja'=>$id_caja,
                'id_usuario'=>Auth::user()->id,
            ]);
            
            DB::commit();
            return ['estado_plan'=>$no_hay_sin_pagar?2:1];
            
        }catch(Exception $e){
            DB::rollback();
        }
    }

    public function getPago(Request $request){
        $pago=DB::table('pago')
        ->join('cuota', 'cuota.id', '=', 'pago.id_cuota')
        ->join('users', 'pago.id_usuario', '=', 'users.id')
        ->select('pago.*', 'users.name as usuario')
        ->where('cuota.id', $request->id_cuota)
        ->get();

        return $pago;
    }
    public function anularPago(Request $request){
        DB::beginTransaction();
        try{
            DB::table('pago')
            ->join('cuota', 'cuota.id', '=', 'pago.id_cuota')
            ->where('pago.id', $request->id_pago)
            ->where('pago.estado', 1)
            ->update([
                'pago.estado'=>0
            ]);
    
            DB::table('cuota')->where('id', $request->id_cuota)->update([
                'estado'=>1
            ]);
    
            $cantidad_pagados=DB::table('plan_pago')
            ->join('cuota', 'cuota.id_plan_pago', '=', 'plan_pago.id')
            ->where('plan_pago.id', $request->id_plan_pago)
            ->where('cuota.estado', 2)
            ->count();
    
            if($cantidad_pagados<$request->nro_cuotas){
                DB::table('plan_pago')
                ->where('plan_pago.id', $request->id_plan_pago)
                ->update([
                    'estado'=>1
                ]);
            }
            DB::commit();
        }catch(Exception $e){
            DB::rollback();
        }

    }

    public function getPagos(Request $request){
        $pagos = DB::table('pago')
        ->join('cuota', 'pago.id_cuota', '=', 'cuota.id')
        ->join('users', 'users.id', '=', 'pago.id_usuario')
        ->join('plan_pago', 'plan_pago.id', '=', 'cuota.id_plan_pago')
        ->join('solicitud', 'plan_pago.id_solicitud', '=', 'solicitud.id')
        ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
        ->select('pago.imagen','pago.id', 'pago.fecha_pago', 'pago.forma_pago', 'pago.monto_pago', 'pago.id_cuota', 'pago.estado',
        'users.name as asesor', 'users.personal as nombre_asesor', 'cliente.nombre as cliente', 'cuota.numero as cuota', 'solicitud.nro_cuotas',
        'plan_pago.id as plan_pago', 'plan_pago.total_pagar as total_pago_credito', 'cuota.saldo_capital', 'cuota.interes')
        ->where('pago.estado', 1)
        ->where($request->opcion, 'LIKE', '%'.$request->buscar.'%')
        ->whereDate('pago.fecha_pago', '>=', $request->fecha_inicial)
        ->whereDate('pago.fecha_pago', '<=', $request->fecha_final)
        ->orderBy('pago.id', 'desc')
        ->paginate(50);

        $totalPagos=DB::table('pago')
        ->join('cuota', 'pago.id_cuota', '=', 'cuota.id')
        ->join('users', 'users.id', '=', 'pago.id_usuario')
        ->join('plan_pago', 'plan_pago.id', '=', 'cuota.id_plan_pago')
        ->join('solicitud', 'plan_pago.id_solicitud', '=', 'solicitud.id')
        ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
        ->select(DB::raw('sum(pago.monto_pago) as totalPagos'))
        ->where($request->opcion, 'LIKE', '%'.$request->buscar.'%')
        ->whereDate('pago.fecha_pago', '>=', $request->fecha_inicial)
        ->whereDate('pago.fecha_pago', '<=', $request->fecha_final)
        ->where('pago.estado', 1)
        ->get()[0]->totalPagos;

        return ['pagos'=>$pagos, 'totalPagos'=>$totalPagos];
        //return $pagos;
    }

    public function getPagosFecha(Request $request){
        $pagos = DB::table('pago')
        ->join('cuota', 'pago.id_cuota', '=', 'cuota.id')
        ->join('users', 'users.id', '=', 'pago.id_usuario')
        ->join('plan_pago', 'plan_pago.id', '=', 'cuota.id_plan_pago')
        ->join('solicitud', 'plan_pago.id_solicitud', '=', 'solicitud.id')
        ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
        ->select('pago.imagen','pago.id', 'pago.fecha_pago', 'pago.forma_pago','pago.monto_pago', 'pago.id_cuota', 'pago.estado',
        'users.name as asesor', 'cliente.nombre as cliente', 'cuota.numero as cuota', 'solicitud.nro_cuotas',
        'plan_pago.id as plan_pago', 'plan_pago.total_pagar as total_pago_credito', 'cuota.saldo_capital', 'cuota.interes')
        ->where('pago.estado', 1)
        // ->where($request->opcion, 'LIKE', '%'.$request->buscar.'%')
        ->whereDate('pago.fecha_pago', '>=', $request->fecha_inicial)
        ->whereDate('pago.fecha_pago', '<=', $request->fecha_final)
        ->orderBy('pago.id', 'desc')
        ->paginate(50);

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

        return ['pagos'=>$pagos, 'totalPagos'=>$totalPagos];
        //return $pagos;
    }

    public function getPagosListaCuotas(Request $request){

        
        $fechaActual = Carbon::now(); // Obtiene la fecha y hora actual
    
        $query = DB::table('cuota')
        ->join('plan_pago', 'cuota.id_plan_pago', '=', 'plan_pago.id')
        ->join('solicitud', 'plan_pago.id_solicitud', '=', 'solicitud.id')
        ->join('cliente', 'solicitud.id_cliente', '=', 'cliente.id')
        ->join('users', 'solicitud.id_usuario', '=', 'users.id')
        ->select(

            DB::raw('MIN(cuota.estado) as estado'),
            DB::raw('MIN(cuota.id) as id'), // Selecciona el mínimo ID de cuota
            DB::raw('MIN(cuota.numero) as cuota'),

            DB::raw('MIN(cliente.nombre) as cliente'),
            DB::raw('MIN(solicitud.nro_cuotas) as nro_cuotas'),

            'plan_pago.id as plan_pago',
            'plan_pago.total_pagar as total_pago_credito',
            'plan_pago.id_plan_aux',
            DB::raw('MIN(cuota.fecha) as fecha_a_pagar'),
            DB::raw('MIN(cuota.total) as monto_a_pagar'),
            DB::raw('MIN(cuota.saldo_capital) as saldo_capital'),
            DB::raw('MIN(cuota.interes) as interes'),
            DB::raw('MIN(cuota.capital) as capital'),
            DB::raw('MIN(cuota.ahorro) as ahorro'),
            DB::raw('MIN(cuota.seguro) as seguro'),

            DB::raw('MIN(users.name) as asesor'),
            DB::raw('MIN(users.personal) as nombre_asesor'),

            DB::raw('MIN(DATEDIFF(NOW(), cuota.fecha)) as dias_pasados') // Calcula la diferencia en días
        )
        ->where('cuota.estado', 1)
        ->where('cuota.amortizado', 0)
        ->whereIn('cuota.id', function ($query) {
            $query->select(DB::raw('MIN(c.id)'))
                ->from('cuota as c')
                ->join('plan_pago as pp', 'c.id_plan_pago', '=', 'pp.id')
                ->where('c.estado', 1)
                ->where('c.amortizado', 0)
                ->groupBy('pp.id');
        });

        if ($request->buscar != '') {
            if($request->opcion=='cuota.id_plan_pago'){
                // en esta seccion 
                // $query->where($request->opcion, 'LIKE', '%'.$request->buscar.'%');
                // Aquí agregamos la condición adicional para verificar id_plan_aux
                $query->where(function($subquery) use ($request) {
                    $subquery->where(function($query) use ($request) {
                        $query->where('plan_pago.id_plan_aux', '>', 0)
                            ->where('plan_pago.id_plan_aux', 'LIKE', '%'.$request->buscar.'%');
                    })->orWhere(function($query) use ($request) {
                        $query->where('plan_pago.id_plan_aux', '=', 0)
                            ->where('cuota.id_plan_pago', 'LIKE', '%'.$request->buscar.'%');
                    });
                });
            }else{
                $query->where($request->opcion, 'LIKE', '%'.$request->buscar.'%');
            }
        }

        $query->whereDate('cuota.fecha', '>=', $request->fecha_inicial)
              ->whereDate('cuota.fecha', '<=', $request->fecha_final);
              
        // Aplicar filtro según el estado de la cuota
        if ($request->estado_cuota == 'con_mora') {
            $query->havingRaw('dias_pasados > 0');
        } elseif ($request->estado_cuota == 'sin_mora') {
            $query->havingRaw('dias_pasados <= 0');
        }


        $query->groupBy('plan_pago.id', 'plan_pago.total_pagar', 'plan_pago.id_plan_aux') // Agrupa por plan de pago
            ->orderBy('fecha_a_pagar', 'asc'); // Ordena por fecha ascendente

        $pagos_sumatorias = $query->get();

        // Calcular sumatorias a partir de los resultados
        $totalCuotas = $pagos_sumatorias->sum('monto_a_pagar');
        $totalCuotasMora = $pagos_sumatorias->where('dias_pasados', '>', 0)->sum('monto_a_pagar');
        $totalCuotasSinMora = $pagos_sumatorias->where('dias_pasados', '<=', 0)->sum('monto_a_pagar');

        $pagos = $query->paginate(100);

        return [
            'pagos' => $pagos,
            'totalCuotas' => $totalCuotas,
            'totalCuotasMora' => $totalCuotasMora,
            'totalCuotasSinMora' => $totalCuotasSinMora
        ];

    }

    public function getPagosListaCuotasTotal(Request $request){
        $pagos = DB::table('cuota')
        ->join('plan_pago', 'cuota.id_plan_pago', '=', 'plan_pago.id')
        ->join('solicitud', 'plan_pago.id_solicitud', '=', 'solicitud.id')
        ->join('cliente', 'solicitud.id_cliente', '=', 'cliente.id')
        ->join('users', 'solicitud.id_usuario', '=', 'users.id')
        ->select('cuota.estado','cuota.id', 'cuota.numero as cuota', 'cliente.nombre as cliente', 'solicitud.nro_cuotas', 'plan_pago.id as plan_pago', 'cuota.fecha as fecha_a_pagar', 'cuota.total as monto_a_pagar', 'users.name as asesor',
        DB::raw('DATEDIFF(NOW(), cuota.fecha) as dias_pasados'), 'plan_pago.total_pagar as total_pago_credito', 'cuota.interes', 'cuota.saldo_capital',
        'users.personal as nombre_asesor')
        ->where('cuota.estado', 1)
        ->where('cuota.amortizado', 0)
        ->where($request->opcion, 'LIKE', '%'.$request->buscar.'%')
        // ->whereDate('cuota.fecha', '>=', $request->fecha_inicial)
        // ->whereDate('cuota.fecha', '<=', $request->fecha_final)
        ->orderBy('cuota.fecha', 'asc')
        ->paginate(100);
        return $pagos;
    }

    public function getPagosListaAnulados(Request $request){
        $pagos = DB::table('pago')
        ->join('cuota', 'pago.id_cuota', '=', 'cuota.id')
        ->join('users', 'users.id', '=', 'pago.id_usuario')
        ->join('plan_pago', 'plan_pago.id', '=', 'cuota.id_plan_pago')
        ->join('solicitud', 'plan_pago.id_solicitud', '=', 'solicitud.id')
        ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
        ->select('pago.id', 'pago.fecha_pago', 'pago.forma_pago', 'pago.monto_pago', 'pago.id_cuota', 'pago.estado',
        'users.name as asesor', 'cliente.nombre as cliente', 'cuota.numero as cuota', 'solicitud.nro_cuotas',
        'plan_pago.id as plan_pago')
        ->where('pago.estado', 0)
        ->where($request->opcion, 'LIKE', '%'.$request->buscar.'%')
        ->whereDate('pago.fecha_pago', '>=', $request->fecha_inicial)
        ->whereDate('pago.fecha_pago', '<=', $request->fecha_final)
        ->paginate(100);

        $totalPagosAnuladosAux = DB::table('pago')
        ->join('cuota', 'pago.id_cuota', '=', 'cuota.id')
        ->join('users', 'users.id', '=', 'pago.id_usuario')
        ->join('plan_pago', 'plan_pago.id', '=', 'cuota.id_plan_pago')
        ->join('solicitud', 'plan_pago.id_solicitud', '=', 'solicitud.id')
        ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
        ->select('pago.id', 'pago.fecha_pago', 'pago.forma_pago', 'pago.monto_pago', 'pago.id_cuota', 'pago.estado',
        'users.name as asesor', 'cliente.nombre as cliente', 'cuota.numero as cuota', 'solicitud.nro_cuotas',
        'plan_pago.id as plan_pago')
        ->where('pago.estado', 0)
        ->where($request->opcion, 'LIKE', '%'.$request->buscar.'%')
        ->whereDate('pago.fecha_pago', '>=', $request->fecha_inicial)
        ->whereDate('pago.fecha_pago', '<=', $request->fecha_final)
        ->get();

        $totalPagosAnulados=$totalPagosAnuladosAux->sum('monto_pago');

        return [
            'pagos_anulados'=>$pagos,
            'totalPagosAnulados'=>$totalPagosAnulados,
        ];
      
    }

    public function getPagosListaCuotasFecha(Request $request){
        if($request->buscar==''){
            $fechaActual = Carbon::now(); // Obtiene la fecha y hora actual
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
                'users.personal as nombre_asesor',
                'users.name as asesor',
                DB::raw('DATEDIFF(NOW(), cuota.fecha) as dias_pasados') // Calcula la diferencia en días
            )
            ->where('cuota.estado', 1)
            ->where('cuota.amortizado', 0)
            ->whereIn('cuota.id', function ($query) {
                $query->select(DB::raw('MIN(c.id)'))
                    ->from('cuota as c')
                    ->join('plan_pago as pp', 'c.id_plan_pago', '=', 'pp.id')
                    ->where('c.estado', 1)
                    ->where('c.amortizado', 0)

                    ->groupBy('pp.id');
            })
            ->orWhere(function ($query) use ($fechaActual) {
                $query->whereDate('cuota.fecha', '<=', $fechaActual->toDateString()) // Comparación de fecha
                ->where('cuota.amortizado', 0)
                    ->where('cuota.estado', 1);
            })
    
            ->orderBy('cuota.fecha', 'asc') // Ordena por fecha ascendente
            ->paginate(100);
        }else{
            $fechaActual = Carbon::now(); // Obtiene la fecha y hora actual
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
                'users.personal as nombre_asesor',
                'users.name as asesor',

                DB::raw('DATEDIFF(NOW(), cuota.fecha) as dias_pasados') // Calcula la diferencia en días
            )
            ->where('cuota.estado', 1)
            ->where('cuota.amortizado', 0)
            // ->where($request->opcion, 'LIKE', '%'.$request->buscar.'%')// esta linea
            ->whereDate('cuota.fecha', '>=', $request->fecha_inicial)
            ->whereDate('cuota.fecha', '<=', $request->fecha_final)
            
            ->whereIn('cuota.id', function ($query) {
                $query->select(DB::raw('MIN(c.id)'))
                    ->from('cuota as c')
                    ->join('plan_pago as pp', 'c.id_plan_pago', '=', 'pp.id')
                    ->where('c.estado', 1)
                    ->where('c.amortizado', 0)
                    ->groupBy('pp.id');
            })
            ->orWhere(function ($query) use ($fechaActual, $request) {
                $query->where($request->opcion, 'LIKE', '%'.$request->buscar.'%')
                    ->whereDate('cuota.fecha', '<=', $fechaActual->toDateString()) // Comparación de fecha
                    ->where('cuota.amortizado', 0)
                    ->where('cuota.estado', 1);
            })
            ->orderBy('cuota.fecha', 'asc') // Ordena por fecha ascendente
            ->paginate(15);
        }
        return $pagos;
    }

    public function getPagosListaCuotasTotalFecha(Request $request){
        $pagos = DB::table('cuota')
        ->join('plan_pago', 'cuota.id_plan_pago', '=', 'plan_pago.id')
        ->join('solicitud', 'plan_pago.id_solicitud', '=', 'solicitud.id')
        ->join('cliente', 'solicitud.id_cliente', '=', 'cliente.id')
        ->join('users', 'solicitud.id_usuario', '=', 'users.id')
        ->select('cuota.estado','cuota.id', 'cuota.numero as cuota', 'cliente.nombre as cliente', 'solicitud.nro_cuotas', 'plan_pago.id as plan_pago', 'cuota.fecha as fecha_a_pagar', 'cuota.total as monto_a_pagar', 'users.name as asesor',
        DB::raw('DATEDIFF(NOW(), cuota.fecha) as dias_pasados'), 'plan_pago.total_pagar as total_pago_credito', 'cuota.interes', 'cuota.saldo_capital',
        'users.personal as nombre_asesor')
        ->where('cuota.estado', 1)
        ->where('cuota.amortizado', 0)

        // ->where($request->opcion, 'LIKE', '%'.$request->buscar.'%')
        ->whereDate('cuota.fecha', '>=', $request->fecha_inicial)
        ->whereDate('cuota.fecha', '<=', $request->fecha_final)
        ->orderBy('cuota.fecha', 'asc')
        ->paginate(100);
        return $pagos;
    }

    public function getPagosListaAnuladosFecha(Request $request){
        $pagos = DB::table('pago')
        ->join('cuota', 'pago.id_cuota', '=', 'cuota.id')
        ->join('users', 'users.id', '=', 'pago.id_usuario')
        ->join('plan_pago', 'plan_pago.id', '=', 'cuota.id_plan_pago')
        ->join('solicitud', 'plan_pago.id_solicitud', '=', 'solicitud.id')
        ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
        ->select('pago.id', 'pago.forma_pago', 'pago.fecha_pago', 'pago.monto_pago', 'pago.id_cuota', 'pago.estado',
        'users.name as asesor', 'cliente.nombre as cliente', 'cuota.numero as cuota', 'solicitud.nro_cuotas',
        'plan_pago.id as plan_pago')
        ->where('pago.estado', 0)
        // ->where($request->opcion, 'LIKE', '%'.$request->buscar.'%')
        ->whereDate('pago.fecha_pago', '>=', $request->fecha_inicial)
        ->whereDate('pago.fecha_pago', '<=', $request->fecha_final)
        ->paginate(15);

        return $pagos;
    }

    public function generarTicketPago($codigo_transaccion)
    {
        // 1. Buscamos TODOS los pagos asociados a ese código de transacción
        $pagos = DB::table('pago')
            ->join('cuota', 'cuota.id', '=', 'pago.id_cuota')
            ->join('plan_pago', 'plan_pago.id', '=', 'cuota.id_plan_pago')
            ->join('solicitud', 'solicitud.id', '=', 'plan_pago.id_solicitud')
            ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
            ->join('users', 'users.id', '=', 'pago.id_usuario')
            ->select(
                'pago.monto_pago', 'pago.pago_capital', 'pago.pago_interes', 'pago.pago_mora', 
                'pago.monto_condonado', 'pago.forma_pago', 'pago.fecha_pago',
                'cliente.nombre as cliente_nombre', 'cliente.ci',
                'cuota.numero as nro_cuota', 'solicitud.nro_cuotas as cantidad_cuotas',
                'plan_pago.id as codigo_plan', 'users.name as cajero',
                'cuota.estado as estado_cuota'
            )
            ->where('pago.codigo_transaccion', $codigo_transaccion)
            ->where('pago.estado', 1) // Solo pagos no anulados
            ->get();

        if ($pagos->isEmpty()) {
            return response("Error: Transacción no encontrada o anulada.", 404);
        }

        // 2. Agrupamos los datos comunes
        $info_base = $pagos->first(); // Tomamos el primer registro para datos generales
        $total_pagado = $pagos->sum('monto_pago');
        $total_mora = $pagos->sum('pago_mora');
        $total_condonado = $pagos->sum('monto_condonado');
        
        // Unimos los números de cuotas en un texto (Ej: "1, 2, 3")
        $numeros_cuotas = $pagos->pluck('nro_cuota')->toArray();
        $cuotas_texto = implode(', ', $numeros_cuotas);

        // 3. Calculamos el saldo pendiente actual del plan (Lógica de listarAmortizaciones)
        $id_plan = $info_base->codigo_plan;
        
        // Obtenemos el plan y todas sus cuotas
        $plan = DB::table('plan_pago')->where('id', $id_plan)->first();
        $todas_cuotas = DB::table('cuota')
            ->where('id_plan_pago', $id_plan)
            ->orderBy('numero', 'asc')
            ->get();

        $cuotas_pendientes_obj = $todas_cuotas->filter(fn($c) => in_array($c->estado, [1, 3]));
        $firstUnpaidIndex = $todas_cuotas->search(fn($c) => in_array($c->estado, [1, 3]));

        // --- DATOS DEL PRÓXIMO PAGO ---
        $proxima_cuota = $cuotas_pendientes_obj->first();
        $fecha_proximo_pago = $proxima_cuota ? Carbon::parse($proxima_cuota->fecha)->format('d/m/Y') : 'CRÉDITO FINALIZADO';
        
        // El monto a pagar de la próxima cuota es su total original menos lo ya pagado (Capital + Interés + Mora)
        $monto_proximo_pago = $proxima_cuota 
            ? ($proxima_cuota->total - ($proxima_cuota->capital_pagado + $proxima_cuota->interes_pagado + $proxima_cuota->mora_pagada))
            : 0;
        // ------------------------------

        // --- CÁLCULO DE CAPITAL PENDIENTE (Basado en el campo saldo_capital de la última procesada) ---
        $ultimaCuotaProcesada = $todas_cuotas->last(fn($c) => in_array($c->estado, [2, 3]));

        if ($ultimaCuotaProcesada) {
            $capital_restante_cuota_actual = max(0, (float)$ultimaCuotaProcesada->capital - (float)$ultimaCuotaProcesada->capital_pagado);
            $saldo_capital_pendiente = (float)$ultimaCuotaProcesada->saldo_capital + $capital_restante_cuota_actual;
        } else {
            $saldo_capital_pendiente = (float)$todas_cuotas->sum('capital');
        }

        // Definimos la base para el cálculo de interés moratorio (Multa porcentual)
        $lastPaid = $todas_cuotas->last(fn($c) => $c->estado == 2);
        $saldoCapitalMora = $lastPaid ? (float)$lastPaid->saldo_capital : (float)$todas_cuotas->sum('capital');
        // --------------------------------------------------------------------------------------------

        $saldo_interes_pendiente = 0;
        $saldo_mora_pendiente = 0;

        $hoy = Carbon::now()->startOfDay();

        foreach ($todas_cuotas as $index => $cuota) {
            if (!in_array($cuota->estado, [1, 3])) continue;

            // NORMALIZACIÓN DE FECHAS
            $fechaCuota = Carbon::parse($cuota->fecha)->startOfDay();
            $fechaInicioCuota = $index === 0
                ? Carbon::parse($plan->fecha_inicio)->startOfDay()
                : Carbon::parse($todas_cuotas[$index - 1]->fecha)->startOfDay();

            $diasPeriodoCuota = max(1, $fechaInicioCuota->diffInDays($fechaCuota, false));
            $fechaCalculo = $hoy;
            
            // DÍAS TRANSCURRIDOS (Para interés normal)
            if ($index === $firstUnpaidIndex) {
                $diasDesdeInicio = $fechaInicioCuota->diffInDays($fechaCalculo, false);
                $diasTranscurridosNormales = max(0, min($diasDesdeInicio, $diasPeriodoCuota));
                
                // Cálculo de dias_mora_cobro (Lógica SQL CASE)
                $fechaReferenciaMora = $plan->fecha_ultima_amortizacion 
                    ? Carbon::parse($plan->fecha_ultima_amortizacion)->startOfDay() 
                    : $fechaCuota;
                $maxFechaMora = $fechaCuota->gt($fechaReferenciaMora) ? $fechaCuota : $fechaReferenciaMora;
                $dias_mora_cobro = $hoy->gt($maxFechaMora) ? $hoy->diffInDays($maxFechaMora) : 0;
            } else {
                if ($fechaCalculo->isAfter($fechaCuota) || $fechaCalculo->isSameDay($fechaCuota)) {
                    $diasTranscurridosNormales = $diasPeriodoCuota;
                } else {
                    $diasDesdeInicio = $fechaInicioCuota->diffInDays($fechaCalculo, false);
                    $diasTranscurridosNormales = max(0, min($diasDesdeInicio, $diasPeriodoCuota));
                }
                $dias_mora_cobro = 0;
            }

            // DÍAS DE RETRASO (Informativo para Mora)
            $diasRetrasoCuota = $fechaCalculo->isAfter($fechaCuota) ? $fechaCuota->diffInDays($fechaCalculo, false) : 0;

            // INTERÉS DIARIO Y DEVENGADO
            $interesPorDiaNormal = ($diasPeriodoCuota > 0) ? ($cuota->interes / $diasPeriodoCuota) : 0;
            $interesDevengadoBruto = $interesPorDiaNormal * $diasTranscurridosNormales;

            // INTERÉS MORATORIO
            if ($index === $firstUnpaidIndex && $diasRetrasoCuota > 0) {
                $montoBaseMensual = $saldoCapitalMora * ((float)$plan->tasa / 100);
                $lapso = trim(strtolower($plan->lapso_capital));
                $factorPeriodo = 1; $diasDivisor = 30;

                if ($lapso == 'quincenal') { $factorPeriodo = 2; $diasDivisor = 15; }
                elseif ($lapso == 'semanal') { $factorPeriodo = 4; $diasDivisor = 7; }

                $interesMoratorioBruto = ($montoBaseMensual / $factorPeriodo) / $diasDivisor * $diasRetrasoCuota;
            } else {
                $interesMoratorioBruto = 0;
            }

            // MULTA FIJA
            $moraFijaBruta = ($dias_mora_cobro > 0) ? ($dias_mora_cobro * 3) : 0;

            // CÁLCULO DE RESTANTES (NETOS) PARA INTERÉS Y MULTA
            $int_pagado = (float)$cuota->interes_pagado;
            $mora_pagada = (float)$cuota->mora_pagada;

            $intDevengadoRestante = max(0, $interesDevengadoBruto - $int_pagado);
            $excesoInt = max(0, $int_pagado - $interesDevengadoBruto);
            $intMoratorioRestante = max(0, $interesMoratorioBruto - $excesoInt);
            $moraFijaRestante = max(0, $moraFijaBruta - $mora_pagada);

            // ACUMULAR PARA EL RESUMEN DEL RECIBO (El capital ya se calculó fuera del bucle)
            $saldo_interes_pendiente += ($intDevengadoRestante + $intMoratorioRestante);
            $saldo_mora_pendiente += $moraFijaRestante;
        }

        $empresa = DB::table('mi_empresa')->first();

        // 4. Generamos el PDF
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $dompdf = new Dompdf($options);

        $html = view('reporte.ticket_pago', [
            'codigo_transaccion' => $codigo_transaccion,
            'pagos' => $pagos,
            'info_base' => $info_base,
            'total_pagado' => $total_pagado,
            'total_mora' => $total_mora,
            'total_condonado' => $total_condonado,
            'cuotas_texto' => $cuotas_texto,
            'empresa' => $empresa,
            'fecha_proximo_pago' => $fecha_proximo_pago,
            'monto_proximo_pago' => $monto_proximo_pago,
            'saldo_pendientes' => [
                'capital' => $saldo_capital_pendiente,
                'interes' => $saldo_interes_pendiente,
                'mora' => $saldo_mora_pendiente,
                'total' => $saldo_capital_pendiente + $saldo_interes_pendiente + $saldo_mora_pendiente
            ]
        ])->render();

        $dompdf->loadHtml($html);
        $dompdf->render();

        return response($dompdf->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="Recibo_'.$codigo_transaccion.'.pdf"');
    }

    public function imagenRespaldo(Request $request){
        DB::beginTransaction();
        try{
            
            // imagen
            $imagen_logo = $request->imagen;
            $nombreArchivo='';
            if(!empty($imagen_logo)){
                // Obtiene el archivo de imagen del formulario
                $imagen = $imagen_logo;
                // Genera un nombre único para el archivo de imagen
                $nombreArchivo = uniqid() . '.' . $imagen->getClientOriginalExtension();
                // Almacena la imagen en la carpeta public/img
                $imagen->move(public_path('img/pago'), $nombreArchivo);
            }

            // Verifica si $nombreArchivo está definido y no está vacío antes de actualizar 'logo'
            if (isset($nombreArchivo) && !empty($nombreArchivo)) {
                DB::table('pago')->where('id', $request->id_pago)->update([
                    'imagen'=>$nombreArchivo,
                ]);
                $pago=DB::table('pago')->select('imagen')->where('id', $request->id_pago)->get();
                DB::commit();
            }

            //
            return ['imagen'=>$pago[0]->imagen];
        }catch(Exception $e){
            DB::rollback();
        }
    }


    public function pagarCuotas(Request $request)
    {
        $id_plan_pago = $request->input('id_plan_pago');
        $cuotas = $request->input('cuotas');
        
        $bolsa_efectivo = (float) $request->input('monto_recibido'); 
        $bolsa_condonacion_interes = (float) $request->input('condonacion_interes');
        $bolsa_condonacion_mora = (float) $request->input('condonacion_mora');
        
        $forma_pago = $request->input('forma_pago');
        $fecha_pago = $request->input('fecha_pago');

        if (empty($cuotas)) {
            return response()->json(['error' => 'No se seleccionaron cuotas para pagar'], 400);
        }

        DB::beginTransaction();

        try {
            $id_caja = DB::table('caja')->where('estado', 1)->first()->id;
            
            $cuotaIds = array_column($cuotas, 'id_cuota');
            $dbCuotas = DB::table('cuota')
                ->where('id_plan_pago', $id_plan_pago)
                ->whereIn('id', $cuotaIds)
                ->orderBy('numero', 'asc')
                ->get();

            $firstUnpaidCuota = DB::table('cuota')
                ->where('id_plan_pago', $id_plan_pago)
                ->whereIn('estado', [1, 3]) 
                ->orderBy('numero', 'asc')
                ->first();

            if (!$firstUnpaidCuota || $cuotaIds[0] != $firstUnpaidCuota->id) {
                throw new \Exception('Las cuotas deben pagarse en orden empezando por la primera pendiente o parcial.');
            }

            $codigo_transaccion = 'TRX-' . time() . '-' . Auth::id() . '-' . rand(100, 999);
            $alguna_cuota_pagada_totalmente = false; // <-- Control para el reloj de mora

            // ==========================================
            // ESCUDO DE SEGURIDAD: PREVENIR COBRO EXCESIVO
            // ==========================================
            $deuda_total_seleccionada = 0;
            foreach ($cuotas as $c) {
                $deuda_total_seleccionada += max(0, $c['mora_adeudada']) + max(0, $c['interes_adeudado']) + max(0, $c['capital_adeudado']);
            }
            
            // Calculamos el máximo dinero físico que deberíamos aceptar
            $deuda_liquida_maxima = $deuda_total_seleccionada - $bolsa_condonacion_mora - $bolsa_condonacion_interes;

            // Tolerancia de 1 centavo por redondeos flotantes
            if (round($bolsa_efectivo, 2) > round($deuda_liquida_maxima, 2) + 0.01) {
                throw new \Exception('Alerta de Seguridad: El monto recibido (' . $bolsa_efectivo . ' Bs) supera la deuda total líquida de las cuotas seleccionadas (' . round($deuda_liquida_maxima, 2) . ' Bs).');
            }
            // ==========================================

            foreach ($cuotas as $cuotaData) {
                if ($bolsa_efectivo <= 0 && $bolsa_condonacion_interes <= 0 && $bolsa_condonacion_mora <= 0) {
                    break; 
                }

                $id_cuota = $cuotaData['id_cuota'];
                $dbCuota = $dbCuotas->firstWhere('id', $id_cuota);

                $deuda_mora = max(0, $cuotaData['mora_adeudada']);
                $deuda_interes = max(0, $cuotaData['interes_adeudado']);
                $deuda_capital = max(0, $cuotaData['capital_adeudado']);

                $pago_mora = 0; $pago_interes = 0; $pago_capital = 0;
                $condonado_mora_cuota = 0; $condonado_interes_cuota = 0;

                // --- 1. APLICAMOS CONDONACIONES ---
                if ($bolsa_condonacion_mora > 0 && $deuda_mora > 0) {
                    $aplicar = min($bolsa_condonacion_mora, $deuda_mora);
                    $condonado_mora_cuota = $aplicar;
                    $deuda_mora -= $aplicar;
                    $bolsa_condonacion_mora -= $aplicar;
                }

                if ($bolsa_condonacion_interes > 0 && $deuda_interes > 0) {
                    $aplicar = min($bolsa_condonacion_interes, $deuda_interes);
                    $condonado_interes_cuota = $aplicar;
                    $deuda_interes -= $aplicar;
                    $bolsa_condonacion_interes -= $aplicar;
                }

                // --- 2. CASCADA DEL EFECTIVO ---
                if ($bolsa_efectivo > 0 && $deuda_mora > 0) {
                    $aplicar = min($bolsa_efectivo, $deuda_mora);
                    $pago_mora = $aplicar;
                    $deuda_mora -= $aplicar;
                    $bolsa_efectivo -= $aplicar;
                }

                if ($bolsa_efectivo > 0 && $deuda_interes > 0) {
                    $aplicar = min($bolsa_efectivo, $deuda_interes);
                    $pago_interes = $aplicar;
                    $deuda_interes -= $aplicar;
                    $bolsa_efectivo -= $aplicar;
                }

                if ($bolsa_efectivo > 0 && $deuda_capital > 0) {
                    $aplicar = min($bolsa_efectivo, $deuda_capital);
                    $pago_capital = $aplicar;
                    $deuda_capital -= $aplicar;
                    $bolsa_efectivo -= $aplicar;
                }

                $total_pagado_esta_cuota = $pago_mora + $pago_interes + $pago_capital;
                $total_condonado_esta_cuota = $condonado_mora_cuota + $condonado_interes_cuota;

                // --- 3. REGISTRO EN BD ---
                if ($total_pagado_esta_cuota > 0 || $total_condonado_esta_cuota > 0) {
                    
                    $motivos = [];
                    if ($condonado_mora_cuota > 0) $motivos[] = "Mora: " . $request->input('motivo_condonacion_mora');
                    if ($condonado_interes_cuota > 0) $motivos[] = "Interés: " . $request->input('motivo_condonacion_interes');

                    DB::table('pago')->insert([
                        'codigo_transaccion' => $codigo_transaccion,
                        'fecha_pago' => $fecha_pago,
                        'monto_pago' => $total_pagado_esta_cuota,
                        'pago_capital' => $pago_capital,
                        'pago_interes' => $pago_interes,
                        'pago_mora' => $pago_mora,
                        'monto_condonado' => $total_condonado_esta_cuota,
                        'monto_condonado_interes' => $condonado_interes_cuota,
                        'monto_condonado_mora' => $condonado_mora_cuota,
                        'motivo_condonacion' => implode(' | ', $motivos),
                        'forma_pago' => $forma_pago,
                        'estado' => 1,
                        'id_usuario' => Auth::id(),
                        'id_cuota' => $id_cuota,
                        'id_caja' => $id_caja,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    $nuevo_capital_pagado = $dbCuota->capital_pagado + $pago_capital;
                    $nuevo_interes_pagado = $dbCuota->interes_pagado + $pago_interes + $condonado_interes_cuota;
                    $nueva_mora_pagada = $dbCuota->mora_pagada + $pago_mora + $condonado_mora_cuota;

                    // CORRECCIÓN: Ahora evalúa si REALMENTE la deuda restante es 0
                    $estado_cuota = ($deuda_mora <= 0 && $deuda_interes <= 0 && $deuda_capital <= 0) ? 2 : 3;

                    if ($estado_cuota == 2) {
                        $alguna_cuota_pagada_totalmente = true;
                    }

                    DB::table('cuota')->where('id', $id_cuota)->update([
                        'capital_pagado' => $nuevo_capital_pagado,
                        'interes_pagado' => $nuevo_interes_pagado,
                        'mora_pagada' => $nueva_mora_pagada,
                        'estado' => $estado_cuota,
                        'updated_at' => now()
                    ]);

                    if ($total_pagado_esta_cuota > 0) {
                        DB::table('movimientos_caja')->insert([
                            'tipo_movimiento' => 'ingreso',
                            'monto' => $total_pagado_esta_cuota,
                            'descripcion' => 'Pago ' . ($estado_cuota == 3 ? 'Parcial' : 'Total') . ' cuota ' . $dbCuota->numero . ' (' . $codigo_transaccion . ')',
                            'fecha' => now(),
                            'id_caja' => $id_caja,
                            'id_usuario' => Auth::id(),
                        ]);
                    }
                }
            }

            $no_hay_sin_pagar = DB::table('cuota')
                ->where('id_plan_pago', $id_plan_pago)
                ->whereIn('estado', [1, 3])
                ->doesntExist();

            $datos_actualizar_plan = ['estado' => $no_hay_sin_pagar ? 2 : 1];

            // CORRECCIÓN: SOLO reseteamos el reloj de mora si se logró pagar por completo la cuota
            if ($alguna_cuota_pagada_totalmente) {
                $datos_actualizar_plan['fecha_ultima_amortizacion'] = now();
            }

            DB::table('plan_pago')->where('id', $id_plan_pago)->update($datos_actualizar_plan);

            DB::commit();
            
            return [
                'estado_plan' => $no_hay_sin_pagar ? 2 : 1,
                'codigo_transaccion' => $codigo_transaccion 
            ];

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    
}
