<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pago;
use App\Models\Cuota;
use Illuminate\Support\Facades\DB;

class HistorialPagosController extends Controller
{
    public function index(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFinal = $request->input('fecha_final');
        $criterio = $request->input('criterio');
        $buscar = $request->input('buscar');

        // CONSTRUIMOS LA CONSULTA AGRUPADA
        // Seleccionamos el codigo y SUMAMOS los montos
        $query = Pago::select(
            'codigo_transaccion',
            DB::raw('MAX(id) as id_referencia'), // Usamos esto para relaciones
            DB::raw('MAX(fecha_pago) as fecha_pago'),
            DB::raw('MAX(id_usuario) as id_usuario'), // Asumimos mismo cajero
            DB::raw('SUM(monto_pago) as total_pagado'), // Suma total dinero
            DB::raw('SUM(monto_cuota) as total_capital'),
            DB::raw('SUM(multa_total) as total_multa'),
            DB::raw('COUNT(id) as cantidad_cuotas'), // Cuántas cuotas pagó
            DB::raw('GROUP_CONCAT(id_cuota) as ids_cuotas'), // (Opcional) IDs separados por coma
            DB::raw('MAX(forma_pago) as forma_pago'),
            DB::raw('MAX(estado) as estado')
        )
        ->groupBy('codigo_transaccion');


        // 2. Filtros de Fecha (Sobre los agregados o where simple)
        if ($fechaInicio && $fechaFinal) {
            $query->whereBetween('fecha_pago', [$fechaInicio . ' 00:00:00', $fechaFinal . ' 23:59:59']);
        }

        // 3. Filtros de Búsqueda
        if (!empty($buscar)) {
            if ($criterio == 'pago.id' || $criterio == 'codigo') {
                // Buscamos por el código de transacción
                $query->where('codigo_transaccion', 'like', "%$buscar%");
            } 
            elseif ($criterio == 'users.name') {
                $query->whereHas('usuario', function($q) use ($buscar) {
                    $q->where('name', 'like', "%$buscar%");
                });
            }
            elseif (str_contains($criterio, 'cliente')) {
                // Buscamos en los pagos que tengan cuotas de ese cliente
                // Esta es una subconsulta "whereExists" para no romper el group by
                $query->whereHas('cuota.planPago.solicitud.cliente', function($q) use ($buscar) {
                    $q->where(DB::raw("CONCAT(nombre, ' ', apellido)"), 'like', "%$buscar%")
                    ->orWhere('ci', 'like', "%$buscar%");
                });
            }
        }

        // Ordenar por fecha reciente
        $query->orderBy('fecha_pago', 'desc');

        // 4. KPIS (Totales globales antes de paginar)
        // Nota: Para sumar totales de una query agrupada, es mejor hacer una query separada simple
        $kpiQuery = Pago::query();
        if ($fechaInicio && $fechaFinal) {
            $kpiQuery->whereBetween('fecha_pago', [$fechaInicio . ' 00:00:00', $fechaFinal . ' 23:59:59']);
        }
        // (Aquí podrías replicar los filtros de búsqueda si fuera necesario para exactitud extrema)
        
        $totalRecaudado = $kpiQuery->where('estado', 1)->sum('monto_pago'); // + Multas si ya estan incluidas en monto_pago o sumar aparte
        $totalMultas = $kpiQuery->where('estado', 1)->sum('multa_total');
        $totalCondonado = $kpiQuery->where('estado', 1)->sum('monto_condonado');


        // 5. Paginación
        $pagos = $query->paginate(10);

        // 6. Cargar Relaciones (Cliente) DESPUÉS de paginar
        // Como $pagos es una colección agrupada, no tiene la relación directa.
        // Usamos 'id_referencia' (que es un ID real de pago) para cargar la data del cliente.
        
        // Transformamos la colección para inyectar el cliente
        $pagos->getCollection()->transform(function ($pagoGroup) {
            // Buscamos UN pago real de este grupo para sacar datos del cliente
            $pagoReal = Pago::with('usuario', 'cuota.planPago.solicitud.cliente')
                            ->find($pagoGroup->id_referencia);
            
            $pagoGroup->usuario = $pagoReal->usuario;
            $pagoGroup->cliente_data = $pagoReal->cuota->planPago->solicitud->cliente ?? null;
            $pagoGroup->credito_id = $pagoReal->cuota->planPago->id ?? null;
            
            // Obtenemos los números de cuota (ej: "1, 2, 3")
            $numerosCuotas = Cuota::whereIn('id', explode(',', $pagoGroup->ids_cuotas))->pluck('numero')->toArray();
            $pagoGroup->detalles_cuotas = implode(', ', $numerosCuotas);

            return $pagoGroup;
        });

        return response()->json([
            'pagos' => $pagos,
            'kpis' => [
                'total_recaudado' => $totalRecaudado + $totalMultas, 
                'total_multas' => $totalMultas,
                'total_condonado' => $totalCondonado
            ]
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

    /**
     * Obtener detalles de una transacción agrupada por código
     * * @param string $codigo El código de transacción (ej. TRX-123...)
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($codigo)
    {
        // 1. Buscamos TODOS los pagos que pertenezcan a ese código de transacción
        // Cargamos las relaciones necesarias para mostrar nombres y datos del crédito
        $pagos = Pago::with([
                'cuota', 
                'usuario', 
                'cuota.planPago.solicitud.cliente'
            ])
            ->where('codigo_transaccion', $codigo)
            ->orderBy('id', 'asc') // Ordenamos para que las cuotas salgan en orden (1, 2, 3...)
            ->get();

        // Validación: Si no existe el código
        if ($pagos->isEmpty()) {
            return response()->json(['message' => 'Transacción no encontrada'], 404);
        }

        // 2. Preparamos la CABECERA (Datos compartidos por todo el grupo)
        // Tomamos el primer registro como referencia para sacar cliente, fecha y cajero
        $referencia = $pagos->first();
        
        // Calculamos los totales sumando la columna de todos los registros encontrados
        $totalMonto = $pagos->sum('monto_pago');
        $totalMulta = $pagos->sum('multa_total');

        $cabecera = [
            'codigo'            => $codigo,
            'fecha'             => $referencia->fecha_pago,
            'cajero'            => $referencia->usuario ? $referencia->usuario->name : 'Sistema',
            // Obtenemos el objeto cliente completo para mostrar Nombre, CI, etc.
            'cliente'           => $referencia->cuota->planPago->solicitud->cliente, 
            'plan_pago_id'      => $referencia->cuota->planPago->id, // ID del crédito
            'total_transaccion' => $totalMonto + $totalMulta // Total global pagado en ese momento
        ];

        // 3. Retornamos la respuesta JSON estructurada
        return response()->json([
            'cabecera' => $cabecera,
            'detalles' => $pagos // Aquí va la lista completa de cuotas individuales
        ]);
    }
}