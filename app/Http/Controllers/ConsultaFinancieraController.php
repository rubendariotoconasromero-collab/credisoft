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
            if ($item->debe > 0) {
                $copy = clone $item;
                $copy->nro = $nroIng++;
                $lista_ingresos[] = $copy;
            }
            if ($item->haber > 0) {
                $copy = clone $item;
                $copy->nro = $nroEgr++;
                $lista_egresos[] = $copy;
            }
        }

        return response()->json([
            'movimientos' => [
                'current_page' => $page,
                'data' => $items,
                'last_page' => max(1, (int)ceil($total / $perPage)),
                'total' => $total,
            ],
            'ingresos_lista' => $lista_ingresos,
            'egresos_lista' => $lista_egresos,
            'totales' => [
                'ingresos' => round($procesado['ingresos'], 2),
                'egresos' => round($procesado['egresos'], 2)
            ]
        ]);
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
            ->select('pago.fecha_pago as fecha', DB::raw("'INTERES' as tipo"), DB::raw("CONCAT('INTERES, CREDITO: ', cuota.id_plan_pago, ', CUOTA: ', cuota.numero) as descripcion"), 'pago.pago_interes as debe', DB::raw('0 as haber'), 'pago.created_at');

        $q_mora = DB::table('pago')->join('cuota', 'pago.id_cuota', '=', 'cuota.id')
            ->where('pago.estado', 1)->where('pago.pago_mora', '>', 0)->whereDate('pago.fecha_pago', '<=', $fecha_final)
            ->select('pago.fecha_pago as fecha', DB::raw("'MORA' as tipo"), DB::raw("CONCAT('MORA, CREDITO: ', cuota.id_plan_pago, ', CUOTA: ', cuota.numero) as descripcion"), 'pago.pago_mora as debe', DB::raw('0 as haber'), 'pago.created_at');

        $q_gastosadm = DB::table('pago_administrativo')->where('estado', 0)->where('monto', '>', 0)->whereDate('fecha', '<=', $fecha_final)
            ->select('fecha', DB::raw("'GASTOSADM' as tipo"), DB::raw("CONCAT('GASTOSADM, CREDITO: ', id_plan_pago) as descripcion"), 'monto as debe', DB::raw('0 as haber'), 'fecha as created_at');

        $q_ingreso = DB::table('ingreso')->where('estado', 1)->whereDate('fecha', '<=', $fecha_final)
            ->select('fecha', DB::raw("'INGRESO_CAJA' as tipo"), 'descripcion', 'monto as debe', DB::raw('0 as haber'), 'created_at');

        $q_gasto = DB::table('egreso')->where('estado', 1)->whereDate('fecha', '<=', $fecha_final)
            ->select('fecha', DB::raw("'EGRESO_CAJA' as tipo"), 'descripcion', DB::raw('0 as debe'), 'monto as haber', 'created_at');

        $query = $q_interes->unionAll($q_mora)->unionAll($q_gastosadm)->unionAll($q_ingreso)->unionAll($q_gasto);

        // SOLO SE AGREGAN AL LIBRO GENERAL
        if ($tipo_libro === 'GENERAL') {
            $q_capital = DB::table('pago')->join('cuota', 'pago.id_cuota', '=', 'cuota.id')
                ->where('pago.estado', 1)->where('pago.pago_capital', '>', 0)->whereDate('pago.fecha_pago', '<=', $fecha_final)
                ->select('pago.fecha_pago as fecha', DB::raw("'CAPITAL' as tipo"), DB::raw("CONCAT('CAPITAL, CREDITO: ', cuota.id_plan_pago, ', CUOTA: ', cuota.numero) as descripcion"), 'pago.pago_capital as debe', DB::raw('0 as haber'), 'pago.created_at');

            $q_desembolso = DB::table('desembolso')->where('estado', 0)->whereDate('fecha', '<=', $fecha_final)
                ->select('fecha', DB::raw("'DESEMBOLSO' as tipo"), DB::raw("CONCAT('DESEMBOLSO, CREDITO: ', id_plan_pago) as descripcion"), DB::raw('0 as debe'), 'monto as haber', 'fecha as created_at');

            $q_boveda_in = DB::table('movimientos_boveda')
                ->where('tipo_movimiento', 'ingreso')->whereDate('fecha', '<=', $fecha_final)
                ->select('fecha', DB::raw("'BOVEDA_INGRESO' as tipo"), 'descripcion', 'monto as debe', DB::raw('0 as haber'), 'created_at');

            $q_boveda_out = DB::table('movimientos_boveda')
                ->where('tipo_movimiento', 'salida')->where('descripcion', '!=', 'Otorgamiento de préstamo')->whereDate('fecha', '<=', $fecha_final)
                ->select('fecha', DB::raw("'BOVEDA_EGRESO' as tipo"), 'descripcion', DB::raw('0 as debe'), 'monto as haber', 'created_at');

            $query = $query->unionAll($q_capital)->unionAll($q_desembolso)->unionAll($q_boveda_in)->unionAll($q_boveda_out);
        }

        return $query->orderBy('fecha', 'asc')->orderBy('created_at', 'asc')->get();
    }

    private function procesarMovimientosYCalcularSaldos($movimientos, $request, $modo_reporte = 'TODOS')
    {
        $fecha_inicio = $request->input('fecha_inicio', date('Y-m-d', strtotime('-1 month')));
        $tipo_filtro = $request->input('tipo', 'TODOS');
        $buscar_filtro = strtolower($request->input('buscar', ''));

        $saldo_acumulado = 0;
        $lista_final = [];
        $nro = 1;
        $suma_ingresos = 0;
        $suma_egresos = 0;

        foreach ($movimientos as $mov) {
            $saldo_acumulado += $mov->debe;
            $saldo_acumulado -= $mov->haber;

            // Filtro por tipo de reporte (Ingresos/Egresos puros)
            if ($modo_reporte === 'INGRESOS' && $mov->debe <= 0) continue;
            if ($modo_reporte === 'EGRESOS' && $mov->haber <= 0) continue;

            $fecha_mov = date('Y-m-d', strtotime($mov->fecha));

            if ($fecha_mov >= $fecha_inicio) {
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

        return [
            'lista' => $lista_final,
            'ingresos' => $suma_ingresos,
            'egresos' => $suma_egresos
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