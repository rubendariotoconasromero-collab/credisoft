<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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
        $request->validate([
            'moneda' => ['required', Rule::in(['Bolivianos'])],
        ], [
            'moneda.in' => 'Actualmente solo se permiten solicitudes de crédito en bolivianos.',
        ]);

        DB::beginTransaction();
        try{
            $id_solicitud=\App\Services\CarteraAccess::query('solicitud')->insertGetId([
                'importe_solicitud'=>$request->importe_solicitud,
                'moneda'=>$request->moneda,
                'lapso_capital'=>$request->lapso_capital,
                'nro_cuotas'=>$request->nro_cuotas,
                'tasa'=>$request->tasa,
                'fecha_desembolso'=>$request->fecha_desembolso,
                'fecha'=>now(),
                'fecha_primera_cuota'=>$request->fecha_primera_cuota,
                'destino_prestamo'=>$request->destino_prestamo,
                'monto_pago_adm'=>\App\Services\Finance\AdministrativeFee::decimalFromAmount($request->importe_solicitud),
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
                    \App\Services\CarteraAccess::query('garantia')->insert([
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
        abort_if(DB::table('reprogramaciones_financieras')->where('id_solicitud', $request->id_solicitud)->exists(),
            422, 'Los términos de esta solicitud se ajustan desde Aprobar Reprogramación o Refinanciamiento. Para cambiar su origen o capital, cancele y registre una nueva.');
        DB::beginTransaction();
        try{
            $id_solicitud=\App\Services\CarteraAccess::query('solicitud')->where('id', $request->id_solicitud)->update([
                'importe_solicitud'=>$request->importe_solicitud,
                'moneda'=>$request->moneda,
                'lapso_capital'=>$request->lapso_capital,
                'nro_cuotas'=>$request->nro_cuotas,
                'tasa'=>$request->tasa,
                'fecha_desembolso'=>$request->fecha_desembolso,
                'fecha_primera_cuota'=>$request->fecha_primera_cuota,
                'destino_prestamo'=>$request->destino_prestamo,
                'monto_pago_adm'=>\App\Services\Finance\AdministrativeFee::decimalFromAmount($request->importe_solicitud),
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
                        if(!\App\Services\CarteraAccess::query('garantia')
                        ->join('solicitud','solicitud.id', '=', 'garantia.id_solicitud')
                        ->where('garantia.id', '=',$garantia['id_garantia'])
                        ->where('solicitud.id','=', $request->id_solicitud)
                        ->exists())
                        {
                            \App\Services\CarteraAccess::query('garantia')
                            ->insert([
                                'descripcion'=>$garantia['descripcion'],
                                'id_solicitud'=>$request->id_solicitud,
                            ]);
                        }
                    }  
                }else{
                    foreach($request->garantias as $garantia){
                    
                        \App\Services\CarteraAccess::query('garantia')
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
        $query = \App\Services\CarteraAccess::query('solicitud')
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

        if (\App\Services\CarteraAccess::restricted()) {
            $query->where('users.id', Auth::id()); // Optimización: Auth::id() es más conciso
        }

        $solicitudes = $query->orderBy('solicitud.id', 'desc')
                            ->paginate(100);

        return $solicitudes;
    }
    public function activarSolicitud(Request $request){
        abort_if(DB::table('reprogramaciones_financieras')->where('id_solicitud', $request->id_solicitud)->exists(), 422, 'Registre una nueva reprogramación con la deuda vigente.');
        \App\Services\CarteraAccess::query('solicitud')->where('solicitud.id', $request->id_solicitud)
        ->update([
            'estado'=>1
        ]);
    }

    public function desactivarSolicitud(Request $request){
        if (DB::table('reprogramaciones_financieras')->where('id_solicitud', $request->id_solicitud)->exists()) {
            return app(\App\Services\Finance\Rescheduling::class)->cancel($request);
        }
        \App\Services\CarteraAccess::query('solicitud')->where('solicitud.id', $request->id_solicitud)
        ->update([
            'estado'=>0
        ]);
    }

    public function getGarantias(Request $request){
        $garantias = \App\Services\CarteraAccess::query('garantia')
        ->join('solicitud','garantia.id_solicitud', '=', 'solicitud.id')
        ->select('garantia.*')
        ->where('solicitud.id', $request->id_solicitud)
        ->get();

        return ['garantias'=>$garantias];
    }

    public function guardarImagenes(Request $request)
    {
        return response()->json(['message' => 'Ruta retirada. Utilice el guardado de garantías del formulario actual.'], 410);
    }

    public function guardarImagenIndividual(Request $request)
    {
        // Validate incoming request
        $validated = $request->validate([
            'imagen' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB
            'id_garantia' => 'required|integer|exists:garantia,id',
            'id_imagen' => 'nullable|integer|exists:imagen,id',
        ]);
        if (!empty($validated['id_imagen'])) {
            abort_unless(DB::table('imagen')->where('id', $validated['id_imagen'])->where('id_garantia', $validated['id_garantia'])->exists(), 422, 'La imagen no pertenece a la garantía.');
        }

        DB::beginTransaction();

        try {
            // Ensure the img/garantia directory exists
            $directory = \App\Services\PrivateImages::directory('garantia');
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            // Handle the image file
            $image = $request->file('imagen');
            $filename = (string) \Illuminate\Support\Str::uuid() . '.' . $image->extension();
            $relativePath = 'img/garantia/' . $filename;

            // Move the image outside the web root
            $image->move($directory, $filename);

            // Prepare image data
            $imageData = [
                'imagen' => $relativePath,
                'id_garantia' => $validated['id_garantia'],
                'updated_at' => now(),
            ];

            if (!empty($validated['id_imagen'])) {
                $previousImage = \App\Services\CarteraAccess::query('imagen')->where('id', $validated['id_imagen'])->lockForUpdate()->value('imagen');
                \App\Services\PrivateImages::deleteAfterCommit('garantia', $previousImage);
                // Update existing image
                \App\Services\CarteraAccess::query('imagen')
                    ->where('id', $validated['id_imagen'])
                    ->update($imageData);

                $imageId = $validated['id_imagen'];
            } else {
                // Insert new image
                $imageData['created_at'] = now();
                $imageId = \App\Services\CarteraAccess::query('imagen')->insertGetId($imageData);
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
            if (isset($directory, $filename) && is_file($directory.DIRECTORY_SEPARATOR.$filename)) {
                @unlink($directory.DIRECTORY_SEPARATOR.$filename);
            }
            return response()->json([
                'message' => 'No se pudo guardar la imagen',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getImagenesGarantia(Request $request){
        $imagenes=\App\Services\CarteraAccess::query('imagen')->join('garantia', 'imagen.id_garantia', '=', 'garantia.id')
        ->select('imagen.*')
        ->where('imagen.id_garantia', $request->id_garantia)
        ->get();

        return $imagenes;
    }
    public function eliminarImagen(Request $request){
        \App\Services\CarteraAccess::query('imagen')->where('imagen.id', $request->id_imagen)->delete();
    }

    public function ListaCuotasPdf(Request $request){
        // 1. Obtenemos la información de la base de datos
        $informacion = \App\Services\CarteraAccess::query('solicitud')
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
        abort_if(DB::table('solicitud')->where('id', $request->id_solicitud)->whereNotNull('id_solicitud_origen')->exists(), 422, 'Use la aprobación de reprogramación para esta solicitud.');
        DB::beginTransaction();
        try{
            $solicitud=\App\Services\CarteraAccess::query('solicitud')
            ->where('solicitud.id', $request->id_solicitud)
            ->get();
    
            $detalles = json_decode($request->detalles, true);
            $plan_pagoId = \App\Services\CarteraAccess::query('plan_pago')->insertGetId([
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
                \App\Services\CarteraAccess::query('cuota')->insert([
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

            \App\Services\CarteraAccess::query('solicitud')->where('id', $request->id_solicitud)->update([
                'estado'=>2,
                'monto_pago_adm'=>\App\Services\Finance\AdministrativeFee::decimalFromAmount($solicitud[0]->importe_solicitud),
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
        return $this->generatePDFMarcaAgua($html, 'reporte.calculo_cuotas_simulacion', 'calculo_cuotas_simulacion');
    }

    public function guardarGarantiasImagenes(Request $request)
    {
        $createdFiles = [];
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
                foreach (\App\Services\CarteraAccess::query('imagen')->whereIn('id_garantia', $deletedGarantias)->get() as $oldImage) {
                    \App\Services\PrivateImages::deleteAfterCommit('garantia', $oldImage->imagen);
                }
                \App\Services\CarteraAccess::query('imagen')->whereIn('id_garantia', $deletedGarantias)->delete();
                \App\Services\CarteraAccess::query('garantia')->whereIn('id', $deletedGarantias)->delete();
                \Log::info('Deleted garantias: ' . implode(', ', $deletedGarantias));
            }

            // Handle deleted images
            if (!empty($deletedImages)) {
                $imagesToDelete = \App\Services\CarteraAccess::query('imagen')->whereIn('id', $deletedImages)->get();
                foreach ($imagesToDelete as $image) {
                    \App\Services\PrivateImages::deleteAfterCommit('garantia', $image->imagen);
                    \App\Services\CarteraAccess::query('imagen')->where('id', $image->id)->delete();
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
                if (!\App\Services\CarteraAccess::query('solicitud')->where('id', $garantiaData['id_solicitud'])->exists()) {
                    throw new Exception("Invalid id_solicitud at index {$index}");
                }

                // Update or create garantia
                $garantiaId = $garantiaData['id'];
                if ($garantiaId && \App\Services\CarteraAccess::query('garantia')->where('id', $garantiaId)->exists()) {
                    \App\Services\CarteraAccess::query('garantia')->where('id', $garantiaId)->update([
                        'id_solicitud' => $garantiaData['id_solicitud'],
                        'descripcion' => $garantiaData['descripcion'],
                        'updated_at' => now(),
                    ]);
                    \Log::info("Updated garantia ID: {$garantiaId}");
                } else {
                    $garantiaId = \App\Services\CarteraAccess::query('garantia')->insertGetId([
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
                    if (!empty($imagenData['id_imagen']) && $existingImage = \App\Services\CarteraAccess::query('imagen')->where('id', $imagenData['id_imagen'])->first()) {
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
                        $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];

                        if (!in_array($mimeType, $allowedMimes) || $file->getSize() > 2048 * 1024) {
                            \Log::warning("Invalid image at garantia {$index}, image {$imgIndex}: MIME={$mimeType}, Size={$file->getSize()}");
                            continue; // Skip invalid image, use default
                        }

                        // Store image
                        $fileName = (string) \Illuminate\Support\Str::uuid() . "." . $file->extension();
                        $destinationPath = \App\Services\PrivateImages::directory('garantia');
                        if (!file_exists($destinationPath)) {
                            mkdir($destinationPath, 0755, true);
                        }
                        $file->move($destinationPath, $fileName);
                        $createdFiles[] = $destinationPath.DIRECTORY_SEPARATOR.$fileName;
                        $fullFilePath = 'img/garantia/' . $fileName;
                        \Log::info("Stored image: {$fullFilePath}");
                    } else {
                        \Log::info("No image file provided at garantia {$index}, image {$imgIndex}, using default.png");
                    }

                    // Insert or update image
                    if ($imageId && \App\Services\CarteraAccess::query('imagen')->where('id', $imageId)->exists()) {
                        \App\Services\CarteraAccess::query('imagen')->where('id', $imageId)->update([
                            'id_garantia' => $garantiaId,
                            'imagen' => $fileName,
                            'updated_at' => now(),
                        ]);
                        \Log::info("Updated image ID: {$imageId}");
                    } else {
                        $imageId = \App\Services\CarteraAccess::query('imagen')->insertGetId([
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
            foreach ($createdFiles as $path) { if (is_file($path)) @unlink($path); }
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

            \App\Services\CarteraAccess::query('solicitud')
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
        $credito=\App\Services\CarteraAccess::query('plan_pago')->where('plan_pago.id_solicitud', $id_solicitud)->first();

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
        return $this->generatePDF($data, 'reporte.hoja_aprobacion_credito', 'Hoja_Aprobacion_Credito');
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
        return $this->generatePDF($data, 'reporte.hoja_solicitud_credito', 'Hoja_Solicitud_Credito');
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
            return response($mpdf->Output($nombre_reporte . '.pdf', 'S'))
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="'.$nombre_reporte . '.pdf'.'"');

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
            return response($mpdf->Output($nombre_reporte . '.pdf', 'S'))
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="'.$nombre_reporte . '.pdf'.'"');

        } catch (\Exception $e) {
            // Manejo de errores
            \Log::error('Error al generar el PDF: ' . $e->getMessage());
            return response()->json(['error' => 'No se pudo generar el PDF.'], 500);
        }
    }

    public function listarCuotasPlanReprogramacion(Request $request){
        $id_solicitud = $request->input('id_solicitud');

        // Fetch cuotas and plan_pago details
        $cuotas = \App\Services\CarteraAccess::query('cuota')
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
                $diasTranscurridos = (int) $fechaActual->diffInDays($fechaInicioCuota, true);
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
                $dias_pasados_mora = max(0, (int) $fechaActual->diffInDays($fechaCuota, true));
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
        return app(\App\Services\Finance\Rescheduling::class)->register($request,
            fn ($original, $nuevo) => $this->copiarEntidadesAsociadas($original, $nuevo));
    }
    // Helper para copiar (reutilizable)
    private function copiarEntidadesAsociadas($idOriginal, $idNuevo) {
        // Garantías
        $garantias = \App\Services\CarteraAccess::query('garantia')->where('id_solicitud', $idOriginal)->get();
        foreach ($garantias as $g) {
            \App\Services\CarteraAccess::query('garantia')->insert([
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
        $respaldos = \App\Services\CarteraAccess::query('respaldo')->where('id_solicitud', $idOriginal)->get();
        foreach ($respaldos as $r) {
            \App\Services\CarteraAccess::query('respaldo')->insert([
                'descripcion' => $r->descripcion, 'imagen' => $r->imagen, 'id_solicitud' => $idNuevo,
                'created_at' => Carbon::now(), 'updated_at' => Carbon::now()
            ]);
        }
    }

    public function calcularMonto($id_plan_pago)
    {
        \App\Services\CarteraAccess::assertRecord('plan_pago', $id_plan_pago);
        $plan = PlanPago::with('solicitud')->findOrFail($id_plan_pago);
        abort_unless($plan->solicitud && (int) $plan->estado === 1, 422, 'Seleccione un crédito activo.');
        $debt = app(\App\Services\Finance\Debt::class)->calculate((int) $plan->id);
        $amount = $debt['cuotas']->whereIn('estado', [1, 3])->sum(fn ($q) => \App\Services\Finance\Money::cents($q->total_a_pagar));
        return response()->json(['plan_pago' => $plan->solicitud,
            'monto_a_reprogramar' => \App\Services\Finance\Money::decimal($amount),
            'fecha_corte' => $debt['fecha_corte'], 'revision' => $debt['revision']]);
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
            $saldoActual = $cuotas->whereIn('estado', [1, 3])->sum(fn ($c) =>
                max(0, (float) $c->capital - (float) ($c->capital_pagado ?? 0)));
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
        return app(\App\Services\Finance\Rescheduling::class)->approve($request);
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
