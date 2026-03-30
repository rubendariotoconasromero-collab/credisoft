<?php

namespace App\Http\Controllers;

use App\Models\Socio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SocioController extends Controller
{
    public function indexAdmin(){
        return view('frmSocio');
    }

    public function getSociosActivos(Request $request)
    {
        $socios = Socio::where('estado', 1)
            ->select(
                'id', 
                'ci', 
                DB::raw("CONCAT(nombres, ' ', COALESCE(apellidos, '')) as nombre_completo")
            )
            ->orderBy('nombres', 'asc')
            ->get();
            
        return response()->json($socios);
    }

    public function index(Request $request)
    {
        $buscar = $request->buscar;
        $criterio = $request->criterio ?? 'nombres';
        
        $query = Socio::query();

        if ($buscar != '') {
            // Buscador flexible (puede buscar por nombre o CI)
            $query->where($criterio, 'like', '%' . $buscar . '%');
        }

        $socios = $query->orderBy('id', 'desc')->paginate(10);

        return [
            'pagination' => [
                'total'        => $socios->total(),
                'current_page' => $socios->currentPage(),
                'per_page'     => $socios->perPage(),
                'last_page'    => $socios->lastPage(),
                'from'         => $socios->firstItem(),
                'to'           => $socios->lastItem(),
            ],
            'data' => $socios->items()
        ];
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $socio = new Socio();
            $socio->nombres = $request->nombres;
            $socio->apellidos = $request->apellidos;
            $socio->ci = $request->ci;
            $socio->telefono = $request->telefono;
            $socio->direccion = $request->direccion;
            $socio->email = $request->email;
            $socio->estado = 1;
            $socio->save();
            DB::commit();
            return response()->json(['message' => 'Registrado correctamente'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al registrar: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            DB::beginTransaction();
            $socio = Socio::findOrFail($request->id);
            $socio->nombres = $request->nombres;
            $socio->apellidos = $request->apellidos;
            $socio->ci = $request->ci;
            $socio->telefono = $request->telefono;
            $socio->direccion = $request->direccion;
            $socio->email = $request->email;
            $socio->save();
            DB::commit();
            return response()->json(['message' => 'Actualizado correctamente'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al actualizar: ' . $e->getMessage()], 500);
        }
    }

    public function desactivar(Request $request)
    {
        $socio = Socio::findOrFail($request->id);
        $socio->estado = 0;
        $socio->save();
    }

    public function activar(Request $request)
    {
        $socio = Socio::findOrFail($request->id);
        $socio->estado = 1;
        $socio->save();
    }
    
    // Método extra para cargar en los Selects (Combobox) de tu Bóveda
    public function selectSocio(Request $request)
    {
        $socios = Socio::where('estado', 1)
            ->select('id', DB::raw("CONCAT(nombres, ' ', COALESCE(apellidos, '')) as nombre_completo"), 'ci')
            ->orderBy('nombres', 'asc')->get();
            
        return response()->json($socios);
    }
}