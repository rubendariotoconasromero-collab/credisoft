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
            ClienteSeeder::class,
            CodeudorSeeder::class,
            SolicitudSeeder::class,
            // Aquí en el futuro llamaremos a CodeudorSeeder, SolicitudSeeder, etc.
        ]);
    }
}
