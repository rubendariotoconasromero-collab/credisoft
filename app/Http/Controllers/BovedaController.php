<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Auth;


class BovedaController extends Controller
{
    public function cierresCaja(Request $request)
    {
        $request->validate(['page' => 'nullable|integer|min:1']);
        return DB::table('caja_cierres')->join('users', 'users.id', '=', 'caja_cierres.id_usuario')
            ->select('caja_cierres.*', 'users.personal as usuario')->orderByDesc('caja_cierres.id')->paginate(15)
            ->through(function ($closing) {
                $closing->resumen = json_decode($closing->resumen, true, 512, JSON_THROW_ON_ERROR);
                return $closing;
            });
    }

    public function indexBoveda(){
        return view('frmBoveda');
    }

    public function getBoveda(Request $request){
        $boveda = DB::table('boveda')
            ->leftJoin('users', 'boveda.id_usuario', '=', 'users.id')
            ->select('boveda.*', 'users.personal as nombre_usuario')
            ->orderBy('boveda.id', 'desc')
            ->first();

        return [
            'saldo_actual'    => empty($boveda) ? 0       : $boveda->saldo_actual,
            'fecha_apertura'  => empty($boveda) ? null    : $boveda->fecha_apertura,
            'id_boveda'       => empty($boveda) ? 0       : $boveda->id,
            'usuario_apertura'=> empty($boveda) ? 'Sin registro' : $boveda->nombre_usuario,
        ];
    }

    public function getMovimientosBoveda(Request $request){
        // 1. Construir la consulta base (sin el primer select duplicado)
        $query = DB::table('movimientos_boveda')
            ->join('users', 'users.id', '=', 'movimientos_boveda.id_usuario')
            ->leftJoin('socios', 'socios.id', '=', 'movimientos_boveda.id_socio')
            ->select(
                'movimientos_boveda.*',
                'users.personal',
                'socios.nombres as socio_nombres',
                'socios.apellidos as socio_apellidos'
            );

        // 2. Aplicar filtros
        if ($request->has('tipo') && $request->tipo != 'todos') {
            $query->where('movimientos_boveda.tipo_movimiento', $request->tipo);
        }

        if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
            $query->whereDate('movimientos_boveda.fecha', '>=', $request->fecha_inicio)
                  ->whereDate('movimientos_boveda.fecha', '<=', $request->fecha_fin);
        }

        // 3. Clonar antes de paginar para calcular totales del período filtrado
        $queryIngresos = clone $query;
        $querySalidas  = clone $query;

        // 4. Paginación
        $registros = $query->orderBy('movimientos_boveda.id', 'desc')->paginate(40);

        // 5. Totales (reflejan el filtro activo de tipo y fechas)
        $ingresos = $queryIngresos->where('movimientos_boveda.tipo_movimiento', 'ingreso')->sum('monto');
        $salidas  = $querySalidas->where('movimientos_boveda.tipo_movimiento', 'salida')->sum('monto');

        return response()->json([
            'movimientos' => $registros,
            'totales'     => [
                'ingresos' => $ingresos,
                'salidas'  => $salidas,
            ],
        ]);
    }

    public function ingresarBoveda(Request $request)
    {
        return $this->registrarMovimiento($request, 'ingreso');
    }

    public function retirarBoveda(Request $request)
    {
        return $this->registrarMovimiento($request, 'salida');
    }

    private function registrarMovimiento(Request $request, string $direction)
    {
        $request->validate(['monto' => 'required|numeric|min:0.01', 'descripcion' => 'required|string|max:255',
            'id_socio' => 'nullable|integer|exists:socios,id', 'id_caja' => 'nullable|integer|exists:caja,id']);
        $type = str_contains(mb_strtolower($request->descripcion), 'transferencia a caja') ? 'transferencia_caja' : 'boveda_'.$direction;
        return app(\App\Services\Finance\Operations::class)->run($request, $type, function ($op) use ($request, $direction) {
            app(\App\Services\Finance\VaultOperations::class)->move((int) Auth::id(), $direction,
                $request->monto, $request->descripcion, $request->filled('id_caja') ? (int) $request->id_caja : null,
                $request->filled('id_socio') ? (int) $request->id_socio : null, $op);
            return ['message' => 'Movimiento registrado correctamente'];
        });
    }

    public function aperturarBoveda(Request $request)
    {
        app(\App\Services\Finance\VaultOperations::class)->open((int) Auth::id());
        return response()->json(['message' => 'Bóveda aperturada con éxito']);
    }
}
