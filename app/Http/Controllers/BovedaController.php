<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Auth;


class BovedaController extends Controller
{
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

    public function ingresarBoveda(Request $request){
        // BUG-02: Validar input antes de operar
        $request->validate([
            'monto'      => 'required|numeric|min:0.01',
            'descripcion'=> 'required|string|max:255',
        ]);

        if (!DB::table('boveda')->exists()) {
            return response()->json(['message' => 'No existe una bóveda registrada'], 422);
        }

        DB::beginTransaction();
        try {
            $id_boveda = DB::table('boveda')->orderBy('id', 'desc')->value('id');
            $monto     = (float) $request->monto;

            DB::table('movimientos_boveda')->insert([
                'tipo_movimiento' => 'ingreso',
                'monto'           => $monto,
                'descripcion'     => $request->descripcion,
                'fecha'           => now(),
                'id_boveda'       => $id_boveda,
                'id_usuario'      => Auth::id(),
                'id_socio'        => $request->id_socio ?? null,
            ]);

            // BUG-02 resuelto: monto casteado a float antes de DB::raw
            DB::table('boveda')->where('id', $id_boveda)->update([
                'saldo_actual' => DB::raw('saldo_actual + ' . $monto),
            ]);

            DB::commit();
            return response()->json(['message' => 'Ingreso registrado correctamente'], 200);

        } catch (\Exception $e) {
            // BUG-03 resuelto: \Exception con namespace global
            DB::rollBack();
            return response()->json(['message' => 'Error al registrar el ingreso', 'error' => $e->getMessage()], 500);
        }
    }

    public function retirarBoveda(Request $request){
        // BUG-02: Validar input antes de operar
        $request->validate([
            'monto'      => 'required|numeric|min:0.01',
            'descripcion'=> 'required|string|max:255',
        ]);

        if (!DB::table('boveda')->exists()) {
            return response()->json(['message' => 'No existe una bóveda registrada'], 422);
        }

        DB::beginTransaction();
        try {
            $id_boveda = DB::table('boveda')->orderBy('id', 'desc')->value('id');
            $monto     = (float) $request->monto;

            // BUG-01 resuelto: validar saldo suficiente en el backend
            $saldoActual = (float) DB::table('boveda')->where('id', $id_boveda)->value('saldo_actual');
            if ($saldoActual < $monto) {
                DB::rollBack();
                return response()->json(['message' => 'Saldo insuficiente en bóveda'], 422);
            }

            DB::table('movimientos_boveda')->insert([
                'tipo_movimiento' => 'salida',
                'monto'           => $monto,
                'descripcion'     => $request->descripcion,
                'fecha'           => now(),
                'id_boveda'       => $id_boveda,
                'id_usuario'      => Auth::id(),
                'id_socio'        => $request->id_socio ?? null,
            ]);

            // BUG-02 resuelto: monto casteado a float antes de DB::raw
            DB::table('boveda')->where('id', $id_boveda)->update([
                'saldo_actual' => DB::raw('saldo_actual - ' . $monto),
            ]);

            DB::commit();
            return response()->json(['message' => 'Retiro registrado correctamente'], 200);

        } catch (\Exception $e) {
            // BUG-03 resuelto: \Exception con namespace global
            DB::rollBack();
            return response()->json(['message' => 'Error al registrar el retiro', 'error' => $e->getMessage()], 500);
        }
    }

    public function aperturarBoveda(Request $request){
        // BUG-05 resuelto: impedir múltiples aperturas
        if (DB::table('boveda')->exists()) {
            return response()->json(['message' => 'Ya existe una bóveda registrada. No se puede aperturar nuevamente.'], 422);
        }

        DB::beginTransaction();
        try {
            DB::table('boveda')->insert([
                'saldo_actual'   => 0,
                'fecha_apertura' => now(),
                'id_usuario'     => Auth::id(),
            ]);

            DB::commit();
            return response()->json(['message' => 'Bóveda aperturada con éxito'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al aperturar', 'error' => $e->getMessage()], 500);
        }
    }
}
