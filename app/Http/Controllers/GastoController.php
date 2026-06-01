<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\CalculaSaldoCaja;
use DB;
class GastoController extends Controller
{
    use CalculaSaldoCaja;

    public function save(Request $request){
        DB::beginTransaction();
        try{

            $id_caja = DB::table('caja')->where('estado', 1)->first()?->id;

            if (!$id_caja) {
                return response()->json(['message' => 'No hay caja abierta para registrar el egreso.'], 422);
            }

            // ── Validar saldo disponible en caja ─────────────────────────
            $monto     = (float) $request->monto;
            $saldoCaja = $this->calcularSaldoCaja($id_caja);

            if ($saldoCaja < $monto) {
                DB::rollBack();
                return response()->json([
                    'message'          => 'Saldo insuficiente en caja para registrar el egreso.',
                    'saldo_disponible' => round($saldoCaja, 2),
                    'monto_requerido'  => $monto,
                    'faltante'         => round($monto - $saldoCaja, 2),
                ], 422);
            }
            // ─────────────────────────────────────────────────────────────

            $descripcionFinal = ($request->descripcion == 'Otros gastos')
                ? $request->descripcion . ' - ' . $request->descripcion_otro
                : $request->descripcion;

            DB::table('egreso')->insert([
                'monto'       => $request->monto,
                'descripcion' => $descripcionFinal,
                'id_usuario'  => Auth::id(),
                'fecha'       => now(),
                'id_caja'     => $id_caja,
            ]);

            // "Transferencia a Bóveda": el efectivo físicamente entra a la bóveda.
            // Se crea automáticamente la contrapartida en movimientos_boveda.
            $esTransferenciaInterna = str_contains(strtolower($descripcionFinal), 'transferencia a bóveda')
                                   || str_contains(strtolower($descripcionFinal), 'transferencia a boveda');

            if ($esTransferenciaInterna) {
                $id_boveda = DB::table('boveda')->orderBy('id', 'desc')->value('id');
                if ($id_boveda) {
                    DB::table('movimientos_boveda')->insert([
                        'tipo_movimiento' => 'ingreso',
                        'monto'           => $monto,
                        'descripcion'     => 'Transferencia desde Caja',
                        'fecha'           => now(),
                        'id_boveda'       => $id_boveda,
                        'id_usuario'      => Auth::id(),
                    ]);
                    DB::table('boveda')->where('id', $id_boveda)->update([
                        'saldo_actual' => DB::raw('saldo_actual + ' . $monto),
                    ]);
                }
            }

            // Reg. en mov. caja
            DB::table('movimientos_caja')
            ->insertGetId([
                'tipo_movimiento'=>'salida',
                'monto'=>$request->monto,
                'descripcion'=>($request->descripcion=='Otros gastos')?$request->descripcion .' - '.$request->descripcion_otro: $request->descripcion,
                'fecha'=>now(),
                'id_caja'=>$id_caja,
                'id_usuario'=>Auth::user()->id,
            ]);

            DB::commit();
        }catch(Exception $e){
            DB::rollback();
        }

    }

    public function getGastosCorrientes(Request $request){
        $gastos = DB::table('egreso')
        ->join('users', 'egreso.id_usuario', '=', 'users.id')
        ->join('caja', 'caja.id', '=', 'egreso.id_caja')
        ->select('users.name as asesor', 'egreso.fecha', 'egreso.monto as monto_gasto', 'egreso.id', 'egreso.estado', 'egreso.descripcion')
        ->where('caja.id', $request->id_caja)
        ->whereDate('egreso.fecha', '>=', $request->fecha_inicio)
        ->whereDate('egreso.fecha', '<=', $request->fecha_final)
        ->paginate(40);

        $totalGastos = DB::table('egreso')
        ->join('users', 'egreso.id_usuario', '=', 'users.id')
        ->join('caja', 'caja.id', '=', 'egreso.id_caja')
        ->select(DB::raw('sum(egreso.monto) as totalIngresos'))
        ->where('caja.id', $request->id_caja)
        ->whereDate('egreso.fecha', '>=', $request->fecha_inicio)
        ->whereDate('egreso.fecha', '<=', $request->fecha_final)
        ->get()[0]->totalIngresos;



        return ['gastos'=>$gastos, 'totalGastos'=>$totalGastos];

    }

    public function historialGastosListado(Request $request){

        $fechaInicio = $request->fecha_inicio;
        $fechaFinal = $request->fecha_final;
        $criterio = $request->criterio;
        $buscar = $request->buscar;

        $gastos = DB::table('egreso')
        ->join('users', 'egreso.id_usuario', '=', 'users.id')
        ->join('caja', 'caja.id', '=', 'egreso.id_caja')
        ->select('users.name as asesor', 'egreso.fecha', 'egreso.monto as monto_gasto', 'egreso.id', 'egreso.estado', 
        'egreso.descripcion', 'users.personal', 'caja.estado as estado_caja')
        ->whereDate('egreso.fecha', '>=',  $fechaInicio)
        ->whereDate('egreso.fecha', '<=',  $fechaFinal)
        ->where($criterio, 'like', '%'.$buscar.'%')
        ->orderBy('egreso.id', 'desc')
        ->paginate(50);

        $totalGastos = DB::table('egreso')
        ->join('users', 'egreso.id_usuario', '=', 'users.id')
        ->join('caja', 'caja.id', '=', 'egreso.id_caja')
        ->whereDate('egreso.fecha', '>=', $fechaInicio)
        ->whereDate('egreso.fecha', '<=', $fechaFinal)
        ->where('egreso.estado', '=', 1)
        ->sum('egreso.monto');

        return ['gastos'=>$gastos, 'totalGastos'=>$totalGastos];
        
    }

    public function historialEgresosListadoCaja(Request $request){

        $fechaInicio = $request->fecha_inicio;
        $fechaFinal = $request->fecha_final;
        $criterio = $request->criterio;
        $buscar = $request->buscar;

        $gastos = DB::table('egreso')
        ->join('users', 'egreso.id_usuario', '=', 'users.id')
        ->join('caja', 'caja.id', '=', 'egreso.id_caja')
        ->select('users.name as asesor', 'egreso.fecha', 'egreso.monto as monto_gasto', 'egreso.id', 'egreso.estado', 
        'egreso.descripcion', 'users.personal', 'caja.estado as estado_caja')
        ->whereDate('egreso.fecha', '>=',  $fechaInicio)
        ->whereDate('egreso.fecha', '<=',  $fechaFinal)
        ->where($criterio, 'like', '%'.$buscar.'%')    
        ->where('caja.estado', 1)
        ->orderBy('egreso.id', 'desc')
        ->paginate(50);

        $totalGastos = DB::table('egreso')
        ->join('users', 'egreso.id_usuario', '=', 'users.id')
        ->join('caja', 'caja.id', '=', 'egreso.id_caja')
        ->whereDate('egreso.fecha', '>=', $fechaInicio)
        ->whereDate('egreso.fecha', '<=', $fechaFinal)
        ->where('caja.estado', 1)
        ->where('egreso.estado', '=', 1)
        ->sum('egreso.monto');

        return ['egresos'=>$gastos, 'totalEgresos'=>$totalGastos];
        
    }

    

    public function anularGasto(Request $request){
        $egreso = DB::table('egreso')->where('id', $request->id)->select('monto', 'descripcion')->first();
        $monto  = (float) ($egreso->monto ?? 0);

        $anular = DB::table('egreso')
            ->join('caja', 'caja.id', '=', 'egreso.id_caja')
            ->where('egreso.id', $request->id)
            ->where('caja.estado', 1)->exists();

        if ($anular) {
            DB::table('egreso')
                ->join('caja', 'caja.id', '=', 'egreso.id_caja')
                ->where('egreso.id', $request->id)
                ->where('caja.estado', 1)
                ->update(['egreso.estado' => 0]);

            // Si era "Transferencia a Bóveda", revertir la contrapartida en boveda
            $descripcion = strtolower($egreso->descripcion ?? '');
            $esTransferenciaInterna = str_contains($descripcion, 'transferencia a bóveda')
                                   || str_contains($descripcion, 'transferencia a boveda');

            if ($esTransferenciaInterna) {
                $id_boveda = DB::table('boveda')->orderBy('id', 'desc')->value('id');
                if ($id_boveda) {
                    DB::table('boveda')->where('id', $id_boveda)->update([
                        'saldo_actual' => DB::raw('saldo_actual - ' . $monto),
                    ]);
                }
            }

            return response()->json(['respuesta' => 1]);
        } else {
            return response()->json(['respuesta' => 0]);
        }
    }
}
