<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;
use Dompdf\Dompdf;
use Dompdf\Options;
use Mpdf\Mpdf;
use Carbon\Carbon;
use App\Models\PlanPago; // Asumiendo que tienes el modelo
use App\Models\Cuota;     // Asumiendo que tienes el modelo
use App\Models\Solicitud;
use App\Models\Cliente;
use App\Models\Garantia;
use App\Models\SolicitudCodeudor;
use App\Models\Respaldo;
use Illuminate\Support\Facades\Storage;
use App\Models\OrdenPagoReprogramacion;




class SolicitudController extends Controller
{
    //
    public function index(){
        return view('frmSolicitud');
    }

    public function save(Request $request){
        DB::beginTransaction();
        try{
            $id_solicitud=DB::table('solicitud')->insertGetId([
                'importe_solicitud'=>$request->importe_solicitud,
                'moneda'=>$request->moneda,
                'lapso_capital'=>$request->lapso_capital,
                'nro_cuotas'=>$request->nro_cuotas,
                'tasa'=>$request->tasa,
                'fecha_desembolso'=>$request->fecha_desembolso,
                'fecha'=>now(),
                'fecha_primera_cuota'=>$request->fecha_primera_cuota,
                'destino_prestamo'=>$request->destino_prestamo,
                // 'monto_pago_adm'=>$request->monto_pago_adm,
                'tipo_garantia'=>$request->tipo_garantia,
                'tipo_desembolso'=>$request->tipo_desembolso,
                'id_cliente'=>$request->id_cliente,
                'tipo_tasa'=>$request->tipo_tasa,
                'id_usuario'=>Auth::id(),
                
            ]);



            if (in_array($request->tipo_garantia, [
                'Prendario o Quirografaria',
                'Empeño Joyas (oro)',
                'Custodia Inmueble o Lote terreno',
                'Custodia de Vehículo Automovil',
                'Custodia de Papeles de Moto',
                'Empeño de electrodoméstico u Otros',
            ])) {
                foreach ($request->garantias as $garantia) {
                    DB::table('garantia')->insert([
                        'descripcion' => $garantia['descripcion'] ?? null,
                        'id_solicitud' => $id_solicitud,
                    ]);
                }
            }


    
            foreach($request->lista_codeudores as $codeudor){
                DB::table('solicitud_codeudor')->insert([
                    'id_solicitud'=>$id_solicitud,
                    'id_codeudor'=>$codeudor['id_codeudor'],
                ]);
            }
     

            
            DB::commit();
        }catch(Exception $e){
            DB::rollback();
        }

    }

    public function modify(Request $request){
        DB::beginTransaction();
        try{
            $id_solicitud=DB::table('solicitud')->where('id', $request->id_solicitud)->update([
                'importe_solicitud'=>$request->importe_solicitud,
                'moneda'=>$request->moneda,
                'lapso_capital'=>$request->lapso_capital,
                'nro_cuotas'=>$request->nro_cuotas,
                'tasa'=>$request->tasa,
                'fecha_desembolso'=>$request->fecha_desembolso,
                'fecha_primera_cuota'=>$request->fecha_primera_cuota,
                'destino_prestamo'=>$request->destino_prestamo,
                'monto_pago_adm'=>$request->monto_pago_adm,
                'tipo_garantia'=>$request->tipo_garantia,
                'tipo_desembolso'=>$request->tipo_desembolso,
                'id_cliente'=>$request->id_cliente,
                'tipo_tasa'=>$request->tipo_tasa,
                //'id_usuario'=>Auth::id(),
                
            ]);

            
            if(in_array($request->tipo_garantia, [
                'Prendario o Quirografaria',
                'Empeño Joyas (oro)',
                'Custodia Inmueble o Lote terreno',
                'Custodia de Vehículo Automovil',
                'Custodia de Papeles de Moto',
                'Empeño de electrodoméstico u Otros',
            ])){
                if($request->garantias[0]['id_garantia']!=0){
           
                    // agregando
                    foreach($request->garantias as $garantia){
                        if(!DB::table('garantia')
                        ->join('solicitud','solicitud.id', '=', 'garantia.id_solicitud')
                        ->where('garantia.id', '=',$garantia['id_garantia'])
                        ->where('solicitud.id','=', $request->id_solicitud)
                        ->exists())
                        {
                            DB::table('garantia')
                            ->insert([
                                'descripcion'=>$garantia['descripcion'],
                                'id_solicitud'=>$request->id_solicitud,
                            ]);
                        }
                    }  
                }else{
                    foreach($request->garantias as $garantia){
                    
                        DB::table('garantia')
                        ->insert([
                            'descripcion'=>$garantia['descripcion'],
                            'id_solicitud'=>$request->id_solicitud,
                        ]);
                        
                    }
                }
            }

            // para los codeudores
            // registrando codeudores en tabla tercera
            DB::table('solicitud_codeudor')
            ->where('id_solicitud', $request->id_solicitud )
            ->delete();

            foreach($request->lista_codeudores as $codeudor){

                DB::table('solicitud_codeudor')->insert([
                    'id_solicitud'=>$request->id_solicitud,
                    'id_codeudor'=>$codeudor['id_codeudor'],
                ]);
            
            }

            DB::commit();
        }catch(Exception $e){
            DB::rollback();
        }

    }

    public function getSolicitudes(Request $request)
    {
        $query = DB::table('solicitud')
            ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
            ->join('users', 'users.id', '=', 'solicitud.id_usuario')
            ->select(
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
                'cliente.nombre as cliente',
                'cliente.ci',
                'cliente.lugar_expedicion',
                'cliente.actividad',
                'users.name as asesor',
                'solicitud.estado',
                'users.personal',
                'solicitud.fecha',
                'solicitud.monto_pago_adm',
                'solicitud.tipo_tasa',
                'solicitud.observacion',
                'solicitud.tipo_solicitud',
                'solicitud.desembolso',
                'cliente.imagen',
                'solicitud.id_solicitud_origen',
                'solicitud.monto_refinanciamiento',
            )
            ->where($request->criterio, 'LIKE', '%' . $request->buscar . '%')
            
            ->whereDate('solicitud.fecha', '>=', $request->fecha_inicial)
            ->whereDate('solicitud.fecha', '<=', $request->fecha_final);

        // Aplicar filtro de estado solo si no es 'todos'
        if ($request->estado !== 'todos') {
            $query->where('solicitud.estado', $request->estado);
        }

        if (Auth::user()->id_rol != 1) {
            $query->where('users.id', Auth::id()); // Optimización: Auth::id() es más conciso
        }

        $solicitudes = $query->orderBy('solicitud.id', 'desc')
                            ->paginate(100);

        return $solicitudes;
    }
    public function activarSolicitud(Request $request){
        DB::table('solicitud')->where('solicitud.id', $request->id_solicitud)
        ->update([
            'estado'=>1
        ]);
    }

    public function desactivarSolicitud(Request $request){
        DB::table('solicitud')->where('solicitud.id', $request->id_solicitud)
        ->update([
            'estado'=>0
        ]);
    }

    public function getGarantias(Request $request){
        $garantias = DB::table('garantia')
        ->join('solicitud','garantia.id_solicitud', '=', 'solicitud.id')
        ->select('garantia.*')
        ->where('solicitud.id', $request->id_solicitud)
        ->get();

        return ['garantias'=>$garantias];
    }

    public function guardarImagenes(Request $request){
        //$imagenes = json_decode($request->imagenes, true);
        //dd($request->imagenes);
        DB::beginTransaction();
        try{
            foreach ($request->imagenes as $arreglo) {
                foreach ($arreglo as $item) {
                    // Accede a los elementos individuales del arreglo $item
                    $id_imagen = $item['id_imagen'];
                    $id_garantia = $item['id_garantia'];
                    $imagen = $item['imagen'];
                    $imagen_file = $item['imagen_file'];
                    
                    //dd($imagen_file);
    
                    DB::table('imagen')
                    ->join('garantia', 'garantia.id', '=', 'imagen.id_garantia')
                    ->select('imagen.*')
                    ->where('imagen.id_garantia', '=', $id_garantia)
                    ->where('imagen.id','!=', $id_imagen) // Agregamos esta condición
                    ->delete();
                }
    
                foreach ($arreglo as $item) {
                    // Accede a los elementos individuales del arreglo $item
                    $id_imagen = $item['id_imagen'];
                    $id_garantia = $item['id_garantia'];
                    $imagen = $item['imagen'];
                    $imagen_file = $item['imagen_file'];
                    
                    $imagen_existe=DB::table('imagen')
                    ->join('garantia', 'garantia.id', '=', 'imagen.id_garantia')
                    ->select('imagen.*')
                    ->where('imagen.id_garantia', '=', $id_garantia)
                    ->where('imagen.id','!=', $id_imagen) // Agregamos esta condición
                    ->get();
    
    
                    if(!empty($imagen_existe)){
                        $nombreArchivo='';
                        if(!empty($imagen_file)){
                            // Obtiene el archivo de imagen del formulario
                            $imagen = $imagen_file;
                            // Genera un nombre único para el archivo de imagen
                            $nombreArchivo = uniqid() . '.' . $imagen->getClientOriginalExtension();
                            // Almacena la imagen en la carpeta public/img
                            $imagen->move(public_path('img/garantias/'), $nombreArchivo);
                        }
                        DB::table('imagen')->insert([
                            'imagen'=>$nombreArchivo,
                            'id_garantia'=>$id_garantia,
                        ]);
                    }
                }
            }
            DB::commit();
        }catch(Exception $e){
            DB::rollback();
        }
    }

    public function guardarImagenIndividual(Request $request)
    {
        // Validate incoming request
        $validated = $request->validate([
            'imagen' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB
            'id_garantia' => 'required|integer|exists:garantia,id',
            'id_imagen' => 'nullable|integer|exists:imagen,id',
        ]);

        DB::beginTransaction();

        try {
            // Ensure the img/garantia directory exists
            $directory = public_path('img/garantia');
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            // Handle the image file
            $image = $request->file('imagen');
            $filename = uniqid() . '.' . $image->getClientOriginalExtension();
            $relativePath = 'img/garantia/' . $filename;

            // Move the image to the public/img/garantia directory
            $image->move($directory, $filename);

            // Prepare image data
            $imageData = [
                'imagen' => $relativePath,
                'id_garantia' => $validated['id_garantia'],
                'updated_at' => now(),
            ];

            if ($validated['id_imagen']) {
                // Update existing image
                DB::table('imagen')
                    ->where('id', $validated['id_imagen'])
                    ->update($imageData);

                $imageId = $validated['id_imagen'];
            } else {
                // Insert new image
                $imageData['created_at'] = now();
                $imageId = DB::table('imagen')->insertGetId($imageData);
            }

            DB::commit();

            // Return the saved image details
            return response()->json([
                'id_imagen' => $imageId,
                'id_garantia' => $validated['id_garantia'],
                'imagen' => $relativePath,
            ], 200);
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error saving image: ' . $e->getMessage(), [
                'request' => $request->all(),
                'exception' => $e,
            ]);
            return response()->json([
                'message' => 'No se pudo guardar la imagen',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getImagenesGarantia(Request $request){
        $imagenes=DB::table('imagen')->join('garantia', 'imagen.id_garantia', '=', 'garantia.id')
        ->select('imagen.*')
        ->where('imagen.id_garantia', $request->id_garantia)
        ->get();

        return $imagenes;
    }
    public function eliminarImagen(Request $request){
        DB::table('imagen')->where('imagen.id', $request->id_imagen)->delete();
    }

    public function ListaCuotasPdf(Request $request){
        // 1. Obtenemos la información de la base de datos
        $informacion = DB::table('solicitud')
            ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')
            ->join('users', 'users.id', '=', 'solicitud.id_usuario')
            ->where('solicitud.id', $request->id_solicitud)
            ->select('solicitud.id','solicitud.importe_solicitud', 'solicitud.moneda', 'solicitud.lapso_capital', 'solicitud.nro_cuotas',
            'solicitud.tasa', 'solicitud.fecha_desembolso', 'solicitud.fecha_primera_cuota', 'solicitud.destino_prestamo',
            'solicitud.tipo_garantia','solicitud.tipo_desembolso','solicitud.id_cliente', 'solicitud.id_usuario', 'cliente.nombre as cliente',
            'cliente.ci', 'cliente.lugar_expedicion', 'solicitud.monto_pago_adm',
            'users.name as asesor', 'solicitud.estado', 'solicitud.fecha as fecha_solicitud')
            ->get();

        // Validamos que se haya encontrado la solicitud para evitar errores
        if ($informacion->isEmpty()) {
            return redirect()->back()->with('error', 'No se encontró la solicitud.');
        }

        // 2. Preparamos los detalles y la data para la vista
        $detalles = json_decode($request->detalles, true);

        $html = [
            'informacion' => $informacion,
            'detalles' => $detalles,
            'usuario' => auth()->user()->name,
            'fecha_reporte' => now()->format('d/m/Y'),
        ];

        // 3. Obtenemos el estado de la solicitud
        $estadoSolicitud = $informacion[0]->estado;

        // 4. Evaluamos el estado para generar el PDF correspondiente
        if ($estadoSolicitud == 1) {
            // Genera el reporte con la marca de agua
            return $this->generatePDFMarcaAgua($html, 'reporte.simulacion_plan_pago', 'plan_de_pagos', 'SIN APROBAR - NO VÁLIDO');
        } else {
            // Genera el reporte normal (aplica para estado == 1 y otros)
            return $this->generatePDF($html, 'reporte.simulacion_plan_pago', 'plan_de_pagos');
        }
    }

    public function savePlanPagosCuotas(Request $request){
        DB::beginTransaction();
        try{
            $solicitud=DB::table('solicitud')
            ->where('solicitud.id', $request->id_solicitud)
            ->get();
    
            $detalles = json_decode($request->detalles, true);
            $plan_pagoId = DB::table('plan_pago')->insertGetId([
                'fecha_inicio'=>$request->fecha_inicio_plan_pago,
                'fecha_ultima_amortizacion'=>$request->fecha_primera_cuota,
                'fecha_fin'=>$request->fecha_final,

                'tasa'=>$request->tasa,
                'moneda'=>$request->moneda,
                'lapso_capital'=>$request->lapso_capital,
                'nro_cuotas'=>$request->nro_cuotas,
                'fecha_registro'=>now(),

                'total_pagar'=>$solicitud[0]->importe_solicitud,
                'id_solicitud'=>$request->id_solicitud,
            ]);

            foreach($detalles as $detalle){
                DB::table('cuota')->insert([
                    'numero'=>$detalle['nro'],
                    'fecha'=>$detalle['fecha'],
                    'capital'=>$detalle['capital'],
                    'interes'=>$detalle['interes'],
                    'saldo_capital'=>$detalle['saldo_capital'],
                    'ahorro'=>empty($detalle['ahorro'])?0:$detalle['ahorro'],
                    'seguro'=>empty($detalle['seguro'])?0:$detalle['seguro'],
                    'total'=>$detalle['total_cuota'],
                    'id_plan_pago'=>$plan_pagoId,
                ]);
            }

            DB::table('solicitud')->where('id', $request->id_solicitud)->update([
                'estado'=>2
            ]);

            DB::commit();
        }catch(Exception $e){
            DB::rollback();
        }
        

    }

    public function imprimirCuotasSimulacion(Request $request){

    
        $solicitud_simulacion = json_decode($request->solicitud_simulacion, true);
        $detalles = json_decode($request->detalles, true);

        // Prepare data for the HTML template
        $html = [
            'solicitud_simulacion' => $solicitud_simulacion,
            'detalles' => $detalles,
            'usuario' => auth()->user()->name ?? 'Sistema',
            'fecha_reporte' => now()->format('d/m/Y'),
            'hora_reporte' => now()->format('H:i'),
        ];

        // Generate the PDF using the provided template
        $this->generatePDFMarcaAgua($html, 'reporte.calculo_cuotas_simulacion', 'calculo_cuotas_simulacion');
    }

    public function guardarGarantiasImagenes(Request $request)
    {
        try {

            // Basic input checks
            $garantiasJson = $request->input('garantias');
            $deletedImagesJson = $request->input('deletedImages', '[]');
            $deletedGarantiasJson = $request->input('deletedGarantias', '[]');

            if (!$garantiasJson || !($garantias = json_decode($garantiasJson, true))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or missing garantias data',
                ], 422);
            }

            $deletedImages = json_decode($deletedImagesJson, true) ?: [];
            $deletedGarantias = json_decode($deletedGarantiasJson, true) ?: [];

            // Start a transaction
            DB::beginTransaction();

            // Handle deleted guarantees
            if (!empty($deletedGarantias)) {
                DB::table('garantia')->whereIn('id', $deletedGarantias)->delete();
                DB::table('imagen')->whereIn('id_garantia', $deletedGarantias)->delete();
                \Log::info('Deleted garantias: ' . implode(', ', $deletedGarantias));
            }

            // Handle deleted images
            if (!empty($deletedImages)) {
                $imagesToDelete = DB::table('imagen')->whereIn('id', $deletedImages)->get();
                foreach ($imagesToDelete as $image) {
                    $filePath = 'public/img/garantia/' . $image->imagen;
                    if ($image->imagen !== 'default.png' && Storage::exists($filePath)) {
                        Storage::delete($filePath);
                        \Log::info("Deleted image file: {$filePath}");
                    }
                    DB::table('imagen')->where('id', $image->id)->delete();
                }
                \Log::info('Deleted images: ' . implode(', ', $deletedImages));
            }

            // Process guarantees and images
            $updatedGarantias = [];
            foreach ($garantias as $index => $garantiaData) {
                // Check required fields
                if (
                    !isset($garantiaData['id'], $garantiaData['descripcion'], $garantiaData['id_solicitud'], $garantiaData['lista_imagenes']) ||
                    !is_string($garantiaData['descripcion']) ||
                    !is_array($garantiaData['lista_imagenes'])
                ) {
                    throw new Exception("Invalid garantia data at index {$index}");
                }

                // Verify id_solicitud
                if (!DB::table('solicitud')->where('id', $garantiaData['id_solicitud'])->exists()) {
                    throw new Exception("Invalid id_solicitud at index {$index}");
                }

                // Update or create garantia
                $garantiaId = $garantiaData['id'];
                if ($garantiaId && DB::table('garantia')->where('id', $garantiaId)->exists()) {
                    DB::table('garantia')->where('id', $garantiaId)->update([
                        'id_solicitud' => $garantiaData['id_solicitud'],
                        'descripcion' => $garantiaData['descripcion'],
                        'updated_at' => now(),
                    ]);
                    \Log::info("Updated garantia ID: {$garantiaId}");
                } else {
                    $garantiaId = DB::table('garantia')->insertGetId([
                        'id_solicitud' => $garantiaData['id_solicitud'],
                        'descripcion' => $garantiaData['descripcion'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    \Log::info("Created garantia ID: {$garantiaId}");
                }

                // Process images
                $imagenes = [];
                foreach ($garantiaData['lista_imagenes'] as $imgIndex => $imagenData) {
                    // Skip existing images unless updating
                    if (!empty($imagenData['id_imagen']) && $existingImage = DB::table('imagen')->where('id', $imagenData['id_imagen'])->first()) {
                        $imagenes[] = $existingImage;
                        \Log::info("Kept existing image ID: {$existingImage->id}");
                        continue;
                    }

                    // Handle new image or default
                    $fileName = 'default.png'; // Default image
                    $imageId = !empty($imagenData['id_imagen']) ? $imagenData['id_imagen'] : null;

                    if (isset($imagenData['file_key']) && $request->hasFile($imagenData['file_key'])) {
                        $file = $request->file($imagenData['file_key']);
                        $mimeType = $file->getMimeType();
                        $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg'];

                        if (!in_array($mimeType, $allowedMimes) || $file->getSize() > 2048 * 1024) {
                            \Log::warning("Invalid image at garantia {$index}, image {$imgIndex}: MIME={$mimeType}, Size={$file->getSize()}");
                            continue; // Skip invalid image, use default
                        }

                        // Store image
                        $fileName = time() . "_{$index}_{$imgIndex}." . $file->getClientOriginalExtension();
                        $destinationPath = public_path('img/garantia');
                        if (!file_exists($destinationPath)) {
                            mkdir($destinationPath, 0755, true);
                        }
                        $file->move($destinationPath, $fileName);
                        $fullFilePath = 'img/garantia/' . $fileName;
                        \Log::info("Stored image: {$fullFilePath}");
                    } else {
                        \Log::info("No image file provided at garantia {$index}, image {$imgIndex}, using default.png");
                    }

                    // Insert or update image
                    if ($imageId && DB::table('imagen')->where('id', $imageId)->exists()) {
                        DB::table('imagen')->where('id', $imageId)->update([
                            'id_garantia' => $garantiaId,
                            'imagen' => $fileName,
                            'updated_at' => now(),
                        ]);
                        \Log::info("Updated image ID: {$imageId}");
                    } else {
                        $imageId = DB::table('imagen')->insertGetId([
                            'id_garantia' => $garantiaId,
                            'imagen' => $fileName,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        \Log::info("Created image ID: {$imageId}");
                    }

                    $imagenes[] = (object) [
                        'id' => $imageId,
                        'id_garantia' => $garantiaId,
                        'imagen' => $fileName,
                    ];
                }

                $updatedGarantias[] = (object) [
                    'id' => $garantiaId,
                    'id_solicitud' => $garantiaData['id_solicitud'],
                    'descripcion' => $garantiaData['descripcion'],
                    'imagenes' => $imagenes,
                ];
            }

            // Commit transaction
            DB::commit();

            // Format response
            $responseData = array_map(function ($garantia) {
                return [
                    'id' => $garantia->id,
                    'id_solicitud' => $garantia->id_solicitud,
                    'descripcion' => $garantia->descripcion,
                    'lista_imagenes' => array_map(function ($imagen) {
                        return [
                            'id_imagen' => $imagen->id,
                            'id_garantia' => $imagen->id_garantia,
                            'imagen' => $imagen->imagen,
                        ];
                    }, $garantia->imagenes),
                ];
            }, $updatedGarantias);

            \Log::info('Garantias saved successfully', ['count' => count($responseData)]);

            return response()->json([
                'success' => true,
                'data' => $responseData,
                'message' => 'Garantías e imágenes guardadas exitosamente',
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('Error saving garantias: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar garantías: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function guardarObservacion(Request $request)
    {
        try {
            $id_solicitud = $request->input('id_solicitud');
            $observacion = $request->input('observacion');

            if (!$id_solicitud) {
                return response()->json([
                    'success' => false,
                    'message' => 'Faltan datos requeridos',
                ], 422);
            }

            DB::table('solicitud')
                ->where('id', $id_solicitud)
                ->update([
                    'observacion' => empty($observacion)?'':$observacion,
                    'updated_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Observación guardada exitosamente',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error saving observacion: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la observación: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function reporteHojaAprobacion(Request $request)
    {
        // Decodificar los datos enviados desde el frontend
        $solicitud = json_decode($request->get('solicitud'), true);

        $id_solicitud=$solicitud['id_solicitud'];
        $id_cliente=$solicitud['id_cliente'];
        $credito=DB::table('plan_pago')->where('plan_pago.id_solicitud', $id_solicitud)->first();

        $direcciones=DB::table('direccion')->where('direccion.id_cliente', $id_cliente)->get();

        $telefonos=DB::table('telefono')->where('telefono.id_cliente', $id_cliente)->get();
    

        $codeudores = DB::table('solicitud_codeudor')
        ->join('codeudor', 'solicitud_codeudor.id_codeudor', '=', 'codeudor.id')
        ->leftJoin('direccion', 'codeudor.id', '=', 'direccion.id_codeudor')
        ->leftJoin('telefono', 'codeudor.id', '=', 'telefono.id_codeudor')
        ->where('solicitud_codeudor.id_solicitud', $id_solicitud)
        ->select(
            'codeudor.*',
            'direccion.id as direccion_id',
            'direccion.tipo as direccion_tipo',
            'direccion.departamento',
            'direccion.ciudad',
            'direccion.zona',
            'direccion.descripcion',
            'direccion.referencia',
            'telefono.id as telefono_id',
            'telefono.tipo as telefono_tipo',
            'telefono.numero',
            'telefono.nombre',
            'telefono.apellidos',
            'telefono.relacion',
            'telefono.observacion'
        )
        ->get();

        $codeudoresProcesados = [];
        foreach ($codeudores as $codeudor) {
            if (!isset($codeudoresProcesados[$codeudor->id])) {
                $codeudoresProcesados[$codeudor->id] = [
                    'id' => $codeudor->id,
                    'ci' => $codeudor->ci,
                    'nombre' => $codeudor->nombre,
                    'direcciones' => [],
                    'telefonos' => []
                ];
            }

            if (!empty($codeudor->direccion_id)) {
                $codeudoresProcesados[$codeudor->id]['direcciones'][] = (object)[
                    'id' => $codeudor->direccion_id,
                    'tipo' => $codeudor->direccion_tipo,
                    'departamento' => $codeudor->departamento,
                    'ciudad' => $codeudor->ciudad,
                    'zona' => $codeudor->zona,
                    'descripcion' => $codeudor->descripcion,
                    'referencia' => $codeudor->referencia
                ];
            }

            if (!empty($codeudor->telefono_id)) {
                $codeudoresProcesados[$codeudor->id]['telefonos'][] = (object)[
                    'id' => $codeudor->telefono_id,
                    'tipo' => $codeudor->telefono_tipo,
                    'numero' => $codeudor->numero,
                    'nombre' => $codeudor->nombre,
                    'apellidos' => $codeudor->apellidos,
                    'relacion' => $codeudor->relacion,
                    'observacion' => $codeudor->observacion
                ];
            }
        }
        
        $id_credito=$credito->id;
        $fecha_inicio=$credito->fecha_inicio;

        // Extraer los datos necesarios
        $data = [
            'usuario' => auth()->user()->name ?? 'Sistema',
            'fecha_reporte' => now()->format('d/m/Y'),
            'hora_reporte' => now()->format('H:i'),
            'cliente_nombre' => $solicitud['cliente'] ?? '-',
            'ci' => $solicitud['ci'] ?? '-',
            'lugar_expedicion' => $solicitud['lugar_expedicion'] ?? '',
            'direcciones' => $direcciones,
            'telefonos' => $telefonos,
            'importe_solicitud' => $solicitud['importe_solicitud'] ?? 0,
            'nro_cuotas' => $solicitud['nro_cuotas'] ?? 0,
            'lapso_capital' => $solicitud['lapso_capital'] ?? '-',
            'tasa' => $solicitud['tasa'] ?? 0,
            'tipo_garantia' => $solicitud['tipo_garantia'] ?? '-',
            'asesor' => $solicitud['asesor'] ?? '-',
            'actividad' => $solicitud['actividad'] ?? '-',
            'personal' => $solicitud['personal'] ?? '-',
            'id_credito'=> $id_credito?? 0,
            'fecha_inicio'=> $fecha_inicio?? 0,
            'codeudores'=> array_values($codeudoresProcesados),
            'id_usuario' => $solicitud['id_usuario'] ?? '-',
            'asesor' => $solicitud['asesor'] ?? '-',

        ];

        // Generar el PDF
        $this->generatePDF($data, 'reporte.hoja_aprobacion_credito', 'Hoja_Aprobacion_Credito');
    }

     public function reporteHojaSolicitud(Request $request)
    {
        // Decodificar los datos enviados desde el frontend
        $solicitud = json_decode($request->get('solicitud'), true);

        $id_solicitud=$solicitud['id_solicitud'];
        $id_cliente=$solicitud['id_cliente'];
        $fecha_solicitud=$solicitud['fecha_solicitud'];
        
        $direcciones=DB::table('direccion')->where('direccion.id_cliente', $id_cliente)->get();
        $telefonos=DB::table('telefono')->where('telefono.id_cliente', $id_cliente)->get();
    

        $codeudores = DB::table('solicitud_codeudor')
        ->join('codeudor', 'solicitud_codeudor.id_codeudor', '=', 'codeudor.id')
        ->leftJoin('direccion', 'codeudor.id', '=', 'direccion.id_codeudor')
        ->leftJoin('telefono', 'codeudor.id', '=', 'telefono.id_codeudor')
        ->where('solicitud_codeudor.id_solicitud', $id_solicitud)
        ->select(
            'codeudor.*',
            'direccion.id as direccion_id',
            'direccion.tipo as direccion_tipo',
            'direccion.departamento',
            'direccion.ciudad',
            'direccion.zona',
            'direccion.descripcion',
            'direccion.referencia',
            'telefono.id as telefono_id',
            'telefono.tipo as telefono_tipo',
            'telefono.numero',
            'telefono.nombre',
            'telefono.apellidos',
            'telefono.relacion',
            'telefono.observacion'
        )
        ->get();

        $codeudoresProcesados = [];
        foreach ($codeudores as $codeudor) {
            if (!isset($codeudoresProcesados[$codeudor->id])) {
                $codeudoresProcesados[$codeudor->id] = [
                    'id' => $codeudor->id,
                    'ci' => $codeudor->ci,
                    'nombre' => $codeudor->nombre,
                    'direcciones' => [],
                    'telefonos' => []
                ];
            }

            if (!empty($codeudor->direccion_id)) {
                $codeudoresProcesados[$codeudor->id]['direcciones'][] = (object)[
                    'id' => $codeudor->direccion_id,
                    'tipo' => $codeudor->direccion_tipo,
                    'departamento' => $codeudor->departamento,
                    'ciudad' => $codeudor->ciudad,
                    'zona' => $codeudor->zona,
                    'descripcion' => $codeudor->descripcion,
                    'referencia' => $codeudor->referencia
                ];
            }

            if (!empty($codeudor->telefono_id)) {
                $codeudoresProcesados[$codeudor->id]['telefonos'][] = (object)[
                    'id' => $codeudor->telefono_id,
                    'tipo' => $codeudor->telefono_tipo,
                    'numero' => $codeudor->numero,
                    'nombre' => $codeudor->nombre,
                    'apellidos' => $codeudor->apellidos,
                    'relacion' => $codeudor->relacion,
                    'observacion' => $codeudor->observacion
                ];
            }
        }



        // Extraer los datos necesarios
        $data = [
            'usuario' => auth()->user()->name ?? 'Sistema',
            'fecha_reporte' => now()->format('d/m/Y'),
            'hora_reporte' => now()->format('H:i'),
            'cliente_nombre' => $solicitud['cliente'] ?? '-',
            'ci' => $solicitud['ci'] ?? '-',
            'lugar_expedicion' => $solicitud['lugar_expedicion'] ?? '',
            'direcciones' => $direcciones,
            'telefonos' => $telefonos,
            'importe_solicitud' => $solicitud['importe_solicitud'] ?? 0,
            'nro_cuotas' => $solicitud['nro_cuotas'] ?? 0,
            'lapso_capital' => $solicitud['lapso_capital'] ?? '-',
            'tasa' => $solicitud['tasa'] ?? 0,
            'tipo_garantia' => $solicitud['tipo_garantia'] ?? '-',
            'asesor' => $solicitud['asesor'] ?? '-',
            'actividad' => $solicitud['actividad'] ?? '-',
            'personal' => $solicitud['personal'] ?? '-',
            'fecha_solicitud'=> $fecha_solicitud?? 0,
            'codeudores'=> array_values($codeudoresProcesados),
            'id_usuario' => $solicitud['id_usuario'] ?? '-',
            'id_solicitud' => $id_solicitud?? '-',
            'asesor' => $solicitud['asesor'] ?? '-',


        ];

        // Generar el PDF
        $this->generatePDF($data, 'reporte.hoja_solicitud_credito', 'Hoja_Solicitud_Credito');
    }
    

    private function generatePDFMarcaAgua($data, $url_vista, $nombre_reporte, $marcaAguaTexto = 'SIMULACIÓN - NO VÁLIDO')
    {
        try {
            // Configuración inicial de MPDF
            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8', 
                'format' => 'letter', 
                'orientation' => 'P', 
                'margin_left' => 10, 
                'margin_right' => 10,
                'margin_top' => 10,
                'margin_bottom' => 10,
            ]);

            // --- INICIO AGREGAR ESTO ---
            
            // 1. Establecer el texto de la marca de agua
            $mpdf->SetWatermarkText($marcaAguaTexto, 0.1); // El segundo parámetro es el tamaño del texto (opcional)
            
            // 2. Hacer visible la marca de agua (True)
            $mpdf->showWatermarkText = true;
            
            // 3. Opacidad (0.1 es muy suave, 0.5 es medio, 1 es sólido)
            $mpdf->watermarkTextAlpha = 0.15; 
            
            // --- FIN AGREGAR ESTO ---

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
            $mpdf->Output($nombre_reporte . '.pdf', 'I'); 

        } catch (\Exception $e) {
            \Log::error('Error al generar el PDF: ' . $e->getMessage());
            return response()->json(['error' => 'No se pudo generar el PDF.'], 500);
        }
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
                // 'margin_header' => 10,
                // 'margin_footer' => 10,
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

    public function listarCuotasPlanReprogramacion(Request $request){
        $id_solicitud = $request->input('id_solicitud');

        // Fetch cuotas and plan_pago details
        $cuotas = DB::table('cuota')
            ->join('plan_pago', 'cuota.id_plan_pago', '=', 'plan_pago.id')
            ->join('solicitud', 'solicitud.id', '=', 'plan_pago.id_solicitud')
            ->select(
                'cuota.*',
                'plan_pago.fecha_inicio',
                'plan_pago.lapso_capital',
                DB::raw('CASE 
                            WHEN cuota.estado = 1 AND cuota.fecha < NOW() AND cuota.id = (
                                SELECT MIN(id) FROM cuota 
                                WHERE id_plan_pago = plan_pago.id AND estado = 1 AND fecha < NOW()
                            ) THEN DATEDIFF(NOW(), cuota.fecha) 
                            ELSE 0 
                        END as dias_pasados')
            )
            ->where('solicitud.id', $id_solicitud)
            ->orderBy('cuota.numero', 'asc')
            ->get();

        // Define totalDiasCuota based on lapso_capital
        $lapso_capital = $cuotas->first()->lapso_capital ?? 'Mensual';
        $totalDiasCuota = match ($lapso_capital) {
            'Semanal' => 7,
            'Quincenal' => 15,
            'Mensual' => 30,
            default => 30,
        };

        // Find the index of the first unpaid AND overdue cuota
        $firstUnpaidOverdueIndex = $cuotas->search(function ($cuota) {
            return $cuota->estado == 1 && Carbon::parse($cuota->fecha)->isPast();
        });

        // Initialize variables
        $dias_pasados_mora = 0;
        $interes_acumulado_total = 0;

        // Process cuotas
        $cuotas = $cuotas->map(function ($cuota, $index) use ($cuotas, $firstUnpaidOverdueIndex, $totalDiasCuota, &$dias_pasados_mora, &$interes_acumulado_total) {
            $fechaCuota = Carbon::parse($cuota->fecha);
            $fechaActual = Carbon::now();
            $diasTranscurridos = 0;
            $interesAcumulado = 0;

            // Define start date for the cuota
            $fechaInicioCuota = $index === 0
                ? Carbon::parse($cuotas->first()->fecha_inicio)
                : Carbon::parse($cuotas[$index - 1]->fecha);

            // Calculate dias_transcurridos
            if ($cuota->estado == 1 && $fechaCuota->isPast()) {
                $diasTranscurridos = $totalDiasCuota;
            } elseif ($fechaActual->between($fechaInicioCuota, $fechaCuota)) {
                $diasTranscurridos = $fechaActual->diffInDays($fechaInicioCuota);
            } else {
                $diasTranscurridos = 0;
            }

            // Calculate interes_acumulado
            if ($cuota->estado == 1 && $fechaCuota->isPast()) {
                $interesAcumulado = $cuota->total;
            } elseif ($fechaActual->between($fechaInicioCuota, $fechaCuota)) {
                $interesAcumulado = $cuota->interes * ($diasTranscurridos / $totalDiasCuota);
            } else {
                $interesAcumulado = 0;
            }

            // Set dias_pasados_mora for the first unpaid AND overdue cuota
            if ($index === $firstUnpaidOverdueIndex) {
                $dias_pasados_mora = max(0, $fechaActual->diffInDays($fechaCuota));
            }

            // Add to total accumulated interest
            $interes_acumulado_total += round($interesAcumulado, 0);

            // Add calculated fields to the cuota
            $cuota->dias_transcurridos = round($diasTranscurridos);
            $cuota->interes_acumulado = round($interesAcumulado, 0);

            return $cuota;
        });

        return [
            'cuotas' => $cuotas,
            'dias_pasados_mora' => $dias_pasados_mora,
            'interes_acumulado' => $interes_acumulado_total,
        ];
    }

    public function registrarSolicitudEspecial(Request $request)
    {
        // 1. VALIDACIONES
        //dd($request);
        $importeString = $request->input('plan_pago.importe_solicitud'); // "3.401"
        // 2. Quitar el punto (.)
        $importeLimpio = str_replace('.', '', $importeString); // "3401"

        // 3. Convertir a entero (o float)
        $importeEntero = (int) $importeLimpio; // 3401

        $request->validate([
            'tipo_operacion' => 'required|in:REPROGRAMACION,REFINANCIAMIENTO', // Nuevo campo
            'monto_adicional' => 'nullable|numeric|min:0', // Solo para refinanciamiento
            'plan_pago' => 'required|array',
            'plan_pago.id_cliente' => 'required|integer|exists:cliente,id',
            'plan_pago.id_solicitud' => 'required|integer|exists:solicitud,id',
            'plan_pago.importe_solicitud' => 'required|numeric|min:0',
            'plan_pago.forma_pago_reprogramacion' => 'required|string',
            'plan_pago.numero_cuotas_reprogramacion' => 'required|integer|min:1',
            'pago_intereses' => 'nullable|array'
        ]);

        $plan_pago = $request->input('plan_pago');
        $tipoOperacion = $request->input('tipo_operacion');
        $montoAdicional = $request->input('monto_adicional', 0);

        $pagoData = $request->input('pago_intereses');

        DB::beginTransaction();
        try {

            // 2. Obtener Solicitud Original
            $solicitudOriginal = Solicitud::find($plan_pago['id_solicitud']);
            if (!$solicitudOriginal) {
                throw new Exception('La solicitud original no fue encontrada.');
            }

            // 3. Configurar Variables Variables según el Tipo
            $tipoBD = '';
            $cntReprog = $solicitudOriginal->cantidad_reprogramaciones;
            $cntRefin = $solicitudOriginal->cantidad_refinanciamientos;
            $montoRefin = 0;

            if ($tipoOperacion === 'REFINANCIAMIENTO') {
                $tipoBD = 'Refinanciamiento';
                $cntRefin++; // Aumentamos refinanciamientos
                $montoRefin = $montoAdicional; // Guardamos el dinero fresco
            } else {
                $tipoBD = 'Reprogramacion';
                $cntReprog++; // Aumentamos reprogramaciones
            }

            // 4. Calcular Fechas
            $fechaCalculo = Carbon::today();
            $fechaPrimeraCuota = Carbon::parse($plan_pago['fecha_primera_cuota'] ?? Carbon::today());
            
            // Si no viene fecha, calculamos la tentativa
            if (!isset($plan_pago['fecha_primera_cuota'])) {
                 $formaPago = $plan_pago['forma_pago_reprogramacion'];
                 if ($formaPago === 'Mensual') $fechaPrimeraCuota->addMonth();
                 elseif ($formaPago === 'Quincenal') $fechaPrimeraCuota->addDays(15);
                 elseif ($formaPago === 'Semanal') $fechaPrimeraCuota->addWeek();
            }

            // 5. Crear la NUEVA Solicitud
            $nuevaSolicitud = Solicitud::create([
                // Vínculos
                'id_cliente' => $plan_pago['id_cliente'],
                'id_usuario' => Auth::id(),
                'id_solicitud_origen' => $solicitudOriginal->id,
                
                // Lógica Diferenciada
                'tipo_solicitud' => $tipoBD,
                'cantidad_reprogramaciones' => $cntReprog,
                'cantidad_refinanciamientos' => $cntRefin,
                'monto_refinanciamiento' => $montoRefin,
                
                // Datos Generales
                'moneda' => $plan_pago['moneda'],
                'destino_prestamo' => $plan_pago['destino_prestamo'],
                'tipo_garantia' => $plan_pago['tipo_garantia'],
                'tipo_desembolso' => $plan_pago['tipo_desembolso'],
                'tipo_tasa' => $plan_pago['tipo_tasa'],
                'tasa' => (float)$plan_pago['tasa'],
                'importe_solicitud' => $importeEntero,
                'lapso_capital' => $plan_pago['forma_pago_reprogramacion'],
                'nro_cuotas' => $plan_pago['numero_cuotas_reprogramacion'],
                
                'fecha' => $fechaCalculo,
                'fecha_desembolso' => $fechaCalculo,
                'fecha_primera_cuota' => $fechaPrimeraCuota->format('Y-m-d'),
                'estado' => 1, // Nuevo
            ]);
            
            $id_nueva = $nuevaSolicitud->id;

            // 6. COPIAR ENTIDADES (Garantías, Codeudores, Respaldos)
            $this->copiarEntidadesAsociadas($solicitudOriginal->id, $id_nueva);

            // 7. [NUEVO BLOQUE] REGISTRAR ORDEN DE PAGO (INTERESES/MORA)
            // Verificamos si el usuario seleccionó pagar algo en el frontend
            if ($pagoData && isset($pagoData['seleccionado']) && $pagoData['seleccionado'] == true) {
                
                // Combinar IDs de cuotas (interés y mora) en un solo array sin duplicados
                $idsInteres = $pagoData['ids_cuotas_interes'] ?? [];
                $idsMora = $pagoData['ids_cuotas_mora'] ?? [];
                $idsAfectados = array_values(array_unique(array_merge($idsInteres, $idsMora)));

                // Crear la orden
                OrdenPagoReprogramacion::create([
                    'id_solicitud_nueva' => $id_nueva,
                    // OJO: Asegúrate de enviar 'id_plan_pago' o 'id' del plan original desde el front
                    'id_plan_pago_origen' => $plan_pago['id'] ?? $plan_pago['id_plan_pago'], 
                    // Montos calculados (Información Bruta)
                    'monto_interes_calculado' => $pagoData['detalle_interes'] ?? 0,
                    'monto_mora_calculado' => $pagoData['detalle_mora'] ?? 0,
                    // Condonaciones
                    'se_condono_interes' => $pagoData['condonar_interes'] ?? false,
                    'monto_condonado_interes' => $pagoData['monto_condonar_interes'] ?? 0,
                    'se_condono_mora' => $pagoData['condonar_mora'] ?? false,
                    'monto_condonado_mora' => $pagoData['monto_condonar_mora'] ?? 0,
                    'motivo_condonacion' => $pagoData['motivo_condonacion'] ?? null,
                    // Total Final a Pagar (Caja cobrará esto)
                    'total_a_pagar' => $pagoData['total_pagar'],
                    // Detalles técnicos
                    'ids_cuotas_afectadas' => $idsAfectados,
                    'estado' => OrdenPagoReprogramacion::ESTADO_NO_DISPONIBLE // 0
                ]);
            }
            // =================================================================================

            $idPlanOrigen = $plan_pago['id'] ?? $plan_pago['id_plan_pago'];
            $planOriginalObj = PlanPago::find($idPlanOrigen);

            if ($planOriginalObj) {
                // Opción A: Cambiar estado a uno especial (Ej: 5 = En Proceso)
                // Asegúrate de que tus validaciones en otros lados ignoren el estado 5 para cobros normales
                $planOriginalObj->estado = 5; 
                
                // Opción B: Si tienes una columna 'bloqueado'
                // $planOriginalObj->bloqueado = true;

                $planOriginalObj->save();
            }

            DB::commit();
            $mensaje = ($tipoOperacion === 'REFINANCIAMIENTO') 
                ? 'Solicitud de Refinanciamiento registrada (Dinero extra: ' . $montoAdicional . ').'
                : 'Solicitud de Reprogramación registrada con éxito.';

            return response()->json([
                'message' => $mensaje,
                'solicitud_id' => $id_nueva
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // Helper para copiar (reutilizable)
    private function copiarEntidadesAsociadas($idOriginal, $idNuevo) {
        // Garantías
        $garantias = DB::table('garantia')->where('id_solicitud', $idOriginal)->get();
        foreach ($garantias as $g) {
            DB::table('garantia')->insert([
                'descripcion' => $g->descripcion, 'id_solicitud' => $idNuevo, 
                'created_at' => Carbon::now(), 'updated_at' => Carbon::now()
            ]);
        }
        // Codeudores
        $codeudores = DB::table('solicitud_codeudor')->where('id_solicitud', $idOriginal)->get();
        foreach ($codeudores as $c) {
            DB::table('solicitud_codeudor')->insert(['id_solicitud' => $idNuevo, 'id_codeudor' => $c->id_codeudor]);
        }
        // Respaldos
        $respaldos = DB::table('respaldo')->where('id_solicitud', $idOriginal)->get();
        foreach ($respaldos as $r) {
            DB::table('respaldo')->insert([
                'descripcion' => $r->descripcion, 'imagen' => $r->imagen, 'id_solicitud' => $idNuevo,
                'created_at' => Carbon::now(), 'updated_at' => Carbon::now()
            ]);
        }
    }

    public function calcularMonto($id_plan_pago)
    {
        // 1. Usar la fecha actual del servidor
        $hoy = Carbon::today(); 
        // --- Para propósitos de prueba, si tu BD está en el futuro: ---
        // $hoy = Carbon::parse('2025-11-14'); 

        // 2. Obtener el Plan de Pago y la Solicitud asociada
        $plan = PlanPago::with('solicitud')->find($id_plan_pago);

        if (!$plan || !$plan->solicitud) {
            return response()->json(['message' => 'Plan de pago o solicitud asociada no encontrados.'], 404);
        }

        $solicitud = $plan->solicitud;
        
        // 3. VALIDACIÓN (Tu Regla de Negocio: estado=1 es Pendiente)
        $cuotasVencidas = Cuota::where('id_plan_pago', $id_plan_pago)
            ->where('estado', 1) // 1 = Pendiente
            ->where('fecha', '<', $hoy)
            ->count();

        // if ($cuotasVencidas > 0) {
        //     return response()->json([
        //         'message' => 'No se puede reprogramar: El cliente tiene ' . $cuotasVencidas . ' cuota(s) vencida(s).'
        //     ], 422);
        // }

        // 4. CÁLCULO (Tu Lógica Mejorada)
        
        // A. Capital Pendiente (Sumando el capital de las cuotas pendientes)
        // ESTE ES TU NUEVO CÁLCULO:
        $capitalPendiente = (float)Cuota::where('id_plan_pago', $id_plan_pago)
            ->where('estado', 1) // 1 = Pendiente
            ->sum('capital'); // Suma la columna 'capital'

        // B. Interés Acumulado
        $interesAcumulado = 0.00;
        
        // (Si no hay fecha de último pago, usamos la fecha de desembolso de la solicitud)
        // Esta lógica sigue siendo correcta
        $fechaBase = $plan->fecha_ultima_amortizacion ?? $solicitud->fecha_desembolso;
        
        if ($fechaBase) {
            $fechaBase = Carbon::parse($fechaBase);
            $diasTranscurridos = $hoy->diffInDays($fechaBase);

            // Asegurarnos de que la tasa y el capital existan
            if ($diasTranscurridos > 0 && $capitalPendiente > 0 && $plan->tasa > 0) {
                
                // Tasa Mensual (ej: 10) -> Tasa Diaria
                $tasaDiaria = ($plan->tasa / 100) / 30; 
                
                // Usamos el capitalPendiente que acabamos de calcular
                $interesAcumulado = $capitalPendiente * $tasaDiaria * $diasTranscurridos;
            }
        }
        
        // C. Monto Total a Reprogramar
        $montoTotalReprogramar = $capitalPendiente + $interesAcumulado;

        // 5. DEVOLVER RESPUESTA
        return response()->json([
            // Enviamos el objeto 'solicitud' (que se convierte en 'plan_pago' en Vue)
            'plan_pago' => $solicitud, 
            'monto_a_reprogramar' => round($montoTotalReprogramar, 2)
        ]);
    }

    public function obtenerDatosOriginales($id_reprogramacion)
    {
        // 1. Buscar la solicitud de reprogramación
        $reprogramacion = Solicitud::find($id_reprogramacion);
        
        if (!$reprogramacion || !$reprogramacion->id_solicitud_origen) {
            return response()->json(['error' => 'No se encontró el crédito original vinculado.'], 404);
        }

        // 2. Buscar la solicitud ORIGINAL
        $original = Solicitud::with(['cliente'])->find($reprogramacion->id_solicitud_origen);
        
        // 3. Buscar el Plan de Pagos del original (el activo o el último)
        $planOriginal = PlanPago::where('id_solicitud', $original->id)->first();
        
        // 4. Traer las cuotas (para el historial)
        $cuotas = [];
        $saldoActual = 0;
        
        if ($planOriginal) {
            $cuotas = Cuota::where('id_plan_pago', $planOriginal->id)->get();
            // Calculamos el saldo capital real sumando las cuotas pendientes
            $saldoActual = Cuota::where('id_plan_pago', $planOriginal->id)
                                ->where('estado', 1) // Pendiente
                                ->sum('capital'); 
        }

        return response()->json([
            'solicitud_original' => $original,
            'plan_pago_original' => $planOriginal,
            'lista_cuotas' => $cuotas,
            'saldo_capital_actual' => $saldoActual,
            'moneda' => $original->moneda
        ]);
    }
    
    public function aprobarReprogramacionFinal(Request $request)
    {
        // 1. Validar datos básicos
        $request->validate([
            'id_solicitud' => 'required|exists:solicitud,id',
            'datos_actualizados' => 'required|array',
            'cuotas' => 'required|array|min:1'
        ]);

        $idSolicitud = $request->input('id_solicitud');
        $datos = $request->input('datos_actualizados');
        $cuotas = $request->input('cuotas');

        DB::beginTransaction();
        try {
            // 2. Obtener la Solicitud Nueva (que está en estado Pendiente)
            $nuevaSolicitud = Solicitud::find($idSolicitud);
            
            // === LÓGICA DE DESEMBOLSO (CRÍTICO) ===
            // Si es REFINANCIAMIENTO, el estado de desembolso es 0 (Pendiente de entregar efectivo en caja)
            // Si es REPROGRAMACION, el estado de desembolso es 1 (Ya desembolsado contablemente)
            $estadoDesembolso = ($nuevaSolicitud->tipo_solicitud === 'Refinanciamiento') ? 0 : 1;

            // 3. Actualizar Solicitud con datos finales editados por el aprobador
            $nuevaSolicitud->update([
                'importe_solicitud' => $datos['importe_solicitud'], // El monto Final
                'lapso_capital'     => $datos['lapso_capital'],
                'nro_cuotas'        => $datos['nro_cuotas'],
                'fecha_desembolso'  => $datos['fecha_desembolso'], // Fecha de Aprobación
                'fecha_primera_cuota' => $datos['fecha_primera_cuota'],
                'tasa'              => $datos['tasa'],
                
                'estado'            => 2, // 2 = APROBADO
                'desembolso'        => $estadoDesembolso 
            ]);

            // 4. Anular el Crédito Original (El Padre)
            if ($nuevaSolicitud->id_solicitud_origen) {
                $solicitudOriginal = Solicitud::find($nuevaSolicitud->id_solicitud_origen);
                
                if ($solicitudOriginal) {
                    // Estado 3 = Anulado/Reprogramado
                    $solicitudOriginal->update(['estado' => 3]); 
                }

                // Desactivar el Plan de Pagos Viejo
                $planOriginal = PlanPago::where('id_solicitud', $nuevaSolicitud->id_solicitud_origen)
                                        ->where('estado', 1)->first();
                                        
                if ($planOriginal) {
                    $planOriginal->update(['estado' => 0]); // 0 = Inactivo
                    
                    // Anular cuotas pendientes del plan viejo para que no sumen mora
                    Cuota::where('id_plan_pago', $planOriginal->id)
                         ->where('estado', 1)
                         ->update(['estado' => 0]);
                }
            }

            // 5. Crear el Nuevo Plan de Pagos
            // Calculamos la suma del CAPITAL puro (según tu requerimiento anterior)
            $totalCapital = collect($cuotas)->sum('capital'); 
            // $totalContrato = collect($cuotas)->sum('total_cuota'); // Si quisieras capital + interés

            $nuevoPlan = PlanPago::create([
                'id_solicitud'   => $nuevaSolicitud->id,
                'fecha_inicio'   => $datos['fecha_desembolso'],
                'fecha_fin'      => end($cuotas)['fecha'],
                'moneda'         => $nuevaSolicitud->moneda,
                'lapso_capital'  => $nuevaSolicitud->lapso_capital,
                'nro_cuotas'     => $nuevaSolicitud->nro_cuotas,
                'tasa'           => $nuevaSolicitud->tasa,
                'estado'         => 1, // 1 = Activo
                
                'desembolso'     => ($nuevaSolicitud->tipo_solicitud === 'Refinanciamiento') ? 1 : 0,// Hereda el estado
                
                'total_pagar'    => $totalCapital, // 6500 (Capital del préstamo)
                'saldo_pendiente'=> $totalCapital, // Inicia debiendo todo
                'fecha_registro' => Carbon::now()
            ]);

            // 6. Registrar las Nuevas Cuotas
            foreach ($cuotas as $c) {
                Cuota::create([
                    'id_plan_pago'  => $nuevoPlan->id,
                    'numero'        => $c['nro'],
                    'fecha'         => $c['fecha'],
                    'capital'       => $c['capital'],
                    'interes'       => $c['interes'],
                    'total'         => $c['total_cuota'],
                    'saldo_capital' => $c['saldo_capital'],
                    'estado'        => 1, 
                    'amortizado'    => 0
                ]);
            }

            // =========================================================================
            // 7. [NUEVO] ACTIVAR ORDEN DE PAGO DE INTERESES (SI EXISTE)
            // =========================================================================
            
            // Buscamos si existe una orden creada para esta solicitud específica
            $ordenPago = OrdenPagoReprogramacion::where('id_solicitud_nueva', $nuevaSolicitud->id)
                            ->where('estado', OrdenPagoReprogramacion::ESTADO_NO_DISPONIBLE) // Solo las pendientes
                            ->first();

            if ($ordenPago) {
                // Cambiamos el estado a 1 (POR PAGAR) para que aparezca en Caja
                $ordenPago->update([
                    'estado' => OrdenPagoReprogramacion::ESTADO_POR_PAGAR
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Operación aprobada exitosamente.'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function getDetalleCompletoSolicitud($id_solicitud)
    {
        try {
            $solicitud = Solicitud::with(['cliente', 'garantias', 'codeudores', 'respaldos'])->find($id_solicitud);

            if (!$solicitud) {
                return response()->json(['message' => 'Solicitud no encontrada.'], 404);
            }

            // Cargar Plan y Cuotas
            $plan = PlanPago::where('id_solicitud', $solicitud->id)->first();
            $cuotas = $plan ? $plan->cuotas : [];

            return response()->json([
                'solicitud' => $solicitud,
                'plan_pago' => $plan,
                'lista_cuotas' => $cuotas,
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al obtener detalle: ' . $e->getMessage()], 500);
        }
    }

    public function getOrdenPagoReprogramacion(Request $request)
    {
        try {
            if (!$request->has('id_solicitud')) {
                return response()->json(null); 
            }

            $idSolicitud = $request->input('id_solicitud');
            
            $orden = OrdenPagoReprogramacion::where('id_solicitud_nueva', $idSolicitud)->first();

            // Si no existe, devolvemos null explícitamente
            if (!$orden) {
                return response()->json(null); 
            }

            return response()->json($orden);

        } catch (\Exception $e) {
            \Log::error("Error al obtener orden de pago: " . $e->getMessage());
            return response()->json(['error' => 'Error interno'], 500);
        }
    }
}
