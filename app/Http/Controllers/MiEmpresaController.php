<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class MiEmpresaController extends Controller
{
    //
    public function index(){
        return view('frmInformacion');
    }

    

    public function modify(Request $request)
    {
        // Validar los datos básicos
        $validatedData = $request->validate([
            'id_mi_empresa' => 'required|integer',
            'nombre' => 'required|string',
            'nit' => 'required|string',
            'direccion' => 'required|string',
            'email' => 'required|email',
            'telefono' => 'required|string',
            'imagen' => 'nullable|max:2048',
        ]);

        // Obtener la empresa actual
        $empresa = DB::table('mi_empresa')->where('id', $request->id_mi_empresa)->first();
        
        if (!$empresa) {
            return back()->with('error', 'Empresa no encontrada');
        }

        $nombreArchivo = null;
        
        // Procesar la imagen si se envió una nueva
        if(isset($request->imagen)){
            if ($request->hasFile('imagen') && $request->file('imagen')->isValid()) {
                $imagen = $request->file('imagen');
                
                // Eliminar la imagen anterior si existe
                if ($empresa->logo) {
                    $rutaImagenAnterior = public_path('img/' . $empresa->logo);
                    if (file_exists($rutaImagenAnterior)) {
                        unlink($rutaImagenAnterior);
                    }
                }
                
                // Generar nombre único y guardar la nueva imagen
                $extension = $imagen->getClientOriginalExtension();
                $nombreArchivo = 'empresa/' . uniqid() . '.' . $extension;
                $imagen->move(public_path('img/empresa'), $nombreArchivo);
            }
        }


        // Preparar datos para actualizar
        $dataToUpdate = [
            'nombre' => $request->nombre,
            'nit' => $request->nit,
            'direccion' => $request->direccion,
            'email' => $request->email,
            'telefono' => $request->telefono,
            'updated_at' => now(),
        ];
        
        // Solo actualizar el logo si se subió una nueva imagen
        if ($nombreArchivo) {
            $dataToUpdate['logo'] = $nombreArchivo;
        }

        // Actualizar la base de datos
        try {
            DB::table('mi_empresa')
                ->where('id', $request->id_mi_empresa)
                ->update($dataToUpdate);
                
            return back()->with('success', 'Empresa actualizada correctamente');
        } catch (\Exception $e) {
            // Si hay error, eliminar la imagen recién subida (si existe)
            if ($nombreArchivo) {
                $rutaImagenNueva = public_path('img/' . $nombreArchivo);
                if (file_exists($rutaImagenNueva)) {
                    unlink($rutaImagenNueva);
                }
            }
            return back()->with('error', 'Error al actualizar la empresa: ' . $e->getMessage());
        }
    }

    public function getMiEmpresa(){
        return DB::table('mi_empresa')->first();
    }
}
