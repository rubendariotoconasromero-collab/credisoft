<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;

class CajaMovimientosController extends Controller
{
 
    public function getListado(Request $request)
    {
        $query = $this->obtenerDatosFiltrados($request);

        return response()->json($query->paginate(10));
    }

    /**
     * Genera el PDF con la lista filtrada
     */
    public function generarReporteLista(Request $request)
    {

        $query = $this->obtenerDatosFiltrados($request);
        
        // Para el PDF ejecutamos get() para traer TODOS los resultados de ese filtro
        $data = $query->get(); 
        
        $total = $data->sum(function($item) {
            return $item->monto ?? $item->monto_pago ?? 0;
        });

        $nombresReporte = [
            'ingresos' => 'Reporte_Ingresos',
            'egresos' => 'Reporte_Gastos',
            'cobros' => 'Reporte_Cobros',
            'desembolsos' => 'Reporte_Desembolsos',
        ];

        $nombreArchivo = ($nombresReporte[$request->tipo] ?? 'Reporte_Movimientos') . '_' . date('Ymd_His');

        $viewData = [
            'lista' => $data,
            'total' => $total,
            'tipo' => $request->tipo,
            'filtros' => $request->only(['fecha_inicio', 'fecha_fin', 'buscar'])
        ];

        // Asegúrate de crear esta vista en resources/views/reportes/caja_movimientos.blade.php
        return $this->generatePDF($viewData, 'reporte.caja.caja_movimientos', $nombreArchivo);
    }

    /**
     * Método centralizado para armar las consultas según el TIPO y los FILTROS
     */
    private function obtenerDatosFiltrados(Request $request)
    {
        $query = null;

        // 1. Determinar qué tabla consultar
        switch ($request->tipo) {
            case 'ingresos_corrientes':
                $query = DB::table('ingreso')->where('id_caja', $request->id_caja)
                           ->select('id', 'fecha', 'descripcion', 'monto', 'created_at');
                break;
                
            case 'gastos_corrientes':
                $query = DB::table('egreso')->where('id_caja', $request->id_caja)
                           ->select('id', 'fecha', 'descripcion', 'monto', 'created_at');
                break;

            case 'cobros_cuotas':
                $query = DB::table('pago')->where('id_caja', $request->id_caja)
                           ->join('cuota', 'pago.id_cuota', '=', 'cuota.id')
                           ->join('plan_pago', 'cuota.id_plan_pago', '=', 'plan_pago.id')
                           ->join('solicitud', 'plan_pago.id_solicitud', '=', 'solicitud.id')
                           ->join('cliente', 'solicitud.id_cliente', '=', 'cliente.id')
                           ->select(
                               'pago.id', 
                               'pago.fecha_pago as fecha', 
                               'pago.monto_pago as monto', 
                               'pago.created_at',
                               DB::raw("'Cobro de Cuota N° ' as descripcion"),
                               'cliente.nombre as cliente'
                           );
                break;

            case 'desembolsos':
                $query = DB::table('desembolso')->where('id_caja', $request->id_caja)
                           ->join('plan_pago', 'desembolso.id_plan_pago', '=', 'plan_pago.id')
                           ->join('solicitud', 'plan_pago.id_solicitud', '=', 'solicitud.id')
                           ->join('cliente', 'solicitud.id_cliente', '=', 'cliente.id')
                           ->select(
                               'desembolso.id', 
                               'desembolso.fecha', 
                               'desembolso.monto', 
                               DB::raw("'Desembolso de Préstamo' as descripcion"),
                               'cliente.nombre as cliente'
                           );
                break;

            default:
                return collect([]); // Array vacío si el tipo no es válido
        }

        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            
            // ¡AQUÍ ESTÁ LA MAGIA! Le decimos exactamente a qué tabla pertenece la fecha
            $columnaFecha = match($request->tipo) {
                'ingresos_corrientes'    => 'ingreso.fecha',
                'gastos_corrientes'     => 'egreso.fecha',
                'cobros_cuotas'      => 'pago.fecha_pago',
                'desembolsos' => 'desembolso.fecha', // <-- Evita la ambigüedad con solicitud.fecha
                default       => 'fecha'
            };

            $query->whereDate($columnaFecha, '>=', $request->fecha_inicio)
                  ->whereDate($columnaFecha, '<=', $request->fecha_fin);
        }

        // 3. Aplicar Filtro de Búsqueda (Texto)
        if ($request->filled('buscar')) {
            $buscar = '%' . $request->buscar . '%';
            
            $query->where(function($q) use ($buscar, $request) {
                if (in_array($request->tipo, ['ingresos', 'egresos'])) {
                    $q->where('descripcion', 'LIKE', $buscar);
                } else {
                    $q->where('cliente.nombre', 'LIKE', $buscar);
                }
            });
        }
        return $query->orderBy('id', 'desc');
    }

    /**
     * Método privado provisto por el usuario para generar PDF
     */
    private function generatePDF($data, $url_vista, $nombre_reporte)
    {
        try {
            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'letter',
                'orientation' => 'P',
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 10,
                'margin_bottom' => 10,
            ]);

            $mpdf->SetHTMLFooter('
                <div style="text-align: center; font-size: 10px; color: #666;">
                    Página {PAGENO} de {nbpg}
                </div>
            ');

            $html = view($url_vista, $data)->render();
            $mpdf->WriteHTML($html);
            $mpdf->Output($nombre_reporte . '.pdf', 'I');

        } catch (\Exception $e) {
            \Log::error('Error generating PDF: ' . $e->getMessage());
            return response()->json(['error' => 'No se pudo generar el PDF.'], 500);
        }
    }
}