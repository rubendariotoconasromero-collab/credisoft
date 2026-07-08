<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConsultaFinancieraController extends Controller
{
    public function index()
    {
        return view('frmConsultaFinanciera');
    }

    // =========================================================
    // 1. MÉTODO PRINCIPAL: LISTADO PARA VUE CON PAGINACIÓN
    // =========================================================
    public function getLibroMayor(Request $request)
    {
        $fecha_final = $request->input('fecha_final', date('Y-m-d'));
        $tipo_libro = $request->input('tipo_libro', 'GENERAL');

        $movimientos = $this->obtenerConsultaSQLBase($fecha_final, $tipo_libro);
        $procesado = $this->procesarMovimientosYCalcularSaldos($movimientos, $request, 'TODOS');

        // Capital Total REAL de bóveda+caja al corte (no depende de filtros ni del tipo de libro).
        // Se calcula sobre el conjunto GENERAL completo; TRANSFER_INTERNO es neto cero.
        $movGeneral = ($tipo_libro === 'GENERAL')
            ? $movimientos
            : $this->obtenerConsultaSQLBase($fecha_final, 'GENERAL');
        $capital_total_real = 0;
        foreach ($movGeneral as $m) {
            $capital_total_real += ($m->debe - $m->haber);
        }
        $capital_total_real = round($capital_total_real, 2);

        // Paginación Manual del Arreglo (solo para tabla General)
        $page = (int)$request->input('page', 1);
        $perPage = 15;
        $total = count($procesado['lista']);
        $items = array_slice($procesado['lista'], ($page - 1) * $perPage, $perPage);

        // Listas completas para tabs Ingresos/Egresos (sin paginar, renumeradas)
        $nroIng = 1;
        $nroEgr = 1;
        $lista_ingresos = [];
        $lista_egresos = [];
        foreach ($procesado['lista'] as $item) {
            if ($item->debe > 0 && $item->tipo !== 'TRANSFER_INTERNO') {
                $copy = clone $item;
                $copy->nro = $nroIng++;
                $lista_ingresos[] = $copy;
            }
            if ($item->haber > 0 && $item->tipo !== 'TRANSFER_INTERNO') {
                $copy = clone $item;
                $copy->nro = $nroEgr++;
                $lista_egresos[] = $copy;
            }
        }

        return response()->json([
            'movimientos' => [
                'current_page' => $page,
                'data'         => $items,
                'last_page'    => max(1, (int)ceil($total / $perPage)),
                'total'        => $total,
            ],
            'ingresos_lista' => $lista_ingresos,
            'egresos_lista'  => $lista_egresos,
            'totales' => [
                'ingresos' => round($procesado['ingresos'], 2),
                'egresos'  => round($procesado['egresos'], 2),
            ],
            'saldo_boveda'       => $procesado['saldo_boveda'],     // saldo final del periodo (según filtros)
            'saldo_anterior'     => $procesado['saldo_anterior'],   // saldo de apertura del periodo
            'capital_total_real' => $capital_total_real,            // bóveda+caja real al corte (sin filtros)
        ]);
    }

    // =========================================================
    // 1.b INFORMACIÓN DEL CRÉDITO ASOCIADO A UN MOVIMIENTO
    // =========================================================
    public function getInfoCreditoAsociado(Request $request)
    {
        $id_plan_pago = $request->input('id_plan_pago');

        if (empty($id_plan_pago)) {
            return response()->json(['error' => 'No se especificó el crédito.'], 422);
        }

        $info = DB::table('plan_pago')
            ->join('solicitud', 'solicitud.id', '=', 'plan_pago.id_solicitud')
            ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
            ->join('users', 'users.id', '=', 'solicitud.id_usuario')
            ->where('plan_pago.id', $id_plan_pago)
            ->select(
                'plan_pago.id as plan_pago_id',
                'solicitud.id as credito_id',
                'solicitud.importe_solicitud',
                'solicitud.moneda',
                'solicitud.tasa',
                'solicitud.nro_cuotas',
                'solicitud.lapso_capital',
                'solicitud.fecha_desembolso',
                'solicitud.destino_prestamo',
                'solicitud.tipo_garantia',
                'plan_pago.total_pagar',
                'plan_pago.saldo_pendiente',
                'plan_pago.fecha_inicio',
                'plan_pago.fecha_fin',
                'plan_pago.estado as estado_plan',
                'cliente.id as cliente_id',
                'cliente.nombre as cliente',
                'cliente.ci',
                'cliente.lugar_expedicion',
                'users.personal as asesor'
            )
            ->first();

        if (!$info) {
            return response()->json(['error' => 'No se encontró el crédito asociado.'], 404);
        }

        // Resumen de cuotas del plan
        $cuotas = DB::table('cuota')->where('id_plan_pago', $id_plan_pago)->get();
        $hoy = now()->startOfDay();

        $info->total_cuotas      = $cuotas->count();
        $info->cuotas_pagadas    = $cuotas->where('estado', 2)->count();
        $info->cuotas_pendientes = $cuotas->whereIn('estado', [1, 3])->count();
        $info->cuotas_vencidas   = $cuotas->whereIn('estado', [1, 3])
            ->filter(fn($c) => \Carbon\Carbon::parse($c->fecha)->startOfDay()->lt($hoy))
            ->count();

        // Teléfonos de contacto del cliente
        $info->telefonos = DB::table('telefono')
            ->where('id_cliente', $info->cliente_id)
            ->pluck('numero')
            ->toArray();

        // Dirección del cliente (tabla aparte)
        $dir = DB::table('direccion')->where('id_cliente', $info->cliente_id)->first();
        $info->direccion = $dir
            ? collect([$dir->descripcion, $dir->zona, $dir->ciudad, $dir->departamento])
                ->filter()
                ->implode(', ')
            : null;

        return response()->json($info);
    }

    // =========================================================
    // 2. MÉTODOS DE REPORTES Y EXCEL
    // =========================================================
    public function imprimirLibroMayor(Request $request)
    {
        $fecha_final = $request->input('fecha_final', date('Y-m-d'));
        $tipo_libro = $request->input('tipo_libro', 'GENERAL');
        
        $movimientos = $this->obtenerConsultaSQLBase($fecha_final, $tipo_libro);
        $procesado = $this->procesarMovimientosYCalcularSaldos($movimientos, $request, 'TODOS');

        $data = $this->prepararDataReporte($request, $procesado);
        return $this->generatePDF($data, 'reporte.libro_mayor', 'Reporte_Libro_Mayor_' . date('Y-m-d'));
    }

    public function exportarExcelLibroMayor(Request $request)
    {
        $fecha_final = $request->input('fecha_final', date('Y-m-d'));
        $tipo_libro = $request->input('tipo_libro', 'GENERAL');
        
        $movimientos = $this->obtenerConsultaSQLBase($fecha_final, $tipo_libro);
        $procesado = $this->procesarMovimientosYCalcularSaldos($movimientos, $request, 'TODOS');

        $data = $this->prepararDataReporte($request, $procesado);
        return response(view('reporte.excel_libro_mayor', $data))
            ->header('Content-Type', 'application/vnd.ms-excel; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="Libro_Mayor_'.date('Y-m-d').'.xls"');
    }

    public function imprimirReporteIngresos(Request $request)
    {
        $fecha_final = $request->input('fecha_final', date('Y-m-d'));
        $tipo_libro = $request->input('tipo_libro', 'GENERAL');
        
        $movimientos = $this->obtenerConsultaSQLBase($fecha_final, $tipo_libro);
        $procesado = $this->procesarMovimientosYCalcularSaldos($movimientos, $request, 'INGRESOS');

        $data = $this->prepararDataReporte($request, $procesado);
        $data['total'] = $data['total_ingresos']; // Renombrar variable para la vista
        return $this->generatePDF($data, 'reporte.reporte_ingresos', 'Reporte_Ingresos_' . date('Y-m-d'));
    }

    public function exportarExcelIngresos(Request $request)
    {
        $fecha_final = $request->input('fecha_final', date('Y-m-d'));
        $tipo_libro = $request->input('tipo_libro', 'GENERAL');
        
        $movimientos = $this->obtenerConsultaSQLBase($fecha_final, $tipo_libro);
        $procesado = $this->procesarMovimientosYCalcularSaldos($movimientos, $request, 'INGRESOS');

        $data = $this->prepararDataReporte($request, $procesado);
        $data['total'] = $data['total_ingresos'];
        return response(view('reporte.excel_ingresos', $data))
            ->header('Content-Type', 'application/vnd.ms-excel; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="Ingresos_'.date('Y-m-d').'.xls"');
    }

    public function imprimirReporteEgresos(Request $request)
    {
        $fecha_final = $request->input('fecha_final', date('Y-m-d'));
        $tipo_libro = $request->input('tipo_libro', 'GENERAL');
        
        $movimientos = $this->obtenerConsultaSQLBase($fecha_final, $tipo_libro);
        $procesado = $this->procesarMovimientosYCalcularSaldos($movimientos, $request, 'EGRESOS');

        $data = $this->prepararDataReporte($request, $procesado);
        $data['total'] = $data['total_egresos'];
        return $this->generatePDF($data, 'reporte.reporte_egresos', 'Reporte_Egresos_' . date('Y-m-d'));
    }

    public function exportarExcelEgresos(Request $request)
    {
        $fecha_final = $request->input('fecha_final', date('Y-m-d'));
        $tipo_libro = $request->input('tipo_libro', 'GENERAL');
        
        $movimientos = $this->obtenerConsultaSQLBase($fecha_final, $tipo_libro);
        $procesado = $this->procesarMovimientosYCalcularSaldos($movimientos, $request, 'EGRESOS');

        $data = $this->prepararDataReporte($request, $procesado);
        $data['total'] = $data['total_egresos'];
        return response(view('reporte.excel_egresos', $data))
            ->header('Content-Type', 'application/vnd.ms-excel; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="Egresos_'.date('Y-m-d').'.xls"');
    }


    // =========================================================
    // 3. FUNCIONES AYUDANTES PRIVADAS (REFACTORIZACIÓN)
    // =========================================================

    private function obtenerConsultaSQLBase($fecha_final, $tipo_libro)
    {
        // BASES QUE VAN EN AMBOS LIBROS
        $q_interes = DB::table('pago')->join('cuota', 'pago.id_cuota', '=', 'cuota.id')
            ->where('pago.estado', 1)->where('pago.pago_interes', '>', 0)->whereDate('pago.fecha_pago', '<=', $fecha_final)
            ->select('pago.fecha_pago as fecha', DB::raw("'INTERES' as tipo"),
                DB::raw("CONCAT('INTERES, CREDITO: ', cuota.id_plan_pago, ', CUOTA: ', cuota.numero, ' | ', UPPER(COALESCE(pago.tipo_pago, 'COMPLETO'))) as descripcion"),
                'pago.pago_interes as debe', DB::raw('0 as haber'), 'pago.created_at', 'cuota.id_plan_pago as id_plan_pago');

        $q_mora = DB::table('pago')->join('cuota', 'pago.id_cuota', '=', 'cuota.id')
            ->where('pago.estado', 1)->where('pago.pago_mora', '>', 0)->whereDate('pago.fecha_pago', '<=', $fecha_final)
            ->select('pago.fecha_pago as fecha', DB::raw("'MORA' as tipo"),
                DB::raw("CONCAT('MORA, CREDITO: ', cuota.id_plan_pago, ', CUOTA: ', cuota.numero, ' | ', UPPER(COALESCE(pago.tipo_pago, 'COMPLETO'))) as descripcion"),
                'pago.pago_mora as debe', DB::raw('0 as haber'), 'pago.created_at', 'cuota.id_plan_pago as id_plan_pago');

        $q_gastosadm = DB::table('pago_administrativo')->where('estado', 0)->where('monto', '>', 0)->whereDate('fecha', '<=', $fecha_final)
            ->select('fecha', DB::raw("'GASTOSADM' as tipo"), DB::raw("CONCAT('GASTOSADM, CREDITO: ', id_plan_pago) as descripcion"), 'monto as debe', DB::raw('0 as haber'), 'fecha as created_at', 'id_plan_pago');

        // "Transferencia desde Bóveda" se excluye de INGRESO_CAJA porque
        // el lado bóveda ya aparece como TRANSFER_INTERNO (neto cero).
        // Incluirlo aquí inflaría el saldo corrido del Flujo de Caja.
        $q_ingreso = DB::table('ingreso')
            ->where('estado', 1)
            ->whereDate('fecha', '<=', $fecha_final)
            ->where('descripcion', 'not like', '%Transferencia desde Bóveda%')
            ->select('fecha', DB::raw("'INGRESO_CAJA' as tipo"), 'descripcion', 'monto as debe', DB::raw('0 as haber'), 'created_at', DB::raw('NULL as id_plan_pago'));

        // "Transferencia a Bóveda" se excluye de EGRESO_CAJA porque
        // el lado bóveda aparece como TRANSFER_INTERNO (neto cero).
        $q_gasto = DB::table('egreso')
            ->where('estado', 1)
            ->whereDate('fecha', '<=', $fecha_final)
            ->where('descripcion', 'not like', '%Transferencia a Bóveda%')
            ->where('descripcion', 'not like', '%Transferencia a Boveda%')
            ->select('fecha', DB::raw("'EGRESO_CAJA' as tipo"), 'descripcion', DB::raw('0 as debe'), 'monto as haber', 'created_at', DB::raw('NULL as id_plan_pago'));

        $query = $q_interes->unionAll($q_mora)->unionAll($q_gastosadm)->unionAll($q_ingreso)->unionAll($q_gasto);

        // SOLO SE AGREGAN AL LIBRO GENERAL
        if ($tipo_libro === 'GENERAL') {
            $q_capital = DB::table('pago')->join('cuota', 'pago.id_cuota', '=', 'cuota.id')
                ->where('pago.estado', 1)->where('pago.pago_capital', '>', 0)->whereDate('pago.fecha_pago', '<=', $fecha_final)
                ->select('pago.fecha_pago as fecha', DB::raw("'CAPITAL' as tipo"),
                    DB::raw("CONCAT('CAPITAL, CREDITO: ', cuota.id_plan_pago, ', CUOTA: ', cuota.numero, ' | ', UPPER(COALESCE(pago.tipo_pago, 'COMPLETO'))) as descripcion"),
                    'pago.pago_capital as debe', DB::raw('0 as haber'), 'pago.created_at', 'cuota.id_plan_pago as id_plan_pago');

            $q_desembolso = DB::table('desembolso')->where('estado', 0)->whereDate('fecha', '<=', $fecha_final)
                ->select('fecha', DB::raw("'DESEMBOLSO' as tipo"), DB::raw("CONCAT('DESEMBOLSO, CREDITO: ', id_plan_pago) as descripcion"), DB::raw('0 as debe'), 'monto as haber', 'fecha as created_at', 'id_plan_pago');

            // Ingresos REALES a bóveda (excluye "Transferencia desde Caja" — es transferencia interna)
            $q_boveda_in = DB::table('movimientos_boveda')
                ->where('tipo_movimiento', 'ingreso')
                ->where('descripcion', 'not like', '%Transferencia desde Caja%')
                ->whereDate('fecha', '<=', $fecha_final)
                ->select('fecha', DB::raw("'BOVEDA_INGRESO' as tipo"), 'descripcion', 'monto as debe', DB::raw('0 as haber'), 'created_at', DB::raw('NULL as id_plan_pago'));

            // Egresos REALES de bóveda (excluye otorgamiento de préstamos y "Transferencia a Caja" — es interna)
            $q_boveda_out = DB::table('movimientos_boveda')
                ->where('tipo_movimiento', 'salida')
                ->where('descripcion', '!=', 'Otorgamiento de préstamo')
                ->where('descripcion', 'not like', '%Transferencia a Caja%')
                ->whereDate('fecha', '<=', $fecha_final)
                ->select('fecha', DB::raw("'BOVEDA_EGRESO' as tipo"), 'descripcion', DB::raw('0 as debe'), 'monto as haber', 'created_at', DB::raw('NULL as id_plan_pago'));

            // Transferencias internas bóveda ↔ caja: debe = haber → neto cero en el saldo
            // "Transferencia a Caja" (salida de bóveda) y "Transferencia desde Caja" (retorno a bóveda)
            $q_transfer_interno = DB::table('movimientos_boveda')
                ->whereDate('fecha', '<=', $fecha_final)
                ->where(function ($q) {
                    $q->where(function ($q2) {
                        $q2->where('tipo_movimiento', 'salida')
                           ->where('descripcion', 'like', '%Transferencia a Caja%');
                    })->orWhere(function ($q2) {
                        $q2->where('tipo_movimiento', 'ingreso')
                           ->where('descripcion', 'like', '%Transferencia desde Caja%');
                    })->orWhere(function ($q2) {
                        $q2->where('tipo_movimiento', 'ingreso')
                           ->where('descripcion', 'like', '%Retorno de Caja al Cierre%');
                    });
                })
                ->select('fecha', DB::raw("'TRANSFER_INTERNO' as tipo"), 'descripcion', 'monto as debe', 'monto as haber', 'created_at', DB::raw('NULL as id_plan_pago'));

            $query = $query->unionAll($q_capital)->unionAll($q_desembolso)
                           ->unionAll($q_boveda_in)->unionAll($q_boveda_out)
                           ->unionAll($q_transfer_interno);
        }

        return $query->orderBy('fecha', 'asc')->orderBy('created_at', 'asc')->get();
    }

    private function procesarMovimientosYCalcularSaldos($movimientos, $request, $modo_reporte = 'TODOS')
    {
        $fecha_inicio = $request->input('fecha_inicio', date('Y-m-d', strtotime('-1 month')));
        $tipo_filtro = $request->input('tipo', 'TODOS');
        $buscar_filtro = strtolower($request->input('buscar', ''));

        $saldo_acumulado = 0;
        $saldo_anterior = 0;
        $lista_final = [];
        $nro = 1;
        $suma_ingresos = 0;
        $suma_egresos = 0;

        foreach ($movimientos as $mov) {
            // --- Filtros que determinan si el movimiento ENTRA al libro ---
            // Se aplican ANTES de acumular el saldo para que el saldo corrido se
            // calcule SOLO sobre las filas visibles. Así cada fila cumple siempre:
            //   saldo = saldo anterior + debe - haber
            if ($modo_reporte === 'INGRESOS' && ($mov->debe <= 0 || $mov->tipo === 'TRANSFER_INTERNO')) continue;
            if ($modo_reporte === 'EGRESOS'  && ($mov->haber <= 0 || $mov->tipo === 'TRANSFER_INTERNO')) continue;
            if ($tipo_filtro !== 'TODOS' && $mov->tipo !== $tipo_filtro) continue;
            if ($buscar_filtro !== '' && !str_contains(strtolower($mov->descripcion), $buscar_filtro)) continue;

            // Saldo corrido sobre las filas que pasaron los filtros
            $saldo_acumulado += $mov->debe;
            $saldo_acumulado -= $mov->haber;

            $fecha_mov = date('Y-m-d', strtotime($mov->fecha));

            if ($fecha_mov < $fecha_inicio) {
                // Antes del periodo: solo arrastra el saldo de apertura, no se lista.
                $saldo_anterior = $saldo_acumulado;
                continue;
            }

            // Las transferencias internas son neto cero — no deben inflar los totales
            if ($mov->tipo !== 'TRANSFER_INTERNO') {
                $suma_ingresos += $mov->debe;
                $suma_egresos  += $mov->haber;
            }

            $lista_final[] = (object)[
                'nro' => $nro++,
                'fecha' => date('d/m/Y', strtotime($mov->fecha)),
                'tipo' => $mov->tipo,
                'descripcion' => $mov->descripcion,
                'debe' => (float)$mov->debe,
                'haber' => (float)$mov->haber,
                'monto' => $mov->debe > 0 ? (float)$mov->debe : (float)$mov->haber,
                'saldo' => round($saldo_acumulado, 2),
                'id_plan_pago' => $mov->id_plan_pago ?? null,
            ];
        }

        return [
            'lista'          => $lista_final,
            'ingresos'       => $suma_ingresos,
            'egresos'        => $suma_egresos,
            'saldo_boveda'   => round($saldo_acumulado, 2),
            'saldo_anterior' => round($saldo_anterior, 2),
        ];
    }

    private function prepararDataReporte($request, $procesado)
    {
        return [
            'empresa' => DB::table('mi_empresa')->first(),
            'movimientos' => $procesado['lista'],
            'fecha_inicio' => $request->input('fecha_inicio'),
            'fecha_final' => $request->input('fecha_final'),
            'tipo_filtro' => $request->input('tipo', 'TODOS'),
            'total_ingresos' => $procesado['ingresos'],
            'total_egresos' => $procesado['egresos']
        ];
    }

    private function generatePDF($data, $url_vista, $nombre_reporte)
    {
        try {
            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8', 'format' => 'letter', 'orientation' => 'P',
                'margin_left' => 10, 'margin_right' => 10, 'margin_top' => 10, 'margin_bottom' => 10,
            ]);

            $mpdf->SetHTMLFooter('<div style="text-align: center; font-size: 10px;">Página {PAGENO} de {nbpg}</div>');
            $html = view($url_vista, $data)->render();
            $mpdf->WriteHTML($html);
            $mpdf->Output($nombre_reporte . '.pdf', 'I');
        } catch (\Exception $e) {
            \Log::error('Error al generar el PDF: ' . $e->getMessage());
            return response()->json(['error' => 'No se pudo generar el PDF.'], 500);
        }
    }
}