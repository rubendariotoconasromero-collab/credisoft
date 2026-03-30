<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pago;
use App\Models\Cuota;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Mpdf\Mpdf;

class HistorialPagosController extends Controller
{
    public function index(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFinal = $request->input('fecha_final');
        $criterio = $request->input('criterio');
        $buscar = $request->input('buscar');

        // 1. CONSTRUIMOS LA CONSULTA AGRUPADA LEYENDO LAS NUEVAS COLUMNAS DE CASCADA
        $query = Pago::select(
            'codigo_transaccion',
            DB::raw('MAX(id) as id_referencia'), 
            DB::raw('MAX(fecha_pago) as fecha_pago'),
            DB::raw('MAX(id_usuario) as id_usuario'),
            DB::raw('SUM(monto_pago) as total_pagado'), // EFECTIVO REAL
            DB::raw('SUM(pago_capital) as total_capital'), // CUÁNTO FUE A CAPITAL
            DB::raw('SUM(pago_interes) as total_interes'), // CUÁNTO FUE A INTERÉS
            DB::raw('SUM(pago_mora) as total_mora'),       // CUÁNTO FUE A MORA
            DB::raw('SUM(monto_condonado) as total_condonado'), 
            DB::raw('COUNT(id) as cantidad_cuotas'), 
            DB::raw('GROUP_CONCAT(id_cuota) as ids_cuotas'), 
            DB::raw('MAX(forma_pago) as forma_pago'),
            DB::raw('MAX(estado) as estado')
        )
        ->groupBy('codigo_transaccion');

        // 2. Filtros de Fecha
        if ($fechaInicio && $fechaFinal) {
            $query->whereBetween('fecha_pago', [$fechaInicio . ' 00:00:00', $fechaFinal . ' 23:59:59']);
        }

        // 3. Filtros de Búsqueda
        if (!empty($buscar)) {
            if ($criterio == 'pago.id' || $criterio == 'codigo') {
                $query->where('codigo_transaccion', 'like', "%$buscar%");
            } 
            elseif ($criterio == 'users.name') {
                $query->whereHas('usuario', function($q) use ($buscar) {
                    $q->where('name', 'like', "%$buscar%");
                });
            }
            elseif (str_contains($criterio, 'cliente')) {
                $query->whereHas('cuota.planPago.solicitud.cliente', function($q) use ($buscar) {
                    $q->where(DB::raw("CONCAT(nombre, ' ', apellido)"), 'like', "%$buscar%")
                    ->orWhere('ci', 'like', "%$buscar%");
                });
            }
        }

        $query->orderBy('fecha_pago', 'desc');

        // 4. KPIS (Totales globales)
        $kpiQuery = Pago::query();
        if ($fechaInicio && $fechaFinal) {
            $kpiQuery->whereBetween('fecha_pago', [$fechaInicio . ' 00:00:00', $fechaFinal . ' 23:59:59']);
        }
        
        // Ahora usamos las columnas correctas
        $totalRecaudado = $kpiQuery->where('estado', 1)->sum('monto_pago'); // Dinero total que entró a caja
        $totalMultas = $kpiQuery->where('estado', 1)->sum('pago_mora'); // Dinero de caja que fue a multas
        $totalCondonado = $kpiQuery->where('estado', 1)->sum('monto_condonado'); // Descuentos

        // 5. Paginación
        $pagos = $query->paginate(10);

        // 6. Cargar Relaciones
        $pagos->getCollection()->transform(function ($pagoGroup) {
            $pagoReal = Pago::with('usuario', 'cuota.planPago.solicitud.cliente')
                            ->find($pagoGroup->id_referencia);
            
            $pagoGroup->usuario = $pagoReal->usuario;
            $pagoGroup->cliente_data = $pagoReal->cuota->planPago->solicitud->cliente ?? null;
            $pagoGroup->credito_id = $pagoReal->cuota->planPago->id ?? null;
            
            $numerosCuotas = Cuota::whereIn('id', explode(',', $pagoGroup->ids_cuotas))->pluck('numero')->toArray();
            $pagoGroup->detalles_cuotas = implode(', ', $numerosCuotas);

            return $pagoGroup;
        });

        return response()->json([
            'pagos' => $pagos,
            'kpis' => [
                'total_recaudado' => $totalRecaudado, // Ya no sumamos la multa aquí, monto_pago ya la incluye
                'total_multas' => $totalMultas,
                'total_condonado' => $totalCondonado
            ]
        ]);
    }

    public function show($codigo)
    {
        $pagos = Pago::with([
                'cuota', 
                'usuario', 
                'cuota.planPago.solicitud.cliente'
            ])
            ->where('codigo_transaccion', $codigo)
            ->orderBy('id', 'asc') 
            ->get();

        if ($pagos->isEmpty()) {
            return response()->json(['message' => 'Transacción no encontrada'], 404);
        }

        $referencia = $pagos->first();
        
        // El monto total es directamente la suma de "monto_pago" (el efectivo). 
        // Ya no sumamos multa_total para evitar duplicar
        $totalMonto = $pagos->sum('monto_pago');

        $cabecera = [
            'codigo'            => $codigo,
            'fecha'             => $referencia->fecha_pago,
            'cajero'            => $referencia->usuario ? $referencia->usuario->name : 'Sistema',
            'cliente'           => $referencia->cuota->planPago->solicitud->cliente, 
            'plan_pago_id'      => $referencia->cuota->planPago->id, 
            'total_transaccion' => $totalMonto 
        ];

        return response()->json([
            'cabecera' => $cabecera,
            'detalles' => $pagos
        ]);
    }

    public function anular($id)
    {
        DB::beginTransaction();
        try {
            $pago = Pago::findOrFail($id);

            if ($pago->estado == 0) {
                return response()->json(['message' => 'El pago ya está anulado'], 400);
            }

            // 1. Cambiar estado del pago
            $pago->estado = 0;
            $pago->save();

            // 2. Revertir saldo en la Cuota
            $cuota = Cuota::findOrFail($pago->id_cuota);
            
            // Lógica básica: Devolvemos el capital y estado
            // OJO: Aquí deberías tener tu lógica de negocio precisa.
            // Ejemplo:
            // $cuota->saldo_capital += ($pago->monto_cuota - $pago->interes_pagado...);
            // $cuota->estado = 1; // Pendiente
            // $cuota->save();
            
            // *IMPORTANTE*: Si tienes una tabla Caja abierta, deberías restar este monto
            // $caja = Caja::find($pago->id_caja);
            // $caja->total_ingreso -= $pago->monto_pago;
            // $caja->save();

            DB::commit();

            return response()->json(['message' => 'Pago anulado correctamente']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al anular: ' . $e->getMessage()], 500);
        }
    }
}