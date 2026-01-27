<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use PDF;
use Dompdf\Dompdf;
use Dompdf\Options;
use Mpdf\Mpdf;
use App\Models\Codeudor;

class CodeudorController extends Controller
{
    public function index(){
        return view('frmCodeudor');
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
                'actividad' => 'required|string|max:100',
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
            $existingCodeudor = DB::table('codeudor')
                ->where(function ($query) use ($request) {
                    $query->where('nombre', $request->nombre)
                        ->orWhere('ci', $request->ci);
                })
                ->first();

            if ($existingCodeudor) {
                $message = $existingCodeudor->nombre === $request->nombre 
                    ? 'Ya existe un codeudor con este nombre'
                    : 'Ya existe un codeudor con este CI';
                return response()->json([
                    'success' => false,
                    'error' => 'duplicate',
                    'message' => $message
                ], 422);
            }

            // Insert codeudor
            $id_codeudor = DB::table('codeudor')->insertGetId([
                'nombre' => $request->nombre,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'ci' => $request->ci,
                'lugar_expedicion' => $request->lugar_expedicion,
                'sexo' => $request->sexo,
                'estado_civil' => $request->estado_civil,
                'actividad' => $request->actividad,
                'vivienda' => $request->vivienda,
                'ingreso_mensual' => $request->ingreso_mensual,
                'tipo' => $request->tipo,
                // 'created_at' => now(),
                // 'updated_at' => now()
            ]);

            // Insert addresses
            $direcciones = array_map(function ($direccion) use ($id_codeudor) {
                return [
                    'tipo' => $direccion['tipo'],
                    'departamento' => $direccion['departamento'],
                    'ciudad' => $direccion['ciudad'] ?? null,
                    'zona' => $direccion['zona'] ?? null,
                    'descripcion' => $direccion['descripcion'],
                    'referencia' => $direccion['referencia'] ?? null,
                    'id_codeudor' => $id_codeudor,
                    'lng' => $direccion['lng'] ?? null,
                    'lat' => $direccion['lat'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }, $request->direcciones);
            DB::table('direccion')->insert($direcciones);

            // Insert phones
            $telefonos = array_map(function ($telefono) use ($id_codeudor) {
                return [
                    'tipo' => $telefono['tipo'],
                    'numero' => $telefono['numero'],
                    'nombre' => $telefono['nombre'] ?? null,
                    'apellidos' => $telefono['apellidos'] ?? null,
                    'relacion' => $telefono['relacion'] ?? null,
                    'observacion' => $telefono['observacion'] ?? null,
                    'id_codeudor' => $id_codeudor,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }, $request->telefonos);
            DB::table('telefono')->insert($telefonos);

            DB::commit();

            return response()->json(['success' => true, 'id' => $id_codeudor]);
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
        $id_codeudor=$request->id;
        DB::beginTransaction();
        try{
            DB::table('codeudor')->where('codeudor.id', $id_codeudor)->update([
                'nombre'=>$request->nombre,
                'fecha_nacimiento'=>$request->fecha_nacimiento,
                'ci'=>$request->ci,
                'lugar_expedicion'=>$request->lugar_expedicion,
                'sexo'=>$request->sexo,
                'estado_civil'=>$request->estado_civil,
                'actividad'=>$request->actividad,
                'vivienda'=>$request->vivienda,
                'ingreso_mensual'=>$request->ingreso_mensual,
                'tipo'=>$request->tipo,
            ]);

            // foreach($request->permisos as $permiso){
            DB::table('direccion')
            // ->where('id_permiso', $permiso['id'])
            ->where('id_codeudor', $id_codeudor)
            ->delete();
            // }

            DB::table('telefono')
            // ->where('id_permiso', $permiso['id'])
            ->where('id_codeudor', $id_codeudor)
            ->delete();

            foreach($request->direcciones as $direccion){
                DB::table('direccion')->insert([
                    'tipo'=>$direccion['tipo'],
                    'departamento'=>$direccion['departamento'],
                    'ciudad'=>$direccion['ciudad'],
                    'zona'=>$direccion['zona'],
                    'descripcion'=>$direccion['descripcion'],
                    'referencia'=>empty($direccion['referencia'])?'':$direccion['referencia'],
                    'id_codeudor'=>$id_codeudor,
                    
                    'lat' => $direccion['lat'],
                    'lng' => $direccion['lng'],

                ]);
            }
    
            foreach($request->telefonos as $telefono){
                DB::table('telefono')->insert([
                    'tipo'=>$telefono['tipo'],
                    'numero'=>$telefono['numero'],
                    'nombre'=>empty($telefono['nombre'])?'':$telefono['nombre'],
                    'apellidos'=>empty($telefono['apellidos'])?'':$telefono['apellidos'],
                    'relacion'=>empty($telefono['relacion'])?'':$telefono['relacion'],
                    'observacion'=>$telefono['observacion'],
                    'id_codeudor'=>$id_codeudor,
                ]);
            }

            DB::commit();
        }catch(Exception $e){
            DB::rollback();
        }

    }

    
    public function getCodeudores(Request $request)
    {
        // Construir la consulta base
        $query = DB::table('codeudor')
            ->leftJoin('direccion', 'direccion.id_codeudor', '=', 'codeudor.id')
            ->leftJoin('telefono', 'telefono.id_codeudor', '=', 'codeudor.id')
            ->select(
                'codeudor.id',
                'codeudor.nombre',
                'codeudor.ci',
                'codeudor.fecha_nacimiento',
                'codeudor.sexo',
                'codeudor.estado_civil',
                'codeudor.lugar_expedicion',
                'codeudor.vivienda',
                'codeudor.ingreso_mensual',
                'codeudor.tipo',
                'codeudor.estado',
                'codeudor.actividad',
                'codeudor.imagen'
            
            )
            ->where('codeudor.nombre', '!=', 'SIN GARANTE')
            ->groupBy(
                'codeudor.id',
                'codeudor.nombre',
                'codeudor.ci',
                'codeudor.fecha_nacimiento',
                'codeudor.sexo',
                'codeudor.estado_civil',
                'codeudor.lugar_expedicion',
                'codeudor.vivienda',
                'codeudor.ingreso_mensual',
                'codeudor.tipo',
                'codeudor.estado',
                'codeudor.actividad',
                'codeudor.imagen'
            )
            ->orderBy('codeudor.id', 'desc');

        // Aplicar filtro de búsqueda si existe
        if ($request->filled('buscar') && $request->filled('criterio')) {
            $searchTerm = '%' . $request->buscar . '%';
            

            $query->where($request->criterio, 'LIKE', $searchTerm);
        }

        // Opción para devolver paginado o todos los resultados
        return $query->get();
    }

    public function getCodeudoresSin(){
        $codeudores = DB::table('codeudor')->leftJoin('direccion', 'direccion.id_codeudor', '=', 'codeudor.id')
        ->leftJoin('telefono', 'telefono.id_codeudor', '=', 'codeudor.id')
        ->select('codeudor.lugar_expedicion','codeudor.id','codeudor.nombre', 'codeudor.fecha_nacimiento', 'codeudor.ci', 'codeudor.sexo', 'codeudor.estado_civil', 'codeudor.vivienda', 'codeudor.ingreso_mensual', 'codeudor.tipo',
        'codeudor.estado', 'codeudor.actividad')
        ->groupBy('codeudor.lugar_expedicion','codeudor.id','codeudor.nombre', 'codeudor.fecha_nacimiento', 'codeudor.ci', 'codeudor.sexo', 'codeudor.estado_civil', 'codeudor.vivienda', 'codeudor.ingreso_mensual', 'codeudor.tipo',
        'codeudor.estado', 'codeudor.actividad')
        // ->where()
        //->orderBy('codeudor.id', 'desc')
        ->orderByRaw('codeudor.ci = 0 DESC') // Ordena primero los registros donde CI=0
        ->get();

        return $codeudores;
    }

    public function activar(Request $request){
        DB::table('codeudor')->where('codeudor.id', $request->id_codeudor)->update([
            'estado'=>1,
        ]);
    }
    public function desactivar(Request $request){
        DB::table('codeudor')->where('codeudor.id', $request->id_codeudor)->update([
            'estado'=>0,
        ]);
    }

    public function getDireccionesTelefonos(Request $request){
        $telefonos=DB::table('telefono')->where('telefono.id_codeudor', $request->id_codeudor)->get();
        $direcciones=DB::table('direccion')->where('direccion.id_codeudor', $request->id_codeudor)->get();

        return ['direcciones'=>$direcciones, 'telefonos'=>$telefonos];

    }

    public function clientesPdf(Request $request){
        $clientes = DB::table('cliente')->join('direccion', 'direccion.id_cliente', '=', 'cliente.id')
        ->join('telefono', 'telefono.id_cliente', '=', 'cliente.id')
        ->select('cliente.lugar_expedicion','cliente.id','cliente.nombre', 'cliente.fecha_nacimiento', 'cliente.ci', 'cliente.sexo', 'cliente.estado_civil', 'cliente.vivienda', 'cliente.ingreso_mensual', 'codeudor.tipo',
        'cliente.estado', 'cliente.actividad')
        ->groupBy('cliente.lugar_expedicion','cliente.id','cliente.nombre', 'cliente.fecha_nacimiento', 'cliente.ci', 'cliente.sexo', 'cliente.estado_civil', 'cliente.vivienda', 'cliente.ingreso_mensual', 'codeudor.tipo',
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

    public function codeudoresPdf2(Request $request){
        //dd($request->id_codeudor);
        $codeudores = DB::table('codeudor')->join('direccion', 'direccion.id_codeudor', '=', 'codeudor.id')
        ->join('telefono', 'telefono.id_codeudor', '=', 'codeudor.id')
        ->select('codeudor.lugar_expedicion','codeudor.id','codeudor.nombre', 'codeudor.fecha_nacimiento', 'codeudor.ci', 'codeudor.sexo', 'codeudor.estado_civil', 'codeudor.vivienda', 'codeudor.ingreso_mensual', 'codeudor.tipo',
        'codeudor.estado', 'codeudor.actividad', 'codeudor.imagen')
        ->groupBy('codeudor.lugar_expedicion','codeudor.id','codeudor.nombre', 'codeudor.fecha_nacimiento', 'codeudor.ci', 'codeudor.sexo', 'codeudor.estado_civil', 'codeudor.vivienda', 'codeudor.ingreso_mensual', 'codeudor.tipo',
        'codeudor.estado', 'codeudor.actividad', 'codeudor.imagen')
        ->orderBy('codeudor.id', 'desc')
        ->where('codeudor.id', $request->id_codeudor)
        ->first();

        //dd($codeudores);

        $telefonos=DB::table('telefono')->where('telefono.id_codeudor', $request->id_codeudor)->get();
        $direcciones=DB::table('direccion')->where('direccion.id_codeudor', $request->id_codeudor)->get();
        $empresa = DB::table('mi_empresa')->first();

        // Check if customer exists
        if (!$codeudores) {
            return response()->json(['error' => 'Codeudor no encontrado'], 404);
        }


        $data = [
            'codeudor' => $codeudores, // Pasa los datos del codeudor a la vista
            'telefonos' => $telefonos, // Pasa los datos de teléfonos a la vista
            'direcciones' => $direcciones, // Pasa los datos de direcciones a la vista
            'empresa' => $empresa,
            'title'=>'Información Personal del Codeudor/Garante'
        ];
        
        // Generate PDF using reusable method
        return $this->generatePdf(
            'reporte.reporte_informacion_codeudor',
            $data,
            'reporte_informacion_codeudor.pdf'
        );

        // Muestra el contenido del PDF
        //echo $output;
    }

    public function fotoCodeudor(Request $request){
        DB::beginTransaction();
        try {
            $codeudor = DB::table('codeudor')
                ->select('imagen')
                ->where('id', $request->id_codeudor)
                ->first();

            // Eliminar la imagen anterior si existe
            if ($codeudor->imagen && $codeudor->imagen !== 'default.png') {
                $rutaImagenAnterior = public_path('img/codeudor/' . $codeudor->imagen);
                if (file_exists($rutaImagenAnterior)) {
                    unlink($rutaImagenAnterior);
                }
            }

            // Procesar la nueva imagen
            $imagen = $request->file('imagen');
            $extension = $imagen->getClientOriginalExtension();
            $nombreArchivo = 'cliente_' . $request->id_codeudor . '_' . time() . '.' . $extension;

            // Mover la imagen al directorio público
            $imagen->move(public_path('img/codeudor'), $nombreArchivo);

            // Actualizar la base de datos
            DB::table('codeudor')
                ->where('id', $request->id_codeudor)
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

    public function getCodeudorSolicitud(Request $request){
        return DB::table('codeudor')
        ->join('solicitud_codeudor', 'solicitud_codeudor.id_codeudor', '=', 'codeudor.id')
        ->join('solicitud', 'solicitud_codeudor.id_solicitud', '=', 'solicitud.id')
        ->select('codeudor.*', 'codeudor.id as id_codeudor')
        ->where('solicitud.id', $request->id_solicitud)
        ->get();

    }

    public function getCodeudoresSolicitudes(){
        return DB::table('codeudor')
        ->leftJoin('solicitud_codeudor', 'solicitud_codeudor.id_codeudor', '=', 'codeudor.id')
        ->leftJoin('solicitud', 'solicitud_codeudor.id_solicitud', '=', 'solicitud.id')
        ->select('codeudor.*', 'codeudor.id as id_codeudor', 'solicitud.id as id_solicitud')
        ->get();
    }


    public function getActividades(){
        return DB::table('actividades')->get();
    }

    protected function generatePdf($view, $data, $filename, $options = [])
    {
        try {
            // Default mPDF configuration
            $config = array_merge([
                'mode' => 'utf-8',
                'format' => 'A4',
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 10,
                'margin_bottom' => 10,
                // 'margin_header' => 10,
                'margin_footer' => 10,
                // 'default_font' => 'dejavusans',
            ], $options);

            // Initialize mPDF
            $mpdf = new Mpdf($config);

            // Set header and footer
            // $mpdf->SetHeader('{DATE j-m-Y} | Informe de Cliente | {PAGENO}/{nbpg}');
            $mpdf->SetFooter('Generado por: ' . auth()->user()->name . ' | | Página {PAGENO}');

            // Render the view
            $html = view($view, $data)->render();
            $mpdf->WriteHTML($html);

            // Output the PDF
            return response($mpdf->Output($filename, 'I'))
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', "inline; filename=\"$filename\"");
        } catch (\Exception $e) {
            \Log::error('PDF Generation Error: ' . $e->getMessage());
            return response()->json(['error' => 'No se pudo generar el PDF'], 500);
        }
    }

    public function imprimirReporteCodeudores(Request $request)
    {
        $query = Codeudor::with(['telefonos', 'direcciones']);
        if ($request->filled('buscar')) {
            $query->where($request->criterio, 'LIKE', '%' . $request->buscar . '%');
        }
        $codeudores = $query->orderBy('id', 'desc')->get();
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
            'codeudores'  => $codeudores,
            'mi_empresa'  => $mi_empresa,
            'logo_base64' => $logo_base64,
            'title'       => 'Reporte de Codeudores',
            'fecha'       => date('d/m/Y'),
            'hora'        => date('H:i:s'),
            'usuario'     => auth()->user()->name
        ];

        return $this->generatePdf('reporte.codeudor.reporte_codeudores', $data, 'reporte_codeudores.pdf');
    }
   
}
