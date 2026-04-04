<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PermisoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Este método se ejecuta cuando corremos el comando de seed.
     */
    public function run(): void
    {
        // Obtenemos la fecha y hora actual para los timestamps
        $now = Carbon::now();

        // Definimos todos los permisos en un arreglo
        $permisos = [
            ['id' => 1, 'nombre' => 'roles', 'descripcion' => 'Gestión de roles de usuarios', 'estado' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'nombre' => 'informacion', 'descripcion' => 'Gestión información de la empresa', 'estado' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'nombre' => 'usuarios', 'descripcion' => 'Gestión de usuarios', 'estado' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'nombre' => 'paneladministracion', 'descripcion' => 'Vista panel administración', 'estado' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'nombre' => 'planpagos', 'descripcion' => ' Gestión de plan de pagos', 'estado' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 6, 'nombre' => 'listadopagos', 'descripcion' => 'Gestión de pagos - cuotas', 'estado' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 7, 'nombre' => 'controlcaja', 'descripcion' => 'Gestión de control de caja', 'estado' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 8, 'nombre' => 'cliente', 'descripcion' => 'Gestión de clientes', 'estado' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 9, 'nombre' => 'solicitudprestamos', 'descripcion' => 'Gestión de solicitudes de prestamos', 'estado' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 10, 'nombre' => 'reportes', 'descripcion' => 'Reportes', 'estado' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 12, 'nombre' => 'codeudores', 'descripcion' => 'Gestion de codeudores', 'estado' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 13, 'nombre' => 'consultasfinancieras', 'descripcion' => 'Consultas financieras y reportes', 'estado' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 14, 'nombre' => 'socios', 'descripcion' => 'Gestión de socios', 'estado' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 15, 'nombre' => 'historial_clientes_creditos', 'descripcion' => 'Historial de clientes y créditos', 'estado' => 1, 'created_at' => $now, 'updated_at' => $now],
        ];

        // Insertamos los datos en la tabla 'permiso'
        DB::table('permiso')->insert($permisos);
    }
}