<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Separa los permisos de Bóveda y Configuración (Motivos), que hasta ahora
 * compartían el permiso 'informacion' junto con Información de la Empresa,
 * Usuarios, Socios y Roles. Con esto se puede otorgar acceso a uno sin
 * otorgar los demás.
 *
 * Para no quitarle acceso a nadie: todo rol que hoy tenga 'informacion'
 * recibe automáticamente 'boveda' y 'configuracion' (replica exactamente
 * el acceso que ya tenía antes de este cambio).
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = Carbon::now();

        $idBoveda = DB::table('permiso')->insertGetId([
            'nombre' => 'boveda',
            'descripcion' => 'Gestión de bóveda física',
            'estado' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $idConfiguracion = DB::table('permiso')->insertGetId([
            'nombre' => 'configuracion',
            'descripcion' => 'Configuración de motivos de ingresos y egresos',
            'estado' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $idInformacion = DB::table('permiso')->where('nombre', 'informacion')->value('id');

        if ($idInformacion) {
            $rolesConInformacion = DB::table('permiso_rol')
                ->where('id_permiso', $idInformacion)
                ->pluck('id_rol');

            $nuevasFilas = [];
            foreach ($rolesConInformacion as $idRol) {
                $nuevasFilas[] = ['id_permiso' => $idBoveda, 'id_rol' => $idRol, 'created_at' => $now, 'updated_at' => $now];
                $nuevasFilas[] = ['id_permiso' => $idConfiguracion, 'id_rol' => $idRol, 'created_at' => $now, 'updated_at' => $now];
            }

            if (!empty($nuevasFilas)) {
                DB::table('permiso_rol')->insert($nuevasFilas);
            }
        }
    }

    public function down(): void
    {
        $ids = DB::table('permiso')->whereIn('nombre', ['boveda', 'configuracion'])->pluck('id');
        DB::table('permiso_rol')->whereIn('id_permiso', $ids)->delete();
        DB::table('permiso')->whereIn('nombre', ['boveda', 'configuracion'])->delete();
    }
};
