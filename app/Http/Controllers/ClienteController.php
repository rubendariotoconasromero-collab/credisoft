<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use PDF;
use Dompdf\Dompdf;
use Dompdf\Options;
use Mpdf\Mpdf;
use App\Models\Cliente;


class ClienteController extends Controller
{
    //

    public function index(){
        return view('frmCliente');
    }

    

    public function save(Request $request)
    {
        try {
            DB::beginTransaction();

            // Validate request data
            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'fecha_nacimiento' => 'required|date',
                'ci' => 'required|string|max:20',
                'lugar_expedicion' => 'required|string|max:100',
                'sexo' => 'required|string|max:20',
                'estado_civil' => 'required|string|max:50',
                // 'actividad' => 'required|string|max:100',
                'vivienda' => 'required|string|max:100',
                'ingreso_mensual' => 'required|numeric',
                'direcciones' => 'required|array|min:1',
                'direcciones.*.tipo' => 'required|string',
                'direcciones.*.departamento' => 'required|string',
                'direcciones.*.descripcion' => 'required|string',
                'telefonos' => 'required|array|min:1',
                'telefonos.*.tipo' => 'required|string',
                'telefonos.*.numero' => 'required|string',
            ]);

            // Check for duplicates
            $existingClient = DB::table('cliente')
                ->where(function ($query) use ($request) {
                    $query->where('nombre', $request->nombre)
                        ->orWhere('ci', $request->ci);
                })
                ->first();

            if ($existingClient) {
                $message = $existingClient->nombre === $request->nombre 
                    ? 'Ya existe un cliente con este nombre'
                    : 'Ya existe un cliente con este CI';
                return response()->json([
                    'success' => false,
                    'error' => 'duplicate',
                    'message' => $message
                ], 422);
            }

            // Insert client
            $id_cliente = DB::table('cliente')->insertGetId([
                'nombre' => $request->nombre,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'ci' => $request->ci,
                'lugar_expedicion' => $request->lugar_expedicion,
                'sexo' => $request->sexo,
                'estado_civil' => $request->estado_civil,
                'actividad' => $request->actividad,
                'vivienda' => $request->vivienda,
                'ingreso_mensual' => $request->ingreso_mensual,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Insert addresses
            $direcciones = array_map(function ($direccion) use ($id_cliente) {
                return [
                    'tipo' => $direccion['tipo'],
                    'departamento' => $direccion['departamento'],
                    'ciudad' => $direccion['ciudad'] ?? null,
                    'zona' => $direccion['zona'] ?? null,
                    'descripcion' => $direccion['descripcion'],
                    'referencia' => $direccion['referencia'] ?? null,
                    'id_cliente' => $id_cliente,
                    'lng' => $direccion['lng'] ?? null,
                    'lat' => $direccion['lat'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }, $request->direcciones);
            DB::table('direccion')->insert($direcciones);

            // Insert phones
            $telefonos = array_map(function ($telefono) use ($id_cliente) {
                return [
                    'tipo' => $telefono['tipo'],
                    'numero' => $telefono['numero'],
                    'nombre' => $telefono['nombre'] ?? null,
                    'apellidos' => $telefono['apellidos'] ?? null,
                    'relacion' => $telefono['relacion'] ?? null,
                    'observacion' => $telefono['observacion'] ?? null,
                    'id_cliente' => $id_cliente,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }, $request->telefonos);
            DB::table('telefono')->insert($telefonos);

            DB::commit();

            return response()->json(['success' => true, 'id' => $id_cliente]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => 'validation',
                'message' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => 'server',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function modify(Request $request){
        DB::beginTransaction();
        try{
            DB::table('cliente')->where('cliente.id', $request->id)->update([
                'nombre'=>$request->nombre,
                'fecha_nacimiento'=>$request->fecha_nacimiento,
                'ci'=>$request->ci,
                'lugar_expedicion'=>$request->lugar_expedicion,
                'sexo'=>$request->sexo,
                'estado_civil'=>$request->estado_civil,
                'actividad'=>$request->actividad,
                'vivienda'=>$request->vivienda,
                'ingreso_mensual'=>$request->ingreso_mensual,
            ]);

            // foreach($request->permisos as $permiso){
            DB::table('direccion')
            // ->where('id_permiso', $permiso['id'])
            ->where('id_cliente', $request->id)
            ->delete();
            // }

            DB::table('telefono')
            // ->where('id_permiso', $permiso['id'])
            ->where('id_cliente', $request->id)
            ->delete();

            foreach($request->direcciones as $direccion){
                DB::table('direccion')->insert([
                   
                    'tipo' => $direccion['tipo'],
                    'departamento' => $direccion['departamento'],
                    'ciudad' => $direccion['ciudad'] ?? null,
                    'zona' => $direccion['zona'] ?? null,
                    'descripcion' => $direccion['descripcion'],
                    'referencia' => $direccion['referencia'] ?? null,
                    'id_cliente' => $request->id,
                    'lat' => $direccion['lat'],
                    'lng' => $direccion['lng'],
                    
                ]);
            }
    
            foreach($request->telefonos as $telefono){
                DB::table('telefono')->insert([
 
                    'tipo' => $telefono['tipo'],
                    'numero' => $telefono['numero'],
                    'nombre' => $telefono['nombre'] ?? null,
                    'apellidos' => $telefono['apellidos'] ?? null,
                    'relacion' => $telefono['relacion'] ?? null,
                    'observacion' => $telefono['observacion'] ?? null,
                    'id_cliente' => $request->id,
                   
                ]);
            }

            DB::commit();
        }catch(Exception $e){
            DB::rollback();
        }

    }

    public function getClientes(Request $request)
    {
        $query = DB::table('cliente')
            ->select(
                'cliente.id',
                'cliente.nombre',
                'cliente.fecha_nacimiento',
                'cliente.ci',
                'cliente.sexo',
                'cliente.estado_civil',
                'cliente.vivienda',
                'cliente.ingreso_mensual',
                'cliente.estado',
                'cliente.actividad',
                'cliente.imagen',
                'cliente.lugar_expedicion',
            )
            ->groupBy('cliente.id');

        if ($request->filled('buscar')) {
            $query->where($request->criterio, 'LIKE', '%' . $request->buscar . '%');
        }
        if ($request->opcion_asesor > 0) {
            $query->where('users.id', $request->opcion_asesor);
        }
        $clientes = $query->orderBy('cliente.id', 'desc')->get();
        return response()->json($clientes);
    }

    public function getClientesSin(){
        $clientes = DB::table('cliente')->leftJoin('direccion', 'direccion.id_cliente', '=', 'cliente.id')
        ->leftJoin('telefono', 'telefono.id_cliente', '=', 'cliente.id')
        ->select('cliente.lugar_expedicion','cliente.id','cliente.nombre', 'cliente.fecha_nacimiento', 'cliente.ci', 'cliente.sexo', 'cliente.estado_civil', 'cliente.vivienda', 'cliente.ingreso_mensual',
        'cliente.estado', 'cliente.actividad', 'cliente.imagen')
        ->groupBy('cliente.lugar_expedicion','cliente.id','cliente.nombre', 'cliente.fecha_nacimiento', 'cliente.ci', 'cliente.sexo', 'cliente.estado_civil', 'cliente.vivienda', 'cliente.ingreso_mensual',
        'cliente.estado', 'cliente.actividad')
        ->orderBy('cliente.id', 'desc')
        ->get();

        return $clientes;
    }

    public function activar(Request $request){
        DB::table('cliente')->where('cliente.id', $request->id_cliente)->update([
            'estado'=>1,
        ]);
    }
    public function desactivar(Request $request){
        DB::table('cliente')->where('cliente.id', $request->id_cliente)->update([
            'estado'=>0,
        ]);
    }

    public function getDireccionesTelefonos(Request $request){
        $telefonos=DB::table('telefono')->where('telefono.id_cliente', $request->id_cliente)->get();
        $direcciones=DB::table('direccion')->where('direccion.id_cliente', $request->id_cliente)->get();

        return ['direcciones'=>$direcciones, 'telefonos'=>$telefonos];

   

    }

    public function clientesPdf(Request $request){
        $clientes = DB::table('cliente')->join('direccion', 'direccion.id_cliente', '=', 'cliente.id')
        ->join('telefono', 'telefono.id_cliente', '=', 'cliente.id')
        ->select('cliente.lugar_expedicion','cliente.id','cliente.nombre', 'cliente.fecha_nacimiento', 'cliente.ci', 'cliente.sexo', 'cliente.estado_civil', 'cliente.vivienda', 'cliente.ingreso_mensual',
        'cliente.estado', 'cliente.actividad')
        ->groupBy('cliente.lugar_expedicion','cliente.id','cliente.nombre', 'cliente.fecha_nacimiento', 'cliente.ci', 'cliente.sexo', 'cliente.estado_civil', 'cliente.vivienda', 'cliente.ingreso_mensual',
        'cliente.estado', 'cliente.actividad')
        ->orderBy('cliente.id', 'desc')
        ->where('cliente.id', $request->id_cliente)
        ->get();

        $telefonos=DB::table('telefono')->where('telefono.id_cliente', $request->id_cliente)->get();
        $direcciones=DB::table('direccion')->where('direccion.id_cliente', $request->id_cliente)->get();

        $pdf = PDF::loadView('reporte.reporte_informacion_cliente', 
        compact('clientes', 'telefonos', 'direcciones'));
        return response($pdf->output())
        ->header('Content-Type', 'application/pdf')
        ->header('Content-Disposition', 'inline; filename="reporte_informacion_cliente.pdf"');
    }

   
    public function fotoCliente(Request $request)
    {
        DB::beginTransaction();
        try {
       

            // Obtener el cliente actual para verificar si tiene imagen previa
            $cliente = DB::table('cliente')
                ->select('imagen')
                ->where('id', $request->id_cliente)
                ->first();

            // Eliminar la imagen anterior si existe
            if ($cliente->imagen && $cliente->imagen !== 'default.png') {
                $rutaImagenAnterior = public_path('img/cliente/' . $cliente->imagen);
                if (file_exists($rutaImagenAnterior)) {
                    unlink($rutaImagenAnterior);
                }
            }

            // Procesar la nueva imagen
            $imagen = $request->file('imagen');
            $extension = $imagen->getClientOriginalExtension();
            $nombreArchivo = 'cliente_' . $request->id_cliente . '_' . time() . '.' . $extension;

            // Mover la imagen al directorio público
            $imagen->move(public_path('img/cliente'), $nombreArchivo);

            // Actualizar la base de datos
            DB::table('cliente')
                ->where('id', $request->id_cliente)
                ->update(['imagen' => $nombreArchivo]);

            DB::commit();
            
            return response()->json([
                'success' => true,
                'imagen' => $nombreArchivo,
                'message' => 'Foto actualizada correctamente'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'error' => 'Error de validación',
                'messages' => $e->validator->getMessageBag()
            ], 422);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'error' => 'Error al actualizar la foto',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function getCreditosCliente(Request $request){
        $cliente_creditos = DB::table('cliente')
        ->join('solicitud', 'cliente.id', '=', 'solicitud.id_cliente')
        ->join('plan_pago', 'plan_pago.id_solicitud', '=', 'solicitud.id')
        ->join('users', 'users.id', '=', 'solicitud.id_usuario')
        ->select('plan_pago.*', 'solicitud.*', 'plan_pago.id as id_plan_pago', 'users.personal as nombre_usuario', 
        'users.id', 'plan_pago.estado as estado_plan')
        ->where('cliente.id', $request->id_cliente)
        ->get();

        return $cliente_creditos;
    }

    public function getCuotasPlanes(Request $request){
        $cuotas = DB::table('cuota')
        ->join('plan_pago', 'cuota.id_plan_pago', '=', 'plan_pago.id')
        ->join('solicitud', 'solicitud.id', '=', 'plan_pago.id_solicitud')
        ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
     
        ->select('cuota.*', DB::raw('DATEDIFF(NOW(), cuota.fecha) as dias_pasados'))
        ->where('cliente.id', $request->id_cliente)
        ->get();

       
        $nuevoArrayConsulta = [];

        foreach ($cuotas as $cuota) {
            $existe = DB::table('pago_amortizacion')
                ->join('cuota', 'cuota.id', '=', 'pago_amortizacion.id_cuota')
                ->where('pago_amortizacion.id_cuota', $cuota->id)
                ->exists();

            // Agrega la cuota original al nuevo array
            $nuevoArrayConsulta[] = $cuota;

            if ($existe) {
                // Realiza la segunda consulta
                $consultaAdicional = DB::table('pago_amortizacion')
                    ->select('pago_amortizacion.*', 'saldo_pendiente as saldo_capital', 'monto_pago as total', 'capital_pagado as capital', 'interes_pagado as interes', 'multa_pagada as ahorro')  // Ajusta los campos según tu estructura
                    ->where('id_cuota', $cuota->id)
                    ->get();
                // Agrega los resultados adicionales al nuevo array
                $nuevoArrayConsulta = array_merge($nuevoArrayConsulta, $consultaAdicional->toArray());
            }
        }
        return $nuevoArrayConsulta;
    }

    public function getAsesores(Request $request){
        return DB::table('users')
        ->select('users.id as id', 'users.personal')
        ->join('rol', 'rol.id', '=', 'users.id_rol')
        // ->where('rol.nombre', 'asesor')
        ->get();
    }

    public function getCantidadClientes(Request $request){
        $clientes = DB::table('cliente')
     
        ->join('solicitud', 'solicitud.id_cliente', '=', 'cliente.id')
        ->join('plan_pago', 'plan_pago.id_solicitud', '=', 'solicitud.id')
        ->join('users', 'solicitud.id_usuario', '=', 'users.id')
        ->select('users.personal', DB::raw('COUNT(DISTINCT cliente.id) as cant_clientes'))
        ->groupBy('users.personal')
        ->orderBy('cant_clientes', 'desc')
        ->get();

        return $clientes;
    }

    public function cantidadesClientes(Request $request){


        $clientes_sin = DB::table('cliente')
        ->leftJoin('solicitud', 'solicitud.id_cliente', '=', 'cliente.id')
        ->leftJoin('plan_pago', 'plan_pago.id_solicitud', '=', 'solicitud.id')
        ->leftJoin('users', 'solicitud.id_usuario', '=', 'users.id')
        ->select(DB::raw('COUNT(DISTINCT cliente.id) as cant_clientes'))
        ->orderBy('cant_clientes', 'desc')
        ->get();



        $clientes_creditos = DB::table('cliente')
   
        ->join('solicitud', 'solicitud.id_cliente', '=', 'cliente.id')
        ->join('plan_pago', 'plan_pago.id_solicitud', '=', 'solicitud.id')
        ->join('users', 'solicitud.id_usuario', '=', 'users.id')
        ->select(DB::raw('count(DISTINCT cliente.id) as cant_clientes'))
        ->orderBy('cant_clientes', 'desc')
        ->get();

        return [
            'clientes_con_creditos'=>($clientes_creditos->count()>0)?$clientes_creditos[0]->cant_clientes:0,
            'clientes_sin_creditos'=>($clientes_sin->count()>0)?$clientes_sin[0]->cant_clientes:0,
        ];
    }

    protected function generatePdf($view, $data, $filename, $options = [])
    {
        try {
            $config = array_merge([
                'mode' => 'utf-8',
                'format' => 'letter',
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 10,
                'margin_bottom' => 10,
                'margin_footer' => 10,
            ], $options);

            $mpdf = new Mpdf($config);
            $mpdf->SetFooter('Generado por: ' . auth()->user()->name . ' | | Página {PAGENO}');
            $html = view($view, $data)->render();
            $mpdf->WriteHTML($html);
            return response($mpdf->Output($filename, 'I'))
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', "inline; filename=\"$filename\"");
        } catch (\Exception $e) {
            \Log::error('PDF Generation Error: ' . $e->getMessage());
            return response()->json(['error' => 'No se pudo generar el PDF'], 500);
        }
    }

    public function clientesPdf2(Request $request)
    {
        // Validate request
        $request->validate([
            'id_cliente' => 'required|integer|exists:cliente,id',
        ]);

        // Fetch customer data with optimized query
        $clientes = DB::table('cliente')
            ->leftJoin('direccion', 'direccion.id_cliente', '=', 'cliente.id')
            ->leftJoin('telefono', 'telefono.id_cliente', '=', 'cliente.id')
            ->select(
                'cliente.id',
                'cliente.nombre',
                'cliente.fecha_nacimiento',
                'cliente.ci',
                'cliente.lugar_expedicion',
                'cliente.sexo',
                'cliente.estado_civil',
                'cliente.vivienda',
                'cliente.ingreso_mensual',
                'cliente.estado',
                'cliente.actividad',
                'cliente.imagen'
            )
            ->where('cliente.id', $request->id_cliente)
            ->first();

        // Fetch related data
        $telefonos = DB::table('telefono')
            ->where('telefono.id_cliente', $request->id_cliente)
            ->get();
        $direcciones = DB::table('direccion')
            ->where('direccion.id_cliente', $request->id_cliente)
            ->get();

        // Fetch company data
        $empresa = DB::table('mi_empresa')->first();

        // Check if customer exists
        if (!$clientes) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        // Prepare data for the view
        $data = [
            'cliente' => $clientes,
            'telefonos' => $telefonos,
            'direcciones' => $direcciones,
            'empresa' => $empresa,
            'title'=>'Información Personal del Cliente'
        ];

        // Generate PDF using reusable method
        return $this->generatePdf(
            'reporte.reporte_informacion_cliente',
            $data,
            'reporte_informacion_cliente.pdf'
        );
    }


    public function getClientesPaginate(Request $request)
    {
        $query = DB::table('cliente')
            
            ->select(
                'cliente.id',
                'cliente.nombre',
                'cliente.fecha_nacimiento',
                'cliente.ci',
                'cliente.sexo',
                'cliente.estado_civil',
                'cliente.vivienda',
                'cliente.ingreso_mensual',
                'cliente.estado',
                'cliente.actividad',
                'cliente.imagen',
                'cliente.lugar_expedicion',
            )

            ->groupBy('cliente.id'); // Group by primary key to avoid duplicate rows

        // Apply search filter
        if ($request->filled('buscar')) {
            $query->where($request->criterio, 'LIKE', '%' . $request->buscar . '%');
        }

        // Apply advisor filter
        if ($request->opcion_asesor > 0) {
            $query->where('users.id', $request->opcion_asesor);
        }
        // Order and paginate
        //$perPage = 10; // Adjust as needed
        $clientes = $query->orderBy('cliente.id', 'desc')->paginate(20);

        return response()->json($clientes);
    }

    public function getFichaCompleta($id)
    {
        $cliente = Cliente::with(['telefonos', 'direcciones'])->find($id);

        if (!$cliente) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }

        return response()->json($cliente);
    }

    public function imprimirReporteClientes(Request $request)
    {
        $query = Cliente::with(['telefonos', 'direcciones'])
            ->select('cliente.*'); 
        if ($request->filled('buscar')) {
            $query->where($request->criterio, 'LIKE', '%' . $request->buscar . '%');
        }
        $clientes = $query->orderBy('id', 'desc')->get();
        $mi_empresa = DB::table('mi_empresa')->first();
        
        $logoName = empty($mi_empresa->logo) ? 'logo_sistema_codesoft.png' : $mi_empresa->logo;
        $path = public_path('img/' . $logoName);
        $logo_base64 = '';
        
        if (file_exists($path)) {
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $dataImg = file_get_contents($path);
            $logo_base64 = 'data:image/' . $type . ';base64,' . base64_encode($dataImg);
        }

        $data = [
            'clientes'    => $clientes,
            'mi_empresa'  => $mi_empresa,
            'logo_base64' => $logo_base64,
            'title'       => 'Reporte de Clientes',
            'fecha'       => date('d/m/Y'),
            'hora'        => date('H:i:s'),
            'usuario'     => auth()->user()->name
        ];

        return $this->generatePdf('reporte.cliente.reporte_clientes', $data, 'reporte_clientes.pdf');
    }

    public function getClienteInfo(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:cliente,id'
        ]);
        $cliente = Cliente::find($request->id);
        return response()->json($cliente);
    }
}
