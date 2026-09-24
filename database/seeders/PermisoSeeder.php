<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermisoSeeder extends Seeder
{
    public function run(): void
    {
        // The permission migration may already have inserted boveda/configuracion.
        // Resolve by name rather than assuming numeric IDs.
        foreach ([
            'roles' => 'Gestión de roles', 'informacion' => 'Información de la empresa',
            'usuarios' => 'Gestión de usuarios', 'paneladministracion' => 'Panel informativo',
            'planpagos' => 'Gestión de cartera', 'listadopagos' => 'Listado de pagos',
            'controlcaja' => 'Control de caja', 'cliente' => 'Gestión de clientes',
            'solicitudprestamos' => 'Solicitudes de crédito', 'reportes' => 'Reportes',
            'boveda' => 'Bóveda física', 'codeudores' => 'Gestión de codeudores',
            'consultasfinancieras' => 'Consultas financieras', 'socios' => 'Socios',
            'historial_clientes_creditos' => 'Historial de créditos', 'configuracion' => 'Motivos de movimientos',
            'condonar' => 'Condonar interés y multa', 'anular_finanzas' => 'Anular operaciones financieras propias',
            'pagos_retroactivos' => 'Registrar pagos con fecha efectiva anterior',
        ] as $name => $description) {
            DB::table('permiso')->updateOrInsert(['nombre' => $name], [
                'descripcion' => $description, 'estado' => 1, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }
}
