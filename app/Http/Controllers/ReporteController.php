<?php

namespace App\Http\Controllers;
use DB;
use PDF;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mpdf\Mpdf;
use Carbon\Carbon;
use App\Models\Cliente;
use Illuminate\Support\Facades\View;




class ReporteController extends Controller
{
    //

    

    public function index(){
        return view ('frmReporte');
    }

    public function listadoClientes(){
        // 1. OPTIMIZACIÓN: Traemos clientes con sus teléfonos y direcciones en 1 sola consulta
        $clientes = Cliente::with(['telefonos', 'direcciones'])->get();

        $mi_empresa = DB::table('mi_empresa')->first();

        // 2. Lógica del Logo (Movida de la vista al controlador para limpieza)
        $logoName = empty($mi_empresa->logo) ? 'logo_sistema_codesoft.png' : $mi_empresa->logo;
        $path = public_path('img/' . $logoName);
        $logo_base64 = '';

        if (file_exists($path)) {
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $dataImg = file_get_contents($path);
            $logo_base64 = 'data:image/' . $type . ';base64,' . base64_encode($dataImg);
        }

        // 3. Preparamos el array de datos
        $data = [
            'clientes'    => $clientes,
            'usuario'     => Auth::user()->name,
            'fecha'       => now()->format('d/m/Y'),
            'hora'        => now()->format('H:i'),
            'mi_empresa'  => $mi_empresa,
            'logo_base64' => $logo_base64, // Pasamos el logo ya procesado
            'title'       => 'Listado General de Clientes'
        ];

        // Generamos el PDF
        $this->generatePDF($data, 'reporte.reporte_general_cliente', 'reporte_general_cliente.pdf');
    }



    public function getPlanesPagoCliente(Request $request){
        // desembolso
        // lapso_capital
        
        $planes_pago = DB::table('solicitud')
        ->join('plan_pago', 'plan_pago.id_solicitud', '=', 'solicitud.id')
        ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
        ->join('users', 'users.id', '=', 'solicitud.id_usuario')
        ->select('solicitud.id','solicitud.importe_solicitud', 'solicitud.moneda', 'solicitud.lapso_capital', 'solicitud.nro_cuotas',
        'solicitud.tasa', 'solicitud.fecha_desembolso', 'solicitud.fecha_primera_cuota', 'solicitud.destino_prestamo',
        'solicitud.tipo_garantia','solicitud.tipo_desembolso','solicitud.id_cliente', 'solicitud.id_usuario', 'cliente.nombre as cliente',
        'users.name as asesor', 'solicitud.estado', 'plan_pago.id as id_plan_pago', 'solicitud.tipo_solicitud', 'solicitud.desembolso')
        ->where('solicitud.id_cliente', $request->id_cliente)
        ->orderBy('solicitud.id', 'desc')
        ->get();

        return $planes_pago;
    }

    public function getPlanesPagoGeneral(Request $request)
    {
        $query = DB::table('solicitud')
            ->join('plan_pago', 'plan_pago.id_solicitud', '=', 'solicitud.id')
            ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
            ->join('users', 'users.id', '=', 'solicitud.id_usuario')
            ->leftJoin('solicitud_codeudor', 'solicitud_codeudor.id_solicitud', '=', 'solicitud.id')
            ->leftJoin('codeudor', 'codeudor.id', '=', 'solicitud_codeudor.id_codeudor')
            ->select(
                'solicitud.id',
                'solicitud.importe_solicitud',
                'solicitud.moneda',
                'plan_pago.fecha_inicio',
                'plan_pago.fecha_fin',
                'solicitud.lapso_capital',
                'solicitud.nro_cuotas',
                'solicitud.tasa',
                'solicitud.fecha_desembolso',
                'solicitud.fecha_primera_cuota',
                'solicitud.destino_prestamo',
                'solicitud.tipo_garantia',
                'solicitud.tipo_desembolso',
                'solicitud.id_cliente',
                'solicitud.id_usuario',
                'cliente.nombre as cliente',
                'users.name as asesor',
                'solicitud.estado',
                'plan_pago.id as id_plan_pago',
                'solicitud.tipo_solicitud',
                'solicitud.desembolso',
                DB::raw('GROUP_CONCAT(codeudor.nombre SEPARATOR ",") as codeudores')
            )
            ->groupBy(
                'solicitud.id',
                'solicitud.importe_solicitud',
                'solicitud.moneda',
                'solicitud.lapso_capital',
                'solicitud.nro_cuotas',
                'solicitud.tasa',
                'solicitud.fecha_desembolso',
                'plan_pago.fecha_inicio',
                'plan_pago.fecha_fin',
                'solicitud.fecha_primera_cuota',
                'solicitud.destino_prestamo',
                'solicitud.tipo_garantia',
                'solicitud.tipo_desembolso',
                'solicitud.id_cliente',
                'solicitud.id_usuario',
                'cliente.nombre',
                'users.name',
                'solicitud.estado',
                'plan_pago.id',
                'solicitud.tipo_solicitud',
                'solicitud.desembolso'
            );

        // Filtro por criterio y búsqueda
        if ($request->filled('criterio') && $request->filled('buscar')) {
            $criterio = $request->criterio;
            $buscar = $request->buscar;

            $query->where($criterio, 'LIKE', '%' . $buscar . '%');
            
        }

        // Filtro por estado
        if ($request->filled('estado') && $request->estado!='todos') {
            $fechaActual = Carbon::now()->format('Y-m-d');
            // Filtros existentes (asesor y estado)
            if ($request->estado == 'vigentes') {
                $query->where('plan_pago.estado', 1)
                        ->whereDate('plan_pago.fecha_fin', '>=', $fechaActual);
            } elseif ($request->estado == 'vencidos') {
                $query->where('plan_pago.estado', 1)
                        ->whereDate('plan_pago.fecha_fin', '<', $fechaActual);
            }
        }

        // Filtro por asesor
        if ($request->filled('asesor') && $request->asesor!='0') {
            $query->where('solicitud.id_usuario', $request->asesor);
        }

        // Filtro por rango de fechas (fecha_desembolso)
        if ($request->filled('fecha_inicio')) {
            $query->whereDate('plan_pago.fecha_inicio', '>=', $request->fecha_inicio);
        }
        if ($request->filled('fecha_fin')) {
            $query->whereDate('plan_pago.fecha_inicio', '<=', $request->fecha_fin);
        }

        // Ordenar y paginar
        $planes_pago = $query->orderBy('plan_pago.id', 'desc')->paginate(20);

        return $planes_pago;
    }

    public function reportePlanesCuotasCliente(Request $request)
    {
        // Fetch main information
        $informacion = DB::table('solicitud')
            ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
            ->join('users', 'users.id', '=', 'solicitud.id_usuario')
            ->join('plan_pago', 'plan_pago.id_solicitud', '=', 'solicitud.id')
            ->where('plan_pago.id', $request->id_plan_pago)
            ->select(
                'cliente.ci',
                'cliente.lugar_expedicion',
                'cliente.nombre as cliente',
                'solicitud.nro_cuotas',
                'solicitud.lapso_capital',
                'solicitud.tipo_garantia',
                'solicitud.fecha as fecha_solicitud',
                'solicitud.moneda',
                'solicitud.importe_solicitud',
                'solicitud.monto_pago_adm',
                'solicitud.lapso_capital',
                'plan_pago.id as id_plan_pago',
                'plan_pago.fecha_inicio as fecha_credito',
            )
            ->first(); // Use first() since we expect one record

        // Fetch cuotas
        $monto_pago_adm=DB::table('pago_administrativo')->where('id_plan_pago', $request->id_plan_pago)->first()->monto;
        $cuotas = DB::table('plan_pago')
            ->join('cuota', 'plan_pago.id', '=', 'cuota.id_plan_pago')
            ->where('plan_pago.id', $request->id_plan_pago)
            ->select(
                'cuota.numero as nro',
                'cuota.fecha',
                'cuota.capital',
                'cuota.interes',
                'cuota.saldo_capital',
                'cuota.total as total_cuota'
            )
            ->where('cuota.estado', '!=', 3) // Exclude estado 3 as per original template
            ->get();


        // Prepare data for the view
        $html = [
            'informacion'=>$informacion,
            'detalles' => $cuotas,
            'monto_pago_adm' => $monto_pago_adm,
            'usuario' => Auth::user()->name,
            'fecha_reporte' => now()->format('d/m/Y'),
        ];

        // Generate the PDF
        $this->generatePDF($html, 'reporte.reporte_cuotas_planpago', 'reporte_plan_pago_cliente');
    }

    public function reporteGeneralSolicitudes(Request $request){
        $solicitudes = DB::table('solicitud')
        ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
        ->join('users', 'users.id', '=', 'solicitud.id_usuario')
        ->select('solicitud.id','solicitud.importe_solicitud', 'solicitud.moneda', 'solicitud.lapso_capital', 'solicitud.nro_cuotas',
        'solicitud.tasa', 'solicitud.fecha_desembolso', 'solicitud.fecha_primera_cuota', 'solicitud.destino_prestamo',
        'solicitud.tipo_garantia','solicitud.tipo_desembolso','solicitud.id_cliente', 'solicitud.id_usuario', 'cliente.nombre as cliente',
        'users.name as asesor', 'solicitud.estado')
        ->whereDate('solicitud.fecha', '>=', $request->fecha_inicio)
        ->whereDate('solicitud.fecha', '<=', $request->fecha_final)
        ->whereIn('solicitud.estado', [empty($request->nuevo)?-1:1,empty($request->anulado)?-1:0, empty($request->aprobado)?-1:2])
        ->orderBy('solicitud.id', 'desc')
        ->get();

   

        // Carga la vista HTML para el reporte
        $html = [
            'solicitudes' => $solicitudes, // Pasa los datos del cliente a la vista
            'usuario' => Auth::user()->name,
            'fecha_reporte' => now()->format('d/m/Y'),
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_final' => $request->fecha_final,
            'nuevo'=>empty($request->nuevo)?'':'Nuevo',
            'anulado'=>empty($request->anulado)?'':'Anulado',
            'aprobado'=>empty($request->aprobado)?'':'Aprobado',
            // 'telefonos' => $telefonos, // Pasa los datos de teléfonos a la vista
            // 'direcciones' => $direcciones, // Pasa los datos de direcciones a la vista
        ];

        $this->generatePDF($html, 'reporte.reporte_general_solicitudes', 'reporte_general_solicitudes');


    }

    public function reporteSolicitudesAprobadas(){
        $solicitudes = DB::table('solicitud')
        ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
        ->join('users', 'users.id', '=', 'solicitud.id_usuario')
        ->select('solicitud.id','solicitud.importe_solicitud', 'solicitud.moneda', 'solicitud.lapso_capital', 'solicitud.nro_cuotas',
        'solicitud.tasa', 'solicitud.fecha_desembolso', 'solicitud.fecha_primera_cuota', 'solicitud.destino_prestamo',
        'solicitud.tipo_garantia','solicitud.tipo_desembolso','solicitud.id_cliente', 'solicitud.id_usuario', 'cliente.nombre as cliente',
        'users.name as asesor', 'solicitud.estado')
        ->where('solicitud.estado', 2)
        ->orderBy('solicitud.id', 'desc')
        ->get();

        // Crea una instancia de Dompdf
        $dompdf = new Dompdf();

        // Opciones de configuración de Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true); // Habilita el parser HTML5
        $options->set('isPhpEnabled', true); // Habilita la ejecución de código PHP en la vista (si es necesario)
        $dompdf->setOptions($options);

        // Carga la vista HTML para el reporte
        $html = view('reporte.reporte_solicitudes_aprobadas', [
            'solicitudes' => $solicitudes, // Pasa los datos del cliente a la vista
            // 'telefonos' => $telefonos, // Pasa los datos de teléfonos a la vista
            // 'direcciones' => $direcciones, // Pasa los datos de direcciones a la vista
        ])->render();

        // Carga el contenido HTML en Dompdf
        $dompdf->loadHtml($html);

        // Renderiza el PDF (esto puede tomar un tiempo si el contenido es grande)
        $dompdf->render();

        // Obtén el contenido del PDF como una cadena
        

        // Establece las cabeceras para mostrar el PDF en una nueva pestaña
        return response($dompdf->output())

        ->header('Content-Type', 'application/pdf')
        ->header('Content-Disposition', 'inline; filename="reporte_solicitudes_aprobadas.pdf"');


    }

    public function reporteSolicitudesPorAprobar(){
        $solicitudes = DB::table('solicitud')
        ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
        ->join('users', 'users.id', '=', 'solicitud.id_usuario')
        ->select('solicitud.id','solicitud.importe_solicitud', 'solicitud.moneda', 'solicitud.lapso_capital', 'solicitud.nro_cuotas',
        'solicitud.tasa', 'solicitud.fecha_desembolso', 'solicitud.fecha_primera_cuota', 'solicitud.destino_prestamo',
        'solicitud.tipo_garantia','solicitud.tipo_desembolso','solicitud.id_cliente', 'solicitud.id_usuario', 'cliente.nombre as cliente',
        'users.name as asesor', 'solicitud.estado')
        ->where('solicitud.estado', 1)
        ->orderBy('solicitud.id', 'desc')
        ->get();

        // Crea una instancia de Dompdf
        $dompdf = new Dompdf();

        // Opciones de configuración de Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true); // Habilita el parser HTML5
        $options->set('isPhpEnabled', true); // Habilita la ejecución de código PHP en la vista (si es necesario)
        $dompdf->setOptions($options);

        // Carga la vista HTML para el reporte
        $html = view('reporte.reporte_solicitudes_por_aprobar', [
            'solicitudes' => $solicitudes, // Pasa los datos del cliente a la vista
            // 'telefonos' => $telefonos, // Pasa los datos de teléfonos a la vista
            // 'direcciones' => $direcciones, // Pasa los datos de direcciones a la vista
        ])->render();

        // Carga el contenido HTML en Dompdf
        $dompdf->loadHtml($html);

        // Renderiza el PDF (esto puede tomar un tiempo si el contenido es grande)
        $dompdf->render();

        // Obtén el contenido del PDF como una cadena
        

        // Establece las cabeceras para mostrar el PDF en una nueva pestaña
        return response($dompdf->output())

        ->header('Content-Type', 'application/pdf')
        ->header('Content-Disposition', 'inline; filename="reporte_solicitudes_por_aprobar.pdf"');
    }

    public function reporteSolicitudesAnuladas(){
        $solicitudes = DB::table('solicitud')
        ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
        ->join('users', 'users.id', '=', 'solicitud.id_usuario')
        ->select('solicitud.id','solicitud.importe_solicitud', 'solicitud.moneda', 'solicitud.lapso_capital', 'solicitud.nro_cuotas',
        'solicitud.tasa', 'solicitud.fecha_desembolso', 'solicitud.fecha_primera_cuota', 'solicitud.destino_prestamo',
        'solicitud.tipo_garantia','solicitud.tipo_desembolso','solicitud.id_cliente', 'solicitud.id_usuario', 'cliente.nombre as cliente',
        'users.name as asesor', 'solicitud.estado')
        ->where('solicitud.estado', 0)
        ->orderBy('solicitud.id', 'desc')
        ->get();

        // Crea una instancia de Dompdf
        $dompdf = new Dompdf();

        // Opciones de configuración de Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true); // Habilita el parser HTML5
        $options->set('isPhpEnabled', true); // Habilita la ejecución de código PHP en la vista (si es necesario)
        $dompdf->setOptions($options);

        // Carga la vista HTML para el reporte
        $html = view('reporte.reporte_solicitudes_anuladas', [
            'solicitudes' => $solicitudes, // Pasa los datos del cliente a la vista
            // 'telefonos' => $telefonos, // Pasa los datos de teléfonos a la vista
            // 'direcciones' => $direcciones, // Pasa los datos de direcciones a la vista
        ])->render();

        // Carga el contenido HTML en Dompdf
        $dompdf->loadHtml($html);

        // Renderiza el PDF (esto puede tomar un tiempo si el contenido es grande)
        $dompdf->render();

        // Obtén el contenido del PDF como una cadena
        

        // Establece las cabeceras para mostrar el PDF en una nueva pestaña
        return response($dompdf->output())

        ->header('Content-Type', 'application/pdf')
        ->header('Content-Disposition', 'inline; filename="reporte_solicitudes_anuladas.pdf"');
    }

    public function reporteSolicitudesPorUsuario(Request $request){
        $solicitudes = DB::table('solicitud')
        ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
        ->join('users', 'users.id', '=', 'solicitud.id_usuario')
        ->select('solicitud.id','solicitud.importe_solicitud', 'solicitud.moneda', 'solicitud.lapso_capital', 'solicitud.nro_cuotas',
        'solicitud.tasa', 'solicitud.fecha_desembolso', 'solicitud.fecha_primera_cuota', 'solicitud.destino_prestamo',
        'solicitud.tipo_garantia','solicitud.tipo_desembolso','solicitud.id_cliente', 'solicitud.id_usuario', 'cliente.nombre as cliente',
        'users.name as asesor', 'solicitud.estado')
        ->where('solicitud.id_usuario', $request->id_usuario)
        ->whereIn('solicitud.estado', [empty($request->por_aprobar)?-1:1, empty($request->aprobados)?-1:2, empty($request->anulados)?-1:0])
        ->orderBy('solicitud.id', 'desc')
        ->get();
       
        $usuario = DB::table('users')->where('id', $request->id_usuario)->get();
    
        // Crea una instancia de Dompdf
        $dompdf = new Dompdf();

        // Opciones de configuración de Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true); // Habilita el parser HTML5
        $options->set('isPhpEnabled', true); // Habilita la ejecución de código PHP en la vista (si es necesario)
        $dompdf->setOptions($options);

        // Carga la vista HTML para el reporte
        $html = view('reporte.reporte_solicitudes_por_usuario', [
            'solicitudes' => $solicitudes, // Pasa los datos del cliente a la vista
            'usuario'=>$usuario,
            // 'telefonos' => $telefonos, // Pasa los datos de teléfonos a la vista
            // 'direcciones' => $direcciones, // Pasa los datos de direcciones a la vista
        ])->render();

        // Carga el contenido HTML en Dompdf
        $dompdf->loadHtml($html);

        // Renderiza el PDF (esto puede tomar un tiempo si el contenido es grande)
        $dompdf->render();

        // Obtén el contenido del PDF como una cadena
        

        // Establece las cabeceras para mostrar el PDF en una nueva pestaña
        return response($dompdf->output())

        ->header('Content-Type', 'application/pdf')
        ->header('Content-Disposition', 'inline; filename="reporte_solicitudes_por_usuario.pdf"');
    }

    public function reporteSolicitudesPorRango(Request $request){
        $solicitudes = DB::table('solicitud')
        ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
        ->join('users', 'users.id', '=', 'solicitud.id_usuario')
        ->select('solicitud.id','solicitud.importe_solicitud', 'solicitud.moneda', 'solicitud.lapso_capital', 'solicitud.nro_cuotas',
        'solicitud.tasa', 'solicitud.fecha_desembolso', 'solicitud.fecha_primera_cuota', 'solicitud.destino_prestamo',
        'solicitud.tipo_garantia','solicitud.tipo_desembolso','solicitud.id_cliente', 'solicitud.id_usuario', 'cliente.nombre as cliente',
        'users.name as asesor', 'solicitud.estado')
        ->whereDate('solicitud.fecha', '>=', $request->fecha_inicial)
        ->whereDate('solicitud.fecha', '<=', $request->fecha_final)
        // ->where('solicitud.id_usuario', $request->id_usuario)
        ->whereIn('solicitud.estado', [empty($request->por_aprobar)?-1:1, empty($request->aprobados)?-1:2, empty($request->anulados)?-1:0])
        ->orderBy('solicitud.id', 'desc')
        ->get();
       
        // $usuario = DB::table('users')->where('id', $request->id_usuario)->get();
    
        // Crea una instancia de Dompdf
        $dompdf = new Dompdf();

        // Opciones de configuración de Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true); // Habilita el parser HTML5
        $options->set('isPhpEnabled', true); // Habilita la ejecución de código PHP en la vista (si es necesario)
        $dompdf->setOptions($options);

        // Carga la vista HTML para el reporte
        $html = view('reporte.reporte_solicitudes_por_rango_fecha', [
            'solicitudes' => $solicitudes, // Pasa los datos del cliente a la vista
            'fecha_inicial'=>$request->fecha_inicial,
            'fecha_final'=>$request->fecha_final,
            // 'telefonos' => $telefonos, // Pasa los datos de teléfonos a la vista
            // 'direcciones' => $direcciones, // Pasa los datos de direcciones a la vista
        ])->render();

        // Carga el contenido HTML en Dompdf
        $dompdf->loadHtml($html);

        // Renderiza el PDF (esto puede tomar un tiempo si el contenido es grande)
        $dompdf->render();

        // Obtén el contenido del PDF como una cadena
        

        // Establece las cabeceras para mostrar el PDF en una nueva pestaña
        return response($dompdf->output())

        ->header('Content-Type', 'application/pdf')
        ->header('Content-Disposition', 'inline; filename="reporte_solicitudes_por_rango_fecha.pdf"');
    }

    public function reporteGeneralPlanesPago(Request $request){
        $planes_pago = DB::table('plan_pago')
            ->join('solicitud', 'solicitud.id', '=', 'plan_pago.id_solicitud')
            ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
            ->join('users', 'users.id', '=', 'solicitud.id_usuario')
            ->select('solicitud.id as id_solicitud','solicitud.importe_solicitud', 'solicitud.moneda', 'solicitud.lapso_capital', 'solicitud.nro_cuotas',
            'solicitud.tasa', 'solicitud.fecha_desembolso', 'solicitud.fecha_primera_cuota', 'solicitud.destino_prestamo',
            'solicitud.tipo_garantia','solicitud.tipo_desembolso','solicitud.id_cliente', 'solicitud.id_usuario', 'cliente.nombre as cliente',
            'users.name as asesor', 'solicitud.estado', 'plan_pago.fecha_inicio as fecha_inicio_plan', 'plan_pago.fecha_fin as fecha_fin_plan',
            'plan_pago.total_pagar as total_pagar_plan', 'plan_pago.estado as estado_plan', 'plan_pago.id',
            'cliente.ci', 'cliente.lugar_expedicion')
            ->whereDate('plan_pago.fecha_inicio', '>=', $request->fecha_inicio)
            ->whereDate('plan_pago.fecha_inicio', '<=', $request->fecha_final)
            ->whereIn('plan_pago.estado', [empty($request->nuevo)?-1:1, empty($request->cancelado)?-1:2, empty($request->anulado)?-1:0])
            
            ->orderBy('plan_pago.id', 'desc') 
            ->get();

        $html = [
            'planes_pago' => $planes_pago, // Pasa los datos del cliente a la vista
            'usuario' => Auth::user()->name,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_final' => $request->fecha_final,
            'fecha_reporte' => now()->format('d/m/Y'),
            'cancelado'=>empty($request->cancelado)?'':'Cancelado',
            'nuevo'=>empty($request->nuevo)?'':'Nuevo',
            'anulado'=>empty($request->anulado)?'':'Anulado',

        ];

        $this->generatePDF($html, 'reporte.reporte_general_planes_pago', 'reporte_general_planes_pago');


    }

    public function reportePlanesPagoEnProceso(){
        $planes_pago = DB::table('plan_pago')
            ->join('solicitud', 'solicitud.id', '=', 'plan_pago.id_solicitud')
            ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
            ->join('users', 'users.id', '=', 'solicitud.id_usuario')
            ->select('solicitud.id as id_solicitud','solicitud.importe_solicitud', 'solicitud.moneda', 'solicitud.lapso_capital', 'solicitud.nro_cuotas',
            'solicitud.tasa', 'solicitud.fecha_desembolso', 'solicitud.fecha_primera_cuota', 'solicitud.destino_prestamo',
            'solicitud.tipo_garantia','solicitud.tipo_desembolso','solicitud.id_cliente', 'solicitud.id_usuario', 'cliente.nombre as cliente',
            'users.name as asesor', 'solicitud.estado', 'plan_pago.fecha_inicio as fecha_inicio_plan', 'plan_pago.fecha_fin as fecha_fin_plan',
            'plan_pago.total_pagar as total_pagar_plan', 'plan_pago.estado as estado_plan', 'plan_pago.id',
            'cliente.ci', 'cliente.lugar_expedicion')
            ->where('plan_pago.estado', 1)
            ->orderBy('plan_pago.id', 'desc')
            ->get();
        // Crea una instancia de Dompdf
        $dompdf = new Dompdf();

        // Opciones de configuración de Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true); // Habilita el parser HTML5
        $options->set('isPhpEnabled', true); // Habilita la ejecución de código PHP en la vista (si es necesario)
        $dompdf->setOptions($options);

        // Carga la vista HTML para el reporte
        $html = view('reporte.reporte_planes_pago_en_proceso', [
            'planes_pago' => $planes_pago, // Pasa los datos del cliente a la vista
            
            // 'telefonos' => $telefonos, // Pasa los datos de teléfonos a la vista
            // 'direcciones' => $direcciones, // Pasa los datos de direcciones a la vista
        ])->render();

        // Carga el contenido HTML en Dompdf
        $dompdf->loadHtml($html);

        // Renderiza el PDF (esto puede tomar un tiempo si el contenido es grande)
        $dompdf->render();

        // Obtén el contenido del PDF como una cadena
        

        // Establece las cabeceras para mostrar el PDF en una nueva pestaña
        return response($dompdf->output())

        ->header('Content-Type', 'application/pdf')
        ->header('Content-Disposition', 'inline; filename="reporte_planes_pago_en_proceso.pdf"');
    }

    public function reportePlanesPagoCancelados(){
        $planes_pago = DB::table('plan_pago')
            ->join('solicitud', 'solicitud.id', '=', 'plan_pago.id_solicitud')
            ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
            ->join('users', 'users.id', '=', 'solicitud.id_usuario')
            ->select('solicitud.id as id_solicitud','solicitud.importe_solicitud', 'solicitud.moneda', 'solicitud.lapso_capital', 'solicitud.nro_cuotas',
            'solicitud.tasa', 'solicitud.fecha_desembolso', 'solicitud.fecha_primera_cuota', 'solicitud.destino_prestamo',
            'solicitud.tipo_garantia','solicitud.tipo_desembolso','solicitud.id_cliente', 'solicitud.id_usuario', 'cliente.nombre as cliente',
            'users.name as asesor', 'solicitud.estado', 'plan_pago.fecha_inicio as fecha_inicio_plan', 'plan_pago.fecha_fin as fecha_fin_plan',
            'plan_pago.total_pagar as total_pagar_plan', 'plan_pago.estado as estado_plan', 'plan_pago.id',
            'cliente.ci', 'cliente.lugar_expedicion')
            ->where('plan_pago.estado', 2)
            ->orderBy('plan_pago.id', 'desc')
            ->get();
        // Crea una instancia de Dompdf
        $dompdf = new Dompdf();

        // Opciones de configuración de Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true); // Habilita el parser HTML5
        $options->set('isPhpEnabled', true); // Habilita la ejecución de código PHP en la vista (si es necesario)
        $dompdf->setOptions($options);

        // Carga la vista HTML para el reporte
        $html = view('reporte.reporte_planes_pago_cancelados', [
            'planes_pago' => $planes_pago, // Pasa los datos del cliente a la vista
            
            // 'telefonos' => $telefonos, // Pasa los datos de teléfonos a la vista
            // 'direcciones' => $direcciones, // Pasa los datos de direcciones a la vista
        ])->render();

        // Carga el contenido HTML en Dompdf
        $dompdf->loadHtml($html);

        // Renderiza el PDF (esto puede tomar un tiempo si el contenido es grande)
        $dompdf->render();

        // Obtén el contenido del PDF como una cadena
        

        // Establece las cabeceras para mostrar el PDF en una nueva pestaña
        return response($dompdf->output())

        ->header('Content-Type', 'application/pdf')
        ->header('Content-Disposition', 'inline; filename="reporte_planes_pago_cancelados.pdf"');
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


    public function getPlanesPagoGeneralPdf(Request $request)
    {
        $query = DB::table('solicitud')
            ->join('plan_pago', 'plan_pago.id_solicitud', '=', 'solicitud.id')
            ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
            ->join('users', 'users.id', '=', 'solicitud.id_usuario')
            ->leftJoin('solicitud_codeudor', 'solicitud_codeudor.id_solicitud', '=', 'solicitud.id')
            ->leftJoin('codeudor', 'codeudor.id', '=', 'solicitud_codeudor.id_codeudor')
            ->select(
                'solicitud.id',
                'solicitud.importe_solicitud',
                'solicitud.moneda',
                'plan_pago.fecha_inicio',
                'plan_pago.fecha_fin',
                'solicitud.lapso_capital',
                'solicitud.nro_cuotas',
                'solicitud.tasa',
                'solicitud.fecha_desembolso',
                'solicitud.fecha_primera_cuota',
                'solicitud.destino_prestamo',
                'solicitud.tipo_garantia',
                'solicitud.tipo_desembolso',
                'solicitud.id_cliente',
                'solicitud.id_usuario',
                'cliente.nombre as cliente',
                'users.name as asesor',
                'solicitud.estado',
                'plan_pago.id as id_plan_pago',
                'solicitud.tipo_solicitud',
                'solicitud.desembolso',
                DB::raw('GROUP_CONCAT(codeudor.nombre SEPARATOR ",") as codeudores')
            )
            ->groupBy(
                'solicitud.id',
                'solicitud.importe_solicitud',
                'solicitud.moneda',
                'solicitud.lapso_capital',
                'solicitud.nro_cuotas',
                'solicitud.tasa',
                'solicitud.fecha_desembolso',
                'plan_pago.fecha_inicio',
                'plan_pago.fecha_fin',
                'solicitud.fecha_primera_cuota',
                'solicitud.destino_prestamo',
                'solicitud.tipo_garantia',
                'solicitud.tipo_desembolso',
                'solicitud.id_cliente',
                'solicitud.id_usuario',
                'cliente.nombre',
                'users.name',
                'solicitud.estado',
                'plan_pago.id',
                'solicitud.tipo_solicitud',
                'solicitud.desembolso'
            );

        // Filtro por criterio y búsqueda
        if ($request->filled('criterio') && $request->filled('buscar')) {
            $criterio = $request->criterio;
            $buscar = $request->buscar;
            $query->where($criterio, 'LIKE', '%' . $buscar . '%');
        }
        // Filtro por estado
        if ($request->filled('estado') && $request->estado!='todos') {
            $fechaActual = Carbon::now()->format('Y-m-d');
            // Filtros existentes (asesor y estado)
            if ($request->estado == 'vigentes') {
                $query->where('plan_pago.estado', 1)
                        ->whereDate('plan_pago.fecha_fin', '>=', $fechaActual);
            } elseif ($request->estado == 'vencidos') {
                $query->where('plan_pago.estado', 1)
                        ->whereDate('plan_pago.fecha_fin', '<', $fechaActual);
            }
        }

        // Filtro por asesor
        if ($request->filled('asesor') && $request->asesor!='0') {
            $query->where('solicitud.id_usuario', $request->asesor);
        }
        // Filtro por rango de fechas (fecha_desembolso)
        if ($request->filled('fecha_inicio')) {
            $query->whereDate('plan_pago.fecha_inicio', '>=', $request->fecha_inicio);
        }
        if ($request->filled('fecha_fin')) {
            $query->whereDate('plan_pago.fecha_inicio', '<=', $request->fecha_fin);
        }
        // Ordenar y paginar
        $planes_pago = $query->orderBy('plan_pago.id', 'desc')->get();

        try {
            // Obtener los datos de la empresa
            $mi_empresa = DB::table('mi_empresa')->first();
            
            // Preparar datos para la vista
            $data = [
                'title' => 'Reporte de Planes de Pago',
                'fecha' => Carbon::now()->format('d/m/Y'),
                'hora' => Carbon::now()->format('H:i:s'),
                'usuario' => Auth::user()->name ?? 'Sistema',
                'mi_empresa' => $mi_empresa,
                'planes_pago' => $planes_pago,
            ];

            // Generar el PDF
            return $this->generatePDF($data, 'reporte.planes_pago_general', 'reporte_planes_pago_general');
        } catch (\Exception $e) {
            \Log::error('Error al generar el PDF de planes de pago: ' . $e->getMessage());
            return response()->json(['error' => 'No se pudo generar el PDF.'], 500);
        }
    }

    public function getSolicitudesGeneral(Request $request){
        $query = DB::table('solicitud')
            ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
            ->join('users', 'users.id', '=', 'solicitud.id_usuario')
            ->leftJoin('solicitud_codeudor', 'solicitud_codeudor.id_solicitud', '=', 'solicitud.id')
            ->leftJoin('codeudor', 'codeudor.id', '=', 'solicitud_codeudor.id_codeudor')
            ->select(
                'solicitud.id as id_solicitud',
                'solicitud.importe_solicitud',
                'solicitud.moneda',
                'solicitud.lapso_capital',
                'solicitud.nro_cuotas',
                'solicitud.tasa',
                'solicitud.fecha_desembolso',
                'solicitud.fecha_primera_cuota',
                'solicitud.destino_prestamo',
                'solicitud.tipo_garantia',
                'solicitud.tipo_desembolso',
                'solicitud.id_cliente',
                'solicitud.id_usuario',
                'cliente.nombre as cliente',
                'users.name as asesor',
                'solicitud.estado',
                'solicitud.tipo_solicitud',
                'solicitud.desembolso',
                DB::raw('GROUP_CONCAT(codeudor.nombre SEPARATOR ",") as codeudores')
            )
            ->groupBy(
                'solicitud.id',
                'solicitud.importe_solicitud',
                'solicitud.moneda',
                'solicitud.lapso_capital',
                'solicitud.nro_cuotas',
                'solicitud.tasa',
                'solicitud.fecha_desembolso',
  
                'solicitud.fecha_primera_cuota',
                'solicitud.destino_prestamo',
                'solicitud.tipo_garantia',
                'solicitud.tipo_desembolso',
                'solicitud.id_cliente',
                'solicitud.id_usuario',
                'cliente.nombre',
                'users.name',
                'solicitud.estado',
                'solicitud.tipo_solicitud',
                'solicitud.desembolso'
            );

        // Filtro por criterio y búsqueda
        if ($request->filled('criterio') && $request->filled('buscar')) {
            $criterio = $request->criterio;
            $buscar = $request->buscar;

            $query->where($criterio, 'LIKE', '%' . $buscar . '%');
            
        }

        // Filtro por estado
        if ($request->estado !== 'todos') {
            $query->where('solicitud.estado', $request->estado);
        }

        // Filtro por rango de fechas (fecha_desembolso)
        if ($request->filled('fecha_inicio')) {
            $query->whereDate('solicitud.fecha', '>=', $request->fecha_inicio);
        }
        if ($request->filled('fecha_fin')) {
            $query->whereDate('solicitud.fecha', '<=', $request->fecha_fin);
        }

        // Ordenar y paginar
        $solicitudes = $query->orderBy('solicitud.id', 'desc')->paginate(20);

        return $solicitudes;
    }

    
    public function getSolicitudesGeneralPdf(Request $request)
    {
        $query = DB::table('solicitud')
            ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
            ->join('users', 'users.id', '=', 'solicitud.id_usuario')
            ->leftJoin('solicitud_codeudor', 'solicitud_codeudor.id_solicitud', '=', 'solicitud.id')
            ->leftJoin('codeudor', 'codeudor.id', '=', 'solicitud_codeudor.id_codeudor')
            ->select(
                'solicitud.id as id_solicitud',
                'solicitud.importe_solicitud',
                'solicitud.moneda',
                'solicitud.lapso_capital',
                'solicitud.nro_cuotas',
                'solicitud.tasa',
                'solicitud.fecha_desembolso',
                'solicitud.fecha_primera_cuota',
                'solicitud.destino_prestamo',
                'solicitud.tipo_garantia',
                'solicitud.tipo_desembolso',
                'solicitud.id_cliente',
                'solicitud.id_usuario',
                'cliente.nombre as cliente',
                'users.name as asesor',
                'solicitud.estado',
                'solicitud.tipo_solicitud',
                'solicitud.desembolso',
                DB::raw('GROUP_CONCAT(codeudor.nombre SEPARATOR ",") as codeudores')
            )
            ->groupBy(
                'solicitud.id',
                'solicitud.importe_solicitud',
                'solicitud.moneda',
                'solicitud.lapso_capital',
                'solicitud.nro_cuotas',
                'solicitud.tasa',
                'solicitud.fecha_desembolso',
  
                'solicitud.fecha_primera_cuota',
                'solicitud.destino_prestamo',
                'solicitud.tipo_garantia',
                'solicitud.tipo_desembolso',
                'solicitud.id_cliente',
                'solicitud.id_usuario',
                'cliente.nombre',
                'users.name',
                'solicitud.estado',
                'solicitud.tipo_solicitud',
                'solicitud.desembolso'
            );

        // Filtro por criterio y búsqueda
        if ($request->filled('criterio') && $request->filled('buscar')) {
            $criterio = $request->criterio;
            $buscar = $request->buscar;

            $query->where($criterio, 'LIKE', '%' . $buscar . '%');
            
        }

        // Filtro por estado
        if ($request->estado !== 'todos') {
            $query->where('solicitud.estado', $request->estado);
        }

        // Filtro por rango de fechas (fecha_desembolso)
        if ($request->filled('fecha_inicio')) {
            $query->whereDate('solicitud.fecha', '>=', $request->fecha_inicio);
        }
        if ($request->filled('fecha_fin')) {
            $query->whereDate('solicitud.fecha', '<=', $request->fecha_fin);
        }

        // Ordenar y paginar
        $solicitudes = $query->orderBy('solicitud.id', 'desc')->paginate(20);

        try {
            // Obtener los datos de la empresa
            $mi_empresa = DB::table('mi_empresa')->first();
            
            // Preparar datos para la vista
            $data = [
                'title' => 'Reporte General de Solicitudes',
                'fecha' => Carbon::now()->format('d/m/Y'),
                'hora' => Carbon::now()->format('H:i:s'),
                'usuario' => Auth::user()->name ?? 'Sistema',
                'mi_empresa' => $mi_empresa,
                'solicitudes' => $solicitudes,
            ];

            // Generar el PDF
            return $this->generatePDF($data, 'reporte.solicitudes_general', 'reporte_solicitudes_general');
        } catch (\Exception $e) {
            \Log::error('Error al generar el PDF de solicitudes: ' . $e->getMessage());
            return response()->json(['error' => 'No se pudo generar el PDF.'], 500);
        }
    }
    
    public function generarPlanReprogramadoPDF(Request $request)
    {
        try {
            
            // 1. Obtener los datos enviados desde Vue
            // Convertimos a objeto para usar -> en Blade, es más limpio
            $plan_pago = (object)$request->input('plan_pago');
            $importeLimpio = str_replace('.', '', $plan_pago->importe_solicitud); 
            $importeEntero = (int) $importeLimpio;
            $cuotas = $request->input('cuotas');
            
            // 2. Obtener datos para el Header (como en tu ejemplo)
            $empresa = DB::table('mi_empresa')->first();
            $usuario = auth()->user()->name ?? 'Sistema';

            // 3. Renderizar la vista Blade a HTML
            $html = View::make('plan_pago.plan_reprogramado_pdf', compact(
                'plan_pago', 
                'cuotas', 
                'empresa', 
                'usuario',
                'importeEntero'
            ))->render();

            // 4. Configurar mPDF
            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'letter',
                'margin_header' => 10,
                'margin_footer' => 10,
                'margin_top' => 15, // Espacio para el header
                'margin_bottom' => 15,
            ]);

            // 5. Escribir el HTML en el PDF
            $mpdf->WriteHTML($html);

            // 6. Devolver el PDF como un string
            // 'S' lo devuelve como string, 'D' lo descargaría directo
            $pdfContent = $mpdf->Output('', 'S');

            // 7. Enviar la respuesta al frontend
            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="plan.pdf"');

        } catch (\Exception $e) {
            // Manejar cualquier error
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
