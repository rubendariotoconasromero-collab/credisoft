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
        // ── DATOS BASE ──────────────────────────────────────────────────
        // Necesarios para que el sistema funcione en cualquier entorno
        // (producción y pruebas): datos de referencia y configuración.
        $this->call([
            ActividadSeeder::class,
            MiEmpresaSeeder::class,
            PermisoSeeder::class,
            RolSeeder::class,
            PermisoRolSeeder::class,
            UserSeeder::class,   // internamente crea solo el admin en producción
            MotivoSeeder::class,
        ]);

        // ── DATOS DE DEMO / PRUEBA ──────────────────────────────────────
        // Datos ficticios de ejemplo. Se cargan solo si SEED_DEMO_DATA=true.
        // (Se lee desde config/app.php para que funcione aun con config:cache.)
        if (config('app.seed_demo_data')) {
            $this->call([
                ClienteSeeder::class,
                CodeudorSeeder::class,
                SolicitudSeeder::class,
                SocioSeeder::class,
            ]);
        }
    }
}
