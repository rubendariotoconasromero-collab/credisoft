<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // One atomic seed. Never merge a fresh financial fixture with existing operations.
        if (DevelopmentSeeder::enabled()) DevelopmentSeeder::assertEmpty();
        DB::beginTransaction();
        try {
            $this->call([
                ActividadSeeder::class, MiEmpresaSeeder::class, PermisoSeeder::class,
                RolSeeder::class, PermisoRolSeeder::class, UserSeeder::class, MotivoSeeder::class,
            ]);
            if (DevelopmentSeeder::enabled()) $this->call(DevelopmentSeeder::class);
            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }
    }
}
