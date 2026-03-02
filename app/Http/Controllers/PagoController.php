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

    public function generarTicketPago(Request $request){
        

        $informacion=DB::table('pago')
        ->join('cuota', 'cuota.id', '=', 'pago.id_cuota')
        ->join('plan_pago', 'plan_pago.id', '=', 'cuota.id_plan_pago')
        ->join('solicitud', 'solicitud.id', '=', 'plan_pago.id_solicitud')
        ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
        ->select('pago.monto_pago', 'pago.forma_pago', 'pago.fecha_pago', 'cliente.nombre as nombre_cliente'
        ,'cuota.numero as nro_cuota', 'solicitud.nro_cuotas as cantidad_cuotas', 'plan_pago.id as codigo_plan'
        ,'cuota.saldo_capital', 'pago.multa_total as multa')
        ->where('pago.id', $request->id_pago)
        ->get();
       


        // Crea una instancia de Dompdf
        $dompdf = new Dompdf();

        // Opciones de configuración de Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true); // Habilita el parser HTML5
        $options->set('isPhpEnabled', true); // Habilita la ejecución de código PHP en la vista (si es necesario)
        $dompdf->setOptions($options);

        // Carga la vista HTML para el reporte
        $html = view('reporte.ticket_pago', [
            'informacion' => $informacion,
        ])->render();

        // Carga el contenido HTML en Dompdf
        $dompdf->loadHtml($html);

        // Renderiza el PDF (esto puede tomar un tiempo si el contenido es grande)
        $dompdf->render();

        // Obtén el contenido del PDF como una cadena
        

        // Establece las cabeceras para mostrar el PDF en una nueva pestaña
        return response($dompdf->output())

        ->header('Content-Type', 'application/pdf')
        ->header('Content-Disposition', 'inline; filename="simulacion_plan_pago.pdf"');

        // Muestra el contenido del PDF
        //echo $output;
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

        if (empty($cuotas)) {
            return response()->json(['error' => 'No se seleccionaron cuotas para pagar'], 400);
        }

        DB::beginTransaction();

        try {
            $id_caja = DB::table('caja')->where('estado', 1)->first()->id;

            // --- VALIDACIONES (Mismo código que tenías) ---
            $cuotaIds = array_column($cuotas, 'id_cuota');
            $dbCuotas = DB::table('cuota')
                ->where('id_plan_pago', $id_plan_pago)
                ->whereIn('id', $cuotaIds)
                ->orderBy('numero', 'asc')
                ->get();

            $firstUnpaidCuota = DB::table('cuota')
                ->where('id_plan_pago', $id_plan_pago)
                ->where('estado', 1)
                ->orderBy('numero', 'asc')
                ->first();

            if (!$firstUnpaidCuota || $cuotaIds[0] != $firstUnpaidCuota->id) {
                throw new \Exception('Las cuotas seleccionadas deben comenzar con la primera cuota impaga.');
            }

            $previousNumero = $firstUnpaidCuota->numero - 1;
            foreach ($dbCuotas as $index => $dbCuota) {
                if ($index > 0 && $dbCuota->numero != $previousNumero + 1) {
                    throw new \Exception('Las cuotas seleccionadas deben ser correlativas.');
                }
                if ($dbCuota->estado != 1) {
                    throw new \Exception('Solo se pueden pagar cuotas con estado "Por pagar".');
                }
                $previousNumero = $dbCuota->numero;
            }
            // --- FIN VALIDACIONES ---


            // 1. GENERAMOS EL CÓDIGO ÚNICO DE TRANSACCIÓN PARA ESTE GRUPO
            // Ejemplo formato: TRX-Time-UserID-Random (ej: TRX-1698777-1-450)
            $codigo_transaccion = 'TRX-' . time() . '-' . Auth::id() . '-' . rand(100, 999);


            // Process each cuota payment
            foreach ($cuotas as $cuotaData) {
                $id_cuota = $cuotaData['id_cuota'];
                $cuota = $dbCuotas->firstWhere('id', $id_cuota);

                $multa_mora = $cuotaData['dias_pasados'] > 0 ? ($cuotaData['multa_mora'] ?? 0) : 0;
                $monto_condonado = $cuotaData['monto_condonado'] ?? 0;

                $datos_pago = [
                    'codigo_transaccion' => $codigo_transaccion, // <--- GUARDAMOS EL CÓDIGO
                    'fecha_pago' => $cuotaData['fecha_pago'],
                    'monto_pago' => $cuotaData['dias_pasados'] > 0
                        ? ($cuotaData['monto_pago'] + ($multa_mora * $cuotaData['dias_pasados'])) - $monto_condonado
                        : $cuotaData['monto_pago'],
                    'monto_cuota' => $cuotaData['monto_pago'],
                    'id_usuario' => Auth::id(),
                    'id_cuota' => $id_cuota,
                    'id_caja' => $id_caja,
                    'monto_condonado' => $monto_condonado,
                    'forma_pago' => $cuotaData['forma_pago'],
                ];

                if ($cuotaData['dias_pasados'] > 0) {
                    $datos_pago['dias_retrasados'] = $cuotaData['dias_pasados'];
                    $datos_pago['multa_dia'] = $multa_mora;
                    $datos_pago['multa_total'] = $multa_mora * $cuotaData['dias_pasados'];
                    $datos_pago['motivo_condonacion'] = $cuotaData['motivo_condonacion'] ?? 'No se ingresó motivo';
                }

                // Insert payment
                DB::table('pago')->insert($datos_pago);

                // Update cuota estado
                DB::table('cuota')->where('id', $id_cuota)->update(['estado' => 2]);

                // Register movement in caja (OJO: Aquí podrías agrupar también, pero por ahora está bien individual)
                DB::table('movimientos_caja')->insert([
                    'tipo_movimiento' => 'ingreso',
                    'monto' => $datos_pago['monto_pago'],
                    'descripcion' => 'Pago de cuota ' . $cuota->numero . ' (' . $codigo_transaccion . ')',
                    'fecha' => now(),
                    'id_caja' => $id_caja,
                    'id_usuario' => Auth::id(),
                ]);
            }

            // Check if there are any unpaid cuotas left
            $no_hay_sin_pagar = DB::table('cuota')
                ->where('id_plan_pago', $id_plan_pago)
                ->where('estado', 1)
                ->doesntExist();

            if ($no_hay_sin_pagar) {
                DB::table('plan_pago')->where('id', $id_plan_pago)->update(['estado' => 2]);
            }

            // ACTUALIZACIÓN CLAVE: 
            // Siempre actualizamos la fecha de última amortización para "resetear" el contador de mora.
            // Y si ya no hay cuotas, cambiamos el estado a 2 (Finalizado).
            DB::table('plan_pago')->where('id', $id_plan_pago)->update([
                'fecha_ultima_amortizacion' => now(), // Resetea el reloj de mora
                'estado' => $no_hay_sin_pagar ? 2 : 1
            ]);

            DB::commit();
            
            // Devolvemos el código para que el front pueda imprimir el recibo agrupado
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
