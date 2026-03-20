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

    public function getLibroMayor(Request $request)
    {
        $fecha_inicio = $request->input('fecha_inicio', date('Y-m-d', strtotime('-1 month')));
        $fecha_final = $request->input('fecha_final', date('Y-m-d'));
        
        // 1. CONSTRUIR CONSULTAS INDIVIDUALES (LAS 6 BOLSAS)
        $q_capital = DB::table('pago')->join('cuota', 'pago.id_cuota', '=', 'cuota.id')
            ->where('pago.estado', 1)->where('pago.pago_capital', '>', 0)
            ->whereDate('pago.fecha_pago', '<=', $fecha_final)
            ->select('pago.fecha_pago as fecha', DB::raw("'CAPITAL' as tipo"), DB::raw("CONCAT('CAPITAL, CREDITO: ', cuota.id_plan_pago, ', CUOTA: ', cuota.numero) as descripcion"), 'pago.pago_capital as debe', DB::raw('0 as haber'), 'pago.created_at');

        $q_interes = DB::table('pago')->join('cuota', 'pago.id_cuota', '=', 'cuota.id')
            ->where('pago.estado', 1)->where('pago.pago_interes', '>', 0)
            ->whereDate('pago.fecha_pago', '<=', $fecha_final)
            ->select('pago.fecha_pago as fecha', DB::raw("'INTERES' as tipo"), DB::raw("CONCAT('INTERES, CREDITO: ', cuota.id_plan_pago, ', CUOTA: ', cuota.numero) as descripcion"), 'pago.pago_interes as debe', DB::raw('0 as haber'), 'pago.created_at');

        $q_mora = DB::table('pago')->join('cuota', 'pago.id_cuota', '=', 'cuota.id')
            ->where('pago.estado', 1)->where('pago.pago_mora', '>', 0)
            ->whereDate('pago.fecha_pago', '<=', $fecha_final)
            ->select('pago.fecha_pago as fecha', DB::raw("'MORA' as tipo"), DB::raw("CONCAT('MORA, CREDITO: ', cuota.id_plan_pago, ', CUOTA: ', cuota.numero) as descripcion"), 'pago.pago_mora as debe', DB::raw('0 as haber'), 'pago.created_at');

        $q_desembolso = DB::table('desembolso')->where('estado', 0)->whereDate('fecha', '<=', $fecha_final)
            ->select('fecha', DB::raw("'DESEMBOLSO' as tipo"), DB::raw("CONCAT('DESEMBOLSO, CREDITO: ', id_plan_pago) as descripcion"), DB::raw('0 as debe'), 'monto as haber', 'fecha as created_at');

        $q_gastosadm = DB::table('pago_administrativo')->where('estado', 0)->where('monto', '>', 0)
            ->whereDate('fecha', '<=', $fecha_final)
            ->select('fecha', DB::raw("'GASTOSADM' as tipo"), DB::raw("CONCAT('GASTOSADM, CREDITO: ', id_plan_pago) as descripcion"), 'monto as debe', DB::raw('0 as haber'), 'fecha as created_at');

        $q_ingreso = DB::table('ingreso')->where('estado', 1)->whereDate('fecha', '<=', $fecha_final)
            ->select('fecha', DB::raw("'INGRESO_CAJA' as tipo"), 'descripcion', 'monto as debe', DB::raw('0 as haber'), 'created_at');

        $q_gasto = DB::table('egreso')->where('estado', 1)->whereDate('fecha', '<=', $fecha_final)
            ->select('fecha', DB::raw("'EGRESO_CAJA' as tipo"), 'descripcion', DB::raw('0 as debe'), 'monto as haber', 'created_at');

        // 2. UNIR Y ORDENAR
        $movimientos = $q_capital->unionAll($q_interes)->unionAll($q_mora)->unionAll($q_desembolso)
            ->unionAll($q_gastosadm)->unionAll($q_ingreso)->unionAll($q_gasto)
            ->orderBy('fecha', 'asc')->orderBy('created_at', 'asc')->get();

        // 3. CALCULAR SALDO HISTÓRICO Y FILTRAR
        $saldo_acumulado = 0;
        $lista_final = [];
        $nro = 1;
        $suma_ingresos_periodo = 0;
        $suma_egresos_periodo = 0;

        $tipo_filtro = $request->input('tipo', 'TODOS');
        $buscar_filtro = strtolower($request->input('buscar', ''));

        foreach ($movimientos as $mov) {
            $saldo_acumulado += $mov->debe;
            $saldo_acumulado -= $mov->haber;

            if ($mov->fecha >= $fecha_inicio) {
                if ($tipo_filtro !== 'TODOS' && $mov->tipo !== $tipo_filtro) continue;
                if ($buscar_filtro !== '' && !str_contains(strtolower($mov->descripcion), $buscar_filtro)) continue;

                $suma_ingresos_periodo += $mov->debe;
                $suma_egresos_periodo += $mov->haber;

                $lista_final[] = [
                    'nro' => $nro++,
                    'fecha' => date('d/m/Y', strtotime($mov->fecha)),
                    'tipo' => $mov->tipo,
                    'descripcion' => $mov->descripcion,
                    'debe' => (float)$mov->debe,
                    'haber' => (float)$mov->haber,
                    'saldo' => round($saldo_acumulado, 2)
                ];
            }
        }

        // 4. PAGINACIÓN MANUAL DEL ARREGLO
        $page = (int)$request->input('page', 1);
        $perPage = 15; // Filas por página
        $total = count($lista_final);
        $items = array_slice($lista_final, ($page - 1) * $perPage, $perPage);

        return response()->json([
            'movimientos' => [
                'current_page' => $page,
                'data' => $items,
                'last_page' => ceil($total / $perPage),
                'total' => $total,
            ],
            'totales' => [
                'ingresos' => round($suma_ingresos_periodo, 2),
                'egresos' => round($suma_egresos_periodo, 2)
            ]
        ]);
    }

    public function imprimirLibroMayor(Request $request)
    {
        $fecha_inicio = $request->input('fecha_inicio', date('Y-m-d', strtotime('-1 month')));
        $fecha_final = $request->input('fecha_final', date('Y-m-d'));
        $tipo_filtro = $request->input('tipo', 'TODOS');
        $buscar_filtro = strtolower($request->input('buscar', ''));

        // 1. CONSTRUIR CONSULTAS (Igual que en el método original)
        $q_capital = DB::table('pago')->join('cuota', 'pago.id_cuota', '=', 'cuota.id')
            ->where('pago.estado', 1)->where('pago.pago_capital', '>', 0)
            ->whereDate('pago.fecha_pago', '<=', $fecha_final)
            ->select('pago.fecha_pago as fecha', DB::raw("'CAPITAL' as tipo"), DB::raw("CONCAT('CAPITAL, CREDITO: ', cuota.id_plan_pago, ', CUOTA: ', cuota.numero) as descripcion"), 'pago.pago_capital as debe', DB::raw('0 as haber'), 'pago.created_at');

        $q_interes = DB::table('pago')->join('cuota', 'pago.id_cuota', '=', 'cuota.id')
            ->where('pago.estado', 1)->where('pago.pago_interes', '>', 0)
            ->whereDate('pago.fecha_pago', '<=', $fecha_final)
            ->select('pago.fecha_pago as fecha', DB::raw("'INTERES' as tipo"), DB::raw("CONCAT('INTERES, CREDITO: ', cuota.id_plan_pago, ', CUOTA: ', cuota.numero) as descripcion"), 'pago.pago_interes as debe', DB::raw('0 as haber'), 'pago.created_at');

        $q_mora = DB::table('pago')->join('cuota', 'pago.id_cuota', '=', 'cuota.id')
            ->where('pago.estado', 1)->where('pago.pago_mora', '>', 0)
            ->whereDate('pago.fecha_pago', '<=', $fecha_final)
            ->select('pago.fecha_pago as fecha', DB::raw("'MORA' as tipo"), DB::raw("CONCAT('MORA, CREDITO: ', cuota.id_plan_pago, ', CUOTA: ', cuota.numero) as descripcion"), 'pago.pago_mora as debe', DB::raw('0 as haber'), 'pago.created_at');

        $q_desembolso = DB::table('desembolso')->where('estado', 0)->whereDate('fecha', '<=', $fecha_final)
            ->select('fecha', DB::raw("'DESEMBOLSO' as tipo"), DB::raw("CONCAT('DESEMBOLSO, CREDITO: ', id_plan_pago) as descripcion"), DB::raw('0 as debe'), 'monto as haber', 'fecha as created_at');

        $q_gastosadm = DB::table('pago_administrativo')->where('estado', 0)->where('monto', '>', 0)
            ->whereDate('fecha', '<=', $fecha_final)
            ->select('fecha', DB::raw("'GASTOSADM' as tipo"), DB::raw("CONCAT('GASTOSADM, CREDITO: ', id_plan_pago) as descripcion"), 'monto as debe', DB::raw('0 as haber'), 'fecha as created_at');

        $q_ingreso = DB::table('ingreso')->where('estado', 1)->whereDate('fecha', '<=', $fecha_final)
            ->select('fecha', DB::raw("'INGRESO_CAJA' as tipo"), 'descripcion', 'monto as debe', DB::raw('0 as haber'), 'created_at');

        $q_gasto = DB::table('egreso')->where('estado', 1)->whereDate('fecha', '<=', $fecha_final)
            ->select('fecha', DB::raw("'EGRESO_CAJA' as tipo"), 'descripcion', DB::raw('0 as debe'), 'monto as haber', 'created_at');

        $movimientos = $q_capital->unionAll($q_interes)->unionAll($q_mora)->unionAll($q_desembolso)
            ->unionAll($q_gastosadm)->unionAll($q_ingreso)->unionAll($q_gasto)
            ->orderBy('fecha', 'asc')->orderBy('created_at', 'asc')->get();

        // 2. CALCULAR SALDOS Y FILTRAR
        $saldo_acumulado = 0;
        $lista_final = [];
        $nro = 1;
        $suma_ingresos = 0;
        $suma_egresos = 0;

        foreach ($movimientos as $mov) {
            $saldo_acumulado += $mov->debe;
            $saldo_acumulado -= $mov->haber;

            if ($mov->fecha >= $fecha_inicio) {
                if ($tipo_filtro !== 'TODOS' && $mov->tipo !== $tipo_filtro) continue;
                if ($buscar_filtro !== '' && !str_contains(strtolower($mov->descripcion), $buscar_filtro)) continue;

                $suma_ingresos += $mov->debe;
                $suma_egresos += $mov->haber;

                // Para pasarlo a Blade, lo convertimos en un objeto estándar de PHP
                $lista_final[] = (object)[
                    'nro' => $nro++,
                    'fecha' => date('d/m/Y', strtotime($mov->fecha)),
                    'tipo' => $mov->tipo,
                    'descripcion' => $mov->descripcion,
                    'debe' => (float)$mov->debe,
                    'haber' => (float)$mov->haber,
                    'saldo' => round($saldo_acumulado, 2)
                ];
            }
        }

        // 3. OBTENER INFO EMPRESA Y PREPARAR DATA
        $empresa = DB::table('mi_empresa')->first();

        $data = [
            'empresa' => $empresa,
            'movimientos' => $lista_final,
            'fecha_inicio' => $fecha_inicio,
            'fecha_final' => $fecha_final,
            'tipo_filtro' => $tipo_filtro,
            'total_ingresos' => $suma_ingresos,
            'total_egresos' => $suma_egresos
        ];

        // 4. LLAMAR A TU FUNCIÓN PRIVADA MPDF
        return $this->generatePDF($data, 'reporte.libro_mayor', 'Reporte_Libro_Mayor_' . date('Y-m-d'));
    }

    private function generatePDF($data, $url_vista, $nombre_reporte)
    {

        try {
            // Configuración inicial de MPDF
            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8', // Soporte para caracteres especiales
                'format' => 'letter',  // Tamaño de página (puedes usar 'Letter', 'Legal', etc.)
                'orientation' => 'P', // Orientación: P (vertical) o L (horizontal)
                'margin_left' => 10, // Márgenes en mm
                'margin_right' => 10,
                'margin_top' => 10,
                'margin_bottom' => 10,
            ]);

            $mpdf->SetHTMLFooter('
                <div style="text-align: center; font-size: 10px;">
                    Página {PAGENO} de {nbpg}
                </div>
            ');

            // Renderizar la vista HTML
            $html = view($url_vista, $data)->render();

            // Escribir el contenido HTML en el PDF
            $mpdf->WriteHTML($html);

            // Generar el PDF
            $mpdf->Output($nombre_reporte . '.pdf', 'I'); // 'I' para abrir en el navegador, 'D' para descargar

        } catch (\Exception $e) {
            // Manejo de errores
            \Log::error('Error al generar el PDF: ' . $e->getMessage());
            return response()->json(['error' => 'No se pudo generar el PDF.'], 500);
        }
    }

    public function imprimirReporteIngresos(Request $request)
    {
        $fecha_inicio = $request->input('fecha_inicio', date('Y-m-d', strtotime('-1 month')));
        $fecha_final = $request->input('fecha_final', date('Y-m-d'));
        $tipo_filtro = $request->input('tipo', 'TODOS');
        $buscar_filtro = strtolower($request->input('buscar', ''));

        $q_capital = DB::table('pago')->join('cuota', 'pago.id_cuota', '=', 'cuota.id')
            ->where('pago.estado', 1)->where('pago.pago_capital', '>', 0)
            ->whereDate('pago.fecha_pago', '<=', $fecha_final)
            ->select('pago.fecha_pago as fecha', DB::raw("'CAPITAL' as tipo"), DB::raw("CONCAT('CAPITAL, CREDITO: ', cuota.id_plan_pago, ', CUOTA: ', cuota.numero) as descripcion"), 'pago.pago_capital as debe', DB::raw('0 as haber'), 'pago.created_at');

        $q_interes = DB::table('pago')->join('cuota', 'pago.id_cuota', '=', 'cuota.id')
            ->where('pago.estado', 1)->where('pago.pago_interes', '>', 0)
            ->whereDate('pago.fecha_pago', '<=', $fecha_final)
            ->select('pago.fecha_pago as fecha', DB::raw("'INTERES' as tipo"), DB::raw("CONCAT('INTERES, CREDITO: ', cuota.id_plan_pago, ', CUOTA: ', cuota.numero) as descripcion"), 'pago.pago_interes as debe', DB::raw('0 as haber'), 'pago.created_at');

        $q_mora = DB::table('pago')->join('cuota', 'pago.id_cuota', '=', 'cuota.id')
            ->where('pago.estado', 1)->where('pago.pago_mora', '>', 0)
            ->whereDate('pago.fecha_pago', '<=', $fecha_final)
            ->select('pago.fecha_pago as fecha', DB::raw("'MORA' as tipo"), DB::raw("CONCAT('MORA, CREDITO: ', cuota.id_plan_pago, ', CUOTA: ', cuota.numero) as descripcion"), 'pago.pago_mora as debe', DB::raw('0 as haber'), 'pago.created_at');

        $q_desembolso = DB::table('desembolso')->where('estado', 0)->whereDate('fecha', '<=', $fecha_final)
            ->select('fecha', DB::raw("'DESEMBOLSO' as tipo"), DB::raw("CONCAT('DESEMBOLSO, CREDITO: ', id_plan_pago) as descripcion"), DB::raw('0 as debe'), 'monto as haber', 'fecha as created_at');

        $q_gastosadm = DB::table('pago_administrativo')->where('estado', 0)->where('monto', '>', 0)
            ->whereDate('fecha', '<=', $fecha_final)
            ->select('fecha', DB::raw("'GASTOSADM' as tipo"), DB::raw("CONCAT('GASTOSADM, CREDITO: ', id_plan_pago) as descripcion"), 'monto as debe', DB::raw('0 as haber'), 'fecha as created_at');

        $q_ingreso = DB::table('ingreso')->where('estado', 1)->whereDate('fecha', '<=', $fecha_final)
            ->select('fecha', DB::raw("'INGRESO_CAJA' as tipo"), 'descripcion', 'monto as debe', DB::raw('0 as haber'), 'created_at');

        $q_gasto = DB::table('egreso')->where('estado', 1)->whereDate('fecha', '<=', $fecha_final)
            ->select('fecha', DB::raw("'EGRESO_CAJA' as tipo"), 'descripcion', DB::raw('0 as debe'), 'monto as haber', 'created_at');

        $movimientos = $q_capital->unionAll($q_interes)->unionAll($q_mora)->unionAll($q_desembolso)
            ->unionAll($q_gastosadm)->unionAll($q_ingreso)->unionAll($q_gasto)
            ->orderBy('fecha', 'asc')->orderBy('created_at', 'asc')->get();

        

        $lista_final = [];
        $nro = 1;
        $total_ingresos = 0;

        foreach ($movimientos as $mov) {
            // FILTRO ESTRICTO: Solo queremos los que sumen al DEBE (Ingresos)
            if ($mov->debe <= 0) continue; 

            if ($mov->fecha >= $fecha_inicio) {
                if ($tipo_filtro !== 'TODOS' && $mov->tipo !== $tipo_filtro) continue;
                if ($buscar_filtro !== '' && !str_contains(strtolower($mov->descripcion), $buscar_filtro)) continue;

                $total_ingresos += $mov->debe;

                $lista_final[] = (object)[
                    'nro' => $nro++,
                    'fecha' => date('d/m/Y', strtotime($mov->fecha)),
                    'tipo' => $mov->tipo,
                    'descripcion' => $mov->descripcion,
                    'monto' => (float)$mov->debe
                ];
            }
        }

        $data = [
            'empresa' => DB::table('mi_empresa')->first(),
            'movimientos' => $lista_final,
            'fecha_inicio' => $fecha_inicio,
            'fecha_final' => $fecha_final,
            'tipo_filtro' => $tipo_filtro,
            'total' => $total_ingresos
        ];

        return $this->generatePDF($data, 'reporte.reporte_ingresos', 'Reporte_Ingresos_' . date('Y-m-d'));
    }

    public function imprimirReporteEgresos(Request $request)
    {
        $fecha_inicio = $request->input('fecha_inicio', date('Y-m-d', strtotime('-1 month')));
        $fecha_final = $request->input('fecha_final', date('Y-m-d'));
        $tipo_filtro = $request->input('tipo', 'TODOS');
        $buscar_filtro = strtolower($request->input('buscar', ''));

        $q_capital = DB::table('pago')->join('cuota', 'pago.id_cuota', '=', 'cuota.id')
            ->where('pago.estado', 1)->where('pago.pago_capital', '>', 0)
            ->whereDate('pago.fecha_pago', '<=', $fecha_final)
            ->select('pago.fecha_pago as fecha', DB::raw("'CAPITAL' as tipo"), DB::raw("CONCAT('CAPITAL, CREDITO: ', cuota.id_plan_pago, ', CUOTA: ', cuota.numero) as descripcion"), 'pago.pago_capital as debe', DB::raw('0 as haber'), 'pago.created_at');

        $q_interes = DB::table('pago')->join('cuota', 'pago.id_cuota', '=', 'cuota.id')
            ->where('pago.estado', 1)->where('pago.pago_interes', '>', 0)
            ->whereDate('pago.fecha_pago', '<=', $fecha_final)
            ->select('pago.fecha_pago as fecha', DB::raw("'INTERES' as tipo"), DB::raw("CONCAT('INTERES, CREDITO: ', cuota.id_plan_pago, ', CUOTA: ', cuota.numero) as descripcion"), 'pago.pago_interes as debe', DB::raw('0 as haber'), 'pago.created_at');

        $q_mora = DB::table('pago')->join('cuota', 'pago.id_cuota', '=', 'cuota.id')
            ->where('pago.estado', 1)->where('pago.pago_mora', '>', 0)
            ->whereDate('pago.fecha_pago', '<=', $fecha_final)
            ->select('pago.fecha_pago as fecha', DB::raw("'MORA' as tipo"), DB::raw("CONCAT('MORA, CREDITO: ', cuota.id_plan_pago, ', CUOTA: ', cuota.numero) as descripcion"), 'pago.pago_mora as debe', DB::raw('0 as haber'), 'pago.created_at');

        $q_desembolso = DB::table('desembolso')->where('estado', 0)->whereDate('fecha', '<=', $fecha_final)
            ->select('fecha', DB::raw("'DESEMBOLSO' as tipo"), DB::raw("CONCAT('DESEMBOLSO, CREDITO: ', id_plan_pago) as descripcion"), DB::raw('0 as debe'), 'monto as haber', 'fecha as created_at');

        $q_gastosadm = DB::table('pago_administrativo')->where('estado', 0)->where('monto', '>', 0)
            ->whereDate('fecha', '<=', $fecha_final)
            ->select('fecha', DB::raw("'GASTOSADM' as tipo"), DB::raw("CONCAT('GASTOSADM, CREDITO: ', id_plan_pago) as descripcion"), 'monto as debe', DB::raw('0 as haber'), 'fecha as created_at');

        $q_ingreso = DB::table('ingreso')->where('estado', 1)->whereDate('fecha', '<=', $fecha_final)
            ->select('fecha', DB::raw("'INGRESO_CAJA' as tipo"), 'descripcion', 'monto as debe', DB::raw('0 as haber'), 'created_at');

        $q_gasto = DB::table('egreso')->where('estado', 1)->whereDate('fecha', '<=', $fecha_final)
            ->select('fecha', DB::raw("'EGRESO_CAJA' as tipo"), 'descripcion', DB::raw('0 as debe'), 'monto as haber', 'created_at');

        $movimientos = $q_capital->unionAll($q_interes)->unionAll($q_mora)->unionAll($q_desembolso)
            ->unionAll($q_gastosadm)->unionAll($q_ingreso)->unionAll($q_gasto)
            ->orderBy('fecha', 'asc')->orderBy('created_at', 'asc')->get();

        

        $lista_final = [];
        $nro = 1;
        $total_egresos = 0;

        foreach ($movimientos as $mov) {
            // FILTRO ESTRICTO: Solo queremos los que sumen al HABER (Egresos)
            if ($mov->haber <= 0) continue; 

            if ($mov->fecha >= $fecha_inicio) {
                if ($tipo_filtro !== 'TODOS' && $mov->tipo !== $tipo_filtro) continue;
                if ($buscar_filtro !== '' && !str_contains(strtolower($mov->descripcion), $buscar_filtro)) continue;

                $total_egresos += $mov->haber;

                $lista_final[] = (object)[
                    'nro' => $nro++,
                    'fecha' => date('d/m/Y', strtotime($mov->fecha)),
                    'tipo' => $mov->tipo,
                    'descripcion' => $mov->descripcion,
                    'monto' => (float)$mov->haber
                ];
            }
        }

        $data = [
            'empresa' => DB::table('mi_empresa')->first(),
            'movimientos' => $lista_final,
            'fecha_inicio' => $fecha_inicio,
            'fecha_final' => $fecha_final,
            'tipo_filtro' => $tipo_filtro,
            'total' => $total_egresos
        ];

        return $this->generatePDF($data, 'reporte.reporte_egresos', 'Reporte_Egresos_' . date('Y-m-d'));
    }

    public function exportarExcelLibroMayor(Request $request)
    {
        $fecha_inicio = $request->input('fecha_inicio', date('Y-m-d', strtotime('-1 month')));
        $fecha_final = $request->input('fecha_final', date('Y-m-d'));
        $tipo_filtro = $request->input('tipo', 'TODOS');
        $buscar_filtro = strtolower($request->input('buscar', ''));

        // 1. CONSTRUIR CONSULTAS (Igual a tu Libro Mayor)
        $q_capital = DB::table('pago')->join('cuota', 'pago.id_cuota', '=', 'cuota.id')
            ->where('pago.estado', 1)->where('pago.pago_capital', '>', 0)->whereDate('pago.fecha_pago', '<=', $fecha_final)
            ->select('pago.fecha_pago as fecha', DB::raw("'CAPITAL' as tipo"), DB::raw("CONCAT('CAPITAL, CREDITO: ', cuota.id_plan_pago, ', CUOTA: ', cuota.numero) as descripcion"), 'pago.pago_capital as debe', DB::raw('0 as haber'), 'pago.created_at');

        $q_interes = DB::table('pago')->join('cuota', 'pago.id_cuota', '=', 'cuota.id')
            ->where('pago.estado', 1)->where('pago.pago_interes', '>', 0)->whereDate('pago.fecha_pago', '<=', $fecha_final)
            ->select('pago.fecha_pago as fecha', DB::raw("'INTERES' as tipo"), DB::raw("CONCAT('INTERES, CREDITO: ', cuota.id_plan_pago, ', CUOTA: ', cuota.numero) as descripcion"), 'pago.pago_interes as debe', DB::raw('0 as haber'), 'pago.created_at');

        $q_mora = DB::table('pago')->join('cuota', 'pago.id_cuota', '=', 'cuota.id')
            ->where('pago.estado', 1)->where('pago.pago_mora', '>', 0)->whereDate('pago.fecha_pago', '<=', $fecha_final)
            ->select('pago.fecha_pago as fecha', DB::raw("'MORA' as tipo"), DB::raw("CONCAT('MORA, CREDITO: ', cuota.id_plan_pago, ', CUOTA: ', cuota.numero) as descripcion"), 'pago.pago_mora as debe', DB::raw('0 as haber'), 'pago.created_at');

        $q_desembolso = DB::table('desembolso')->where('estado', 0)->whereDate('fecha', '<=', $fecha_final)
            ->select('fecha', DB::raw("'DESEMBOLSO' as tipo"), DB::raw("CONCAT('DESEMBOLSO, CREDITO: ', id_plan_pago) as descripcion"), DB::raw('0 as debe'), 'monto as haber', 'fecha as created_at');

        $q_gastosadm = DB::table('pago_administrativo')->where('estado', 0)->where('monto', '>', 0)->whereDate('fecha', '<=', $fecha_final)
            ->select('fecha', DB::raw("'GASTOSADM' as tipo"), DB::raw("CONCAT('GASTOSADM, CREDITO: ', id_plan_pago) as descripcion"), 'monto as debe', DB::raw('0 as haber'), 'fecha as created_at');

        $q_ingreso = DB::table('ingreso')->where('estado', 1)->whereDate('fecha', '<=', $fecha_final)
            ->select('fecha', DB::raw("'INGRESO_CAJA' as tipo"), 'descripcion', 'monto as debe', DB::raw('0 as haber'), 'created_at');

        $q_gasto = DB::table('egreso')->where('estado', 1)->whereDate('fecha', '<=', $fecha_final)
            ->select('fecha', DB::raw("'EGRESO_CAJA' as tipo"), 'descripcion', DB::raw('0 as debe'), 'monto as haber', 'created_at');

        $movimientos = $q_capital->unionAll($q_interes)->unionAll($q_mora)->unionAll($q_desembolso)
            ->unionAll($q_gastosadm)->unionAll($q_ingreso)->unionAll($q_gasto)
            ->orderBy('fecha', 'asc')->orderBy('created_at', 'asc')->get();

        // 2. CALCULAR SALDOS
        $saldo_acumulado = 0;
        $lista_final = [];
        $nro = 1;
        $suma_ingresos = 0;
        $suma_egresos = 0;

        foreach ($movimientos as $mov) {
            $saldo_acumulado += $mov->debe;
            $saldo_acumulado -= $mov->haber;

            if ($mov->fecha >= $fecha_inicio) {
                if ($tipo_filtro !== 'TODOS' && $mov->tipo !== $tipo_filtro) continue;
                if ($buscar_filtro !== '' && !str_contains(strtolower($mov->descripcion), $buscar_filtro)) continue;

                $suma_ingresos += $mov->debe;
                $suma_egresos += $mov->haber;

                $lista_final[] = (object)[
                    'nro' => $nro++,
                    'fecha' => date('d/m/Y', strtotime($mov->fecha)),
                    'tipo' => $mov->tipo,
                    'descripcion' => $mov->descripcion,
                    'debe' => (float)$mov->debe,
                    'haber' => (float)$mov->haber,
                    'saldo' => round($saldo_acumulado, 2)
                ];
            }
        }

        $empresa = DB::table('mi_empresa')->first();

        $data = [
            'empresa' => $empresa,
            'movimientos' => $lista_final,
            'fecha_inicio' => $fecha_inicio,
            'fecha_final' => $fecha_final,
            'total_ingresos' => $suma_ingresos,
            'total_egresos' => $suma_egresos
        ];

        // 3. RETORNAR COMO EXCEL NATIVO
        return response(view('reporte.excel_libro_mayor', $data))
            ->header('Content-Type', 'application/vnd.ms-excel; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="Libro_Mayor_'.date('Y-m-d').'.xls"');
    }

    public function exportarExcelIngresos(Request $request)
    {
        $fecha_inicio = $request->input('fecha_inicio', date('Y-m-d', strtotime('-1 month')));
        $fecha_final = $request->input('fecha_final', date('Y-m-d'));
        $tipo_filtro = $request->input('tipo', 'TODOS');
        $buscar_filtro = strtolower($request->input('buscar', ''));

        // 1. CONSTRUIR CONSULTAS (Igual a tu Libro Mayor)
        $q_capital = DB::table('pago')->join('cuota', 'pago.id_cuota', '=', 'cuota.id')
            ->where('pago.estado', 1)->where('pago.pago_capital', '>', 0)->whereDate('pago.fecha_pago', '<=', $fecha_final)
            ->select('pago.fecha_pago as fecha', DB::raw("'CAPITAL' as tipo"), DB::raw("CONCAT('CAPITAL, CREDITO: ', cuota.id_plan_pago, ', CUOTA: ', cuota.numero) as descripcion"), 'pago.pago_capital as debe', DB::raw('0 as haber'), 'pago.created_at');

        $q_interes = DB::table('pago')->join('cuota', 'pago.id_cuota', '=', 'cuota.id')
            ->where('pago.estado', 1)->where('pago.pago_interes', '>', 0)->whereDate('pago.fecha_pago', '<=', $fecha_final)
            ->select('pago.fecha_pago as fecha', DB::raw("'INTERES' as tipo"), DB::raw("CONCAT('INTERES, CREDITO: ', cuota.id_plan_pago, ', CUOTA: ', cuota.numero) as descripcion"), 'pago.pago_interes as debe', DB::raw('0 as haber'), 'pago.created_at');

        $q_mora = DB::table('pago')->join('cuota', 'pago.id_cuota', '=', 'cuota.id')
            ->where('pago.estado', 1)->where('pago.pago_mora', '>', 0)->whereDate('pago.fecha_pago', '<=', $fecha_final)
            ->select('pago.fecha_pago as fecha', DB::raw("'MORA' as tipo"), DB::raw("CONCAT('MORA, CREDITO: ', cuota.id_plan_pago, ', CUOTA: ', cuota.numero) as descripcion"), 'pago.pago_mora as debe', DB::raw('0 as haber'), 'pago.created_at');

        $q_desembolso = DB::table('desembolso')->where('estado', 0)->whereDate('fecha', '<=', $fecha_final)
            ->select('fecha', DB::raw("'DESEMBOLSO' as tipo"), DB::raw("CONCAT('DESEMBOLSO, CREDITO: ', id_plan_pago) as descripcion"), DB::raw('0 as debe'), 'monto as haber', 'fecha as created_at');

        $q_gastosadm = DB::table('pago_administrativo')->where('estado', 0)->where('monto', '>', 0)->whereDate('fecha', '<=', $fecha_final)
            ->select('fecha', DB::raw("'GASTOSADM' as tipo"), DB::raw("CONCAT('GASTOSADM, CREDITO: ', id_plan_pago) as descripcion"), 'monto as debe', DB::raw('0 as haber'), 'fecha as created_at');

        $q_ingreso = DB::table('ingreso')->where('estado', 1)->whereDate('fecha', '<=', $fecha_final)
            ->select('fecha', DB::raw("'INGRESO_CAJA' as tipo"), 'descripcion', 'monto as debe', DB::raw('0 as haber'), 'created_at');

        $q_gasto = DB::table('egreso')->where('estado', 1)->whereDate('fecha', '<=', $fecha_final)
            ->select('fecha', DB::raw("'EGRESO_CAJA' as tipo"), 'descripcion', DB::raw('0 as debe'), 'monto as haber', 'created_at');

        $movimientos = $q_capital->unionAll($q_interes)->unionAll($q_mora)->unionAll($q_desembolso)
            ->unionAll($q_gastosadm)->unionAll($q_ingreso)->unionAll($q_gasto)
            ->orderBy('fecha', 'asc')->orderBy('created_at', 'asc')->get();

        $lista_final = [];
        $nro = 1;
        $total_ingresos = 0;

        foreach ($movimientos as $mov) {
            if ($mov->debe <= 0) continue; // SOLO INGRESOS

            if ($mov->fecha >= $fecha_inicio) {
                if ($tipo_filtro !== 'TODOS' && $mov->tipo !== $tipo_filtro) continue;
                if ($buscar_filtro !== '' && !str_contains(strtolower($mov->descripcion), $buscar_filtro)) continue;

                $total_ingresos += $mov->debe;

                $lista_final[] = (object)[
                    'nro' => $nro++,
                    'fecha' => date('d/m/Y', strtotime($mov->fecha)),
                    'tipo' => $mov->tipo,
                    'descripcion' => $mov->descripcion,
                    'monto' => (float)$mov->debe
                ];
            }
        }

        $data = [
            'empresa' => DB::table('mi_empresa')->first(),
            'movimientos' => $lista_final,
            'fecha_inicio' => $fecha_inicio,
            'fecha_final' => $fecha_final,
            'total' => $total_ingresos
        ];

        return response(view('reporte.excel_ingresos', $data))
            ->header('Content-Type', 'application/vnd.ms-excel; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="Ingresos_'.date('Y-m-d').'.xls"');
    }

    public function exportarExcelEgresos(Request $request)
    {
        $fecha_inicio = $request->input('fecha_inicio', date('Y-m-d', strtotime('-1 month')));
        $fecha_final = $request->input('fecha_final', date('Y-m-d'));
        $tipo_filtro = $request->input('tipo', 'TODOS');
        $buscar_filtro = strtolower($request->input('buscar', ''));

        // 1. CONSTRUIR CONSULTAS (Igual a tu Libro Mayor)
        $q_capital = DB::table('pago')->join('cuota', 'pago.id_cuota', '=', 'cuota.id')
            ->where('pago.estado', 1)->where('pago.pago_capital', '>', 0)->whereDate('pago.fecha_pago', '<=', $fecha_final)
            ->select('pago.fecha_pago as fecha', DB::raw("'CAPITAL' as tipo"), DB::raw("CONCAT('CAPITAL, CREDITO: ', cuota.id_plan_pago, ', CUOTA: ', cuota.numero) as descripcion"), 'pago.pago_capital as debe', DB::raw('0 as haber'), 'pago.created_at');

        $q_interes = DB::table('pago')->join('cuota', 'pago.id_cuota', '=', 'cuota.id')
            ->where('pago.estado', 1)->where('pago.pago_interes', '>', 0)->whereDate('pago.fecha_pago', '<=', $fecha_final)
            ->select('pago.fecha_pago as fecha', DB::raw("'INTERES' as tipo"), DB::raw("CONCAT('INTERES, CREDITO: ', cuota.id_plan_pago, ', CUOTA: ', cuota.numero) as descripcion"), 'pago.pago_interes as debe', DB::raw('0 as haber'), 'pago.created_at');

        $q_mora = DB::table('pago')->join('cuota', 'pago.id_cuota', '=', 'cuota.id')
            ->where('pago.estado', 1)->where('pago.pago_mora', '>', 0)->whereDate('pago.fecha_pago', '<=', $fecha_final)
            ->select('pago.fecha_pago as fecha', DB::raw("'MORA' as tipo"), DB::raw("CONCAT('MORA, CREDITO: ', cuota.id_plan_pago, ', CUOTA: ', cuota.numero) as descripcion"), 'pago.pago_mora as debe', DB::raw('0 as haber'), 'pago.created_at');

        $q_desembolso = DB::table('desembolso')->where('estado', 0)->whereDate('fecha', '<=', $fecha_final)
            ->select('fecha', DB::raw("'DESEMBOLSO' as tipo"), DB::raw("CONCAT('DESEMBOLSO, CREDITO: ', id_plan_pago) as descripcion"), DB::raw('0 as debe'), 'monto as haber', 'fecha as created_at');

        $q_gastosadm = DB::table('pago_administrativo')->where('estado', 0)->where('monto', '>', 0)->whereDate('fecha', '<=', $fecha_final)
            ->select('fecha', DB::raw("'GASTOSADM' as tipo"), DB::raw("CONCAT('GASTOSADM, CREDITO: ', id_plan_pago) as descripcion"), 'monto as debe', DB::raw('0 as haber'), 'fecha as created_at');

        $q_ingreso = DB::table('ingreso')->where('estado', 1)->whereDate('fecha', '<=', $fecha_final)
            ->select('fecha', DB::raw("'INGRESO_CAJA' as tipo"), 'descripcion', 'monto as debe', DB::raw('0 as haber'), 'created_at');

        $q_gasto = DB::table('egreso')->where('estado', 1)->whereDate('fecha', '<=', $fecha_final)
            ->select('fecha', DB::raw("'EGRESO_CAJA' as tipo"), 'descripcion', DB::raw('0 as debe'), 'monto as haber', 'created_at');

        $movimientos = $q_capital->unionAll($q_interes)->unionAll($q_mora)->unionAll($q_desembolso)
            ->unionAll($q_gastosadm)->unionAll($q_ingreso)->unionAll($q_gasto)
            ->orderBy('fecha', 'asc')->orderBy('created_at', 'asc')->get();

        $lista_final = [];
        $nro = 1;
        $total_egresos = 0;

        foreach ($movimientos as $mov) {
            if ($mov->haber <= 0) continue; // SOLO EGRESOS

            if ($mov->fecha >= $fecha_inicio) {
                if ($tipo_filtro !== 'TODOS' && $mov->tipo !== $tipo_filtro) continue;
                if ($buscar_filtro !== '' && !str_contains(strtolower($mov->descripcion), $buscar_filtro)) continue;

                $total_egresos += $mov->haber;

                $lista_final[] = (object)[
                    'nro' => $nro++,
                    'fecha' => date('d/m/Y', strtotime($mov->fecha)),
                    'tipo' => $mov->tipo,
                    'descripcion' => $mov->descripcion,
                    'monto' => (float)$mov->haber
                ];
            }
        }

        $data = [
            'empresa' => DB::table('mi_empresa')->first(),
            'movimientos' => $lista_final,
            'fecha_inicio' => $fecha_inicio,
            'fecha_final' => $fecha_final,
            'total' => $total_egresos
        ];

        return response(view('reporte.excel_egresos', $data))
            ->header('Content-Type', 'application/vnd.ms-excel; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="Egresos_'.date('Y-m-d').'.xls"');
    }

}