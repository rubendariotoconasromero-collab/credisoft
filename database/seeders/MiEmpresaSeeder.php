<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MiEmpresaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('mi_empresa')->exists()) return;
        $identity = SeedProfile::resolve() === 'demo'
            ? [
                'nombre' => 'Financiera Horizonte Andino S.R.L.',
                'nit' => '4837162054',
                'direccion' => 'Av. Monseñor Rivero 318, Santa Cruz de la Sierra',
                'telefono' => '33468215',
                'email' => 'contacto@horizonteandino.test',
            ]
            : config('app.seed_operational.company');
        foreach (['nombre', 'nit', 'direccion', 'telefono', 'email'] as $field) {
            if (!is_string($identity[$field] ?? null) || trim($identity[$field]) === '') {
                throw new \LogicException("Configure SEED_COMPANY para el campo {$field}.");
            }
        }
        DB::table('mi_empresa')->insert($identity + [
            'id' => 1,
            'logo' => 'logo_sistema_codesoft.png',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
