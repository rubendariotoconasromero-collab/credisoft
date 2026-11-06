<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use LogicException;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $profile = SeedProfile::resolve();
        // Both profiles describe an initial population, never an addition to live data.
        if ($profile === 'demo') {
            DevelopmentSeeder::assertEmpty();
        } else {
            foreach (['mi_empresa', 'users', 'cliente', 'codeudor', 'solicitud', 'plan_pago',
                'cuota', 'pago', 'caja', 'boveda', 'socios'] as $table) {
                if (DB::table($table)->exists()) {
                    throw new LogicException('La población operativa requiere una base sin datos de usuarios, clientes ni operaciones. No se mezclaron datos.');
                }
            }
        }
        DB::beginTransaction();
        try {
            $this->call([
                ActividadSeeder::class, MiEmpresaSeeder::class, PermisoSeeder::class,
                RolSeeder::class, PermisoRolSeeder::class, UserSeeder::class, MotivoSeeder::class,
            ]);
            if ($profile === 'demo') $this->call(DevelopmentSeeder::class);
            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }
    }
}
