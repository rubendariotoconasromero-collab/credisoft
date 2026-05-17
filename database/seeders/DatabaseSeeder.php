<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ActividadSeeder::class,
            MiEmpresaSeeder::class,
            PermisoSeeder::class,
            RolSeeder::class,
            PermisoRolSeeder::class,
            UserSeeder::class,
            ClienteSeeder::class,
            CodeudorSeeder::class,
            SolicitudSeeder::class,
            MotivoSeeder::class,
            SocioSeeder::class,
        ]);
    }
}
