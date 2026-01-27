<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Storage;
class RespaldoController extends Controller
{
    //
    


    public function guardarRespaldo(Request $request)
    {
        

        DB::beginTransaction();
        
        try {
            $imagen = $request->imagen;
            $nombreArchivo = 'respaldos/' . uniqid() . '.' . $imagen->getClientOriginalExtension();
            //dd($imagen);
            // Mover la imagen al directorio correspondiente
            $imagen->move(public_path('img/respaldos'), $nombreArchivo);

            // Datos comunes para insertar/actualizar
            $respaldoData = [
                'imagen' => $nombreArchivo,
                'descripcion' => $request->descripcion,
                'updated_at' => now()
            ];

            // Si es una actualización
            if ($request->filled('id_respaldo')) {
                // Obtener el respaldo actual para eliminar su imagen anterior
                $respaldoActual = DB::table('respaldo')
                                    ->where('id', $request->id_respaldo)
                                    ->first();
                
                // Eliminar la imagen anterior si existe
                if ($respaldoActual && $respaldoActual->imagen) {
                    $rutaImagenAnterior = public_path('img/' . $respaldoActual->imagen);
                    if (file_exists($rutaImagenAnterior)) {
                        unlink($rutaImagenAnterior);
                    }
                }

                // Actualizar el respaldo existente
                DB::table('respaldo')
                    ->where('id', $request->id_respaldo)
                    ->update($respaldoData);

                $respaldoId = $request->id_respaldo;
            } else {
                // Insertar nuevo respaldo
                $respaldoData['id_solicitud'] = $request->id_solicitud;
                $respaldoData['created_at'] = now();
                
                $respaldoId = DB::table('respaldo')
                                ->insertGetId($respaldoData);
            }

            // Obtener los datos completos del respaldo
            $respaldo = DB::table('respaldo')
                        ->where('id', $respaldoId)
                        ->first();

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'imagen' => $respaldo->imagen,
                    'id_respaldo' => $respaldo->id,
                    'id_solicitud' => $respaldo->id_solicitud,
                    'descripcion' => $respaldo->descripcion
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            // Eliminar la imagen recién subida si hubo error
            if (isset($nombreArchivo) && file_exists(public_path('img/' . $nombreArchivo))) {
                unlink(public_path('img/' . $nombreArchivo));
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el respaldo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getRespaldos(Request $request)
    {
        $respaldos = DB::table('respaldo')
            ->select('id', 'id_solicitud', 'descripcion')
            ->where('id_solicitud', $request->id_solicitud)
            ->get();

        $respaldosWithImages = $respaldos->map(function ($respaldo) {
            $imagenes = DB::table('imagenes_respaldo')
                ->select('id', 'id_respaldo', 'imagen')
                ->where('id_respaldo', $respaldo->id)
                ->get();

            return [
                'id' => $respaldo->id,
                'id_solicitud' => $respaldo->id_solicitud,
                'descripcion' => $respaldo->descripcion,
                'lista_imagenes' => $imagenes->map(function ($imagen) {
                    return [
                        'id_imagen' => $imagen->id,
                        'id_respaldo' => $imagen->id_respaldo,
                        'imagen' => $imagen->imagen
                    ];
                })->toArray()
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $respaldosWithImages
        ]);
    }

    public function deleteRespaldo(Request $request){
        DB::table('respaldo')->where('id', $request->id_respaldo)
        ->delete();
    }

    public function guardarRespaldosImagenes(Request $request)
    {
        try {
            // Basic input checks
            $respaldosJson = $request->input('respaldos');
            $deletedImagesJson = $request->input('deletedImages', '[]');
            $deletedRespaldosJson = $request->input('deletedRespaldos', '[]');

            if (!$respaldosJson || !($respaldos = json_decode($respaldosJson, true))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or missing respaldos data'
                ], 422);
            }

            $deletedImages = json_decode($deletedImagesJson, true) ?: [];
            $deletedRespaldos = json_decode($deletedRespaldosJson, true) ?: [];

            // Start a transaction
            DB::beginTransaction();

            // Handle deleted respaldos
            if (!empty($deletedRespaldos)) {
                $imagesToDelete = DB::table('imagenes_respaldo')->whereIn('id_respaldo', $deletedRespaldos)->get();
                foreach ($imagesToDelete as $image) {
                    $filePath = 'public/img/respaldos/' . $image->imagen;
                    if ($image->imagen !== 'default.png' && Storage::exists($filePath)) {
                        Storage::delete($filePath);
                        \Log::info("Deleted image file: {$filePath}");
                    }
                }
                DB::table('imagenes_respaldo')->whereIn('id_respaldo', $deletedRespaldos)->delete();
                DB::table('respaldo')->whereIn('id', $deletedRespaldos)->delete();
                \Log::info('Deleted respaldos: ' . implode(', ', $deletedRespaldos));
            }

            // Handle deleted images
            if (!empty($deletedImages)) {
                $imagesToDelete = DB::table('imagenes_respaldo')->whereIn('id', $deletedImages)->get();
                foreach ($imagesToDelete as $image) {
                    $filePath = 'public/img/respaldos/' . $image->imagen;
                    if ($image->imagen !== 'default.png' && Storage::exists($filePath)) {
                        Storage::delete($filePath);
                        \Log::info("Deleted image file: {$filePath}");
                    }
                    DB::table('imagenes_respaldo')->where('id', $image->id)->delete();
                }
                \Log::info('Deleted images: ' . implode(', ', $deletedImages));
            }

            // Process respaldos and images
            $updatedRespaldos = [];
            foreach ($respaldos as $index => $respaldoData) {
                if (
                    !isset($respaldoData['id'], $respaldoData['descripcion'], $respaldoData['id_solicitud'], $respaldoData['lista_imagenes']) ||
                    !is_string($respaldoData['descripcion']) ||
                    !is_array($respaldoData['lista_imagenes'])
                ) {
                    throw new Exception("Invalid respaldo data at index {$index}");
                }

                if (!DB::table('solicitud')->where('id', $respaldoData['id_solicitud'])->exists()) {
                    throw new Exception("Invalid id_solicitud at index {$index}");
                }

                $respaldoId = $respaldoData['id'];
                if ($respaldoId && DB::table('respaldo')->where('id', $respaldoId)->exists()) {
                    DB::table('respaldo')->where('id', $respaldoId)->update([
                        'id_solicitud' => $respaldoData['id_solicitud'],
                        'descripcion' => $respaldoData['descripcion'],
                        'updated_at' => now()
                    ]);
                    \Log::info("Updated respaldo ID: {$respaldoId}");
                } else {
                    $respaldoId = DB::table('respaldo')->insertGetId([
                        'id_solicitud' => $respaldoData['id_solicitud'],
                        'descripcion' => $respaldoData['descripcion'],
                        'created_at' => now(),
                        'updated_at' => now(),
                        'imagen' => 'default.png',
                    ]);
                    \Log::info("Created respaldo ID: {$respaldoId}");
                }

                $imagenes = [];
                foreach ($respaldoData['lista_imagenes'] as $imgIndex => $imagenData) {
                    $fileName = 'default.png';
                    $imageId = !empty($imagenData['id_imagen']) ? $imagenData['id_imagen'] : null;

                    if (isset($imagenData['file_key']) && $request->hasFile($imagenData['file_key'])) {
                        $file = $request->file($imagenData['file_key']);
                        $mimeType = $file->getMimeType();
                        $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg'];

                        if (!in_array($mimeType, $allowedMimes) || $file->getSize() > 2048 * 1024) {
                            \Log::warning("Invalid image at respaldo {$index}, image {$imgIndex}: MIME={$mimeType}, Size={$file->getSize()}");
                            continue;
                        }

                        $fileName = time() . "_{$index}_{$imgIndex}." . $file->getClientOriginalExtension();
                        $destinationPath = public_path('img/respaldos');
                        if (!file_exists($destinationPath)) {
                            mkdir($destinationPath, 0755, true);
                        }
                        $file->move($destinationPath, $fileName);
                        $fullFilePath = 'img/respaldos/' . $fileName;
                        \Log::info("Stored image: {$fullFilePath}");
                    } else if ($imageId && ($existingImage = DB::table('imagenes_respaldo')->where('id', $imageId)->first())) {
                        $fileName = $existingImage->imagen;
                        $imagenes[] = $existingImage;
                        \Log::info("Kept existing image ID: {$existingImage->id}");
                        continue;
                    }

                    if ($imageId && DB::table('imagenes_respaldo')->where('id', $imageId)->exists()) {
                        DB::table('imagenes_respaldo')->where('id', $imageId)->update([
                            'id_respaldo' => $respaldoId,
                            'imagen' => $fileName,
                            'updated_at' => now()
                        ]);
                        \Log::info("Updated image ID: {$imageId}");
                    } else {
                        $imageId = DB::table('imagenes_respaldo')->insertGetId([
                            'id_respaldo' => $respaldoId,
                            'imagen' => $fileName,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                        \Log::info("Created image ID: {$imageId}");
                    }

                    $imagenes[] = (object) [
                        'id' => $imageId,
                        'id_respaldo' => $respaldoId,
                        'imagen' => $fileName
                    ];
                }

                $updatedRespaldos[] = (object) [
                    'id' => $respaldoId,
                    'id_solicitud' => $respaldoData['id_solicitud'],
                    'descripcion' => $respaldoData['descripcion'],
                    'lista_imagenes' => $imagenes
                ];
            }

            DB::commit();

            $responseData = array_map(function ($respaldo) {
                return [
                    'id' => $respaldo->id,
                    'id_solicitud' => $respaldo->id_solicitud,
                    'descripcion' => $respaldo->descripcion,
                    'lista_imagenes' => array_map(function ($imagen) {
                        return [
                            'id_imagen' => $imagen->id,
                            'id_respaldo' => $imagen->id_respaldo,
                            'imagen' => $imagen->imagen
                        ];
                    }, $respaldo->lista_imagenes)
                ];
            }, $updatedRespaldos);

            \Log::info('Respaldos saved successfully', ['count' => count($responseData)]);

            return response()->json([
                'success' => true,
                'data' => $responseData,
                'message' => 'Respaldos e imágenes guardadas exitosamente'
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('Error saving respaldos: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar respaldos: ' . $e->getMessage()
            ], 500);
        }
    }
}
