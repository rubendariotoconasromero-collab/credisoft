<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $demo = SeedProfile::resolve() === 'demo';
        $admin = config('app.seed_operational.admin');
        $accounts = $demo
            ? [['administrador', 'Administrador', 'Sofía Elena Paredes', 'sofia.paredes@horizonteandino.test', '6827419', '72145308']]
            : [['administrador', 'Administrador', $admin['personal'] ?? null,
                $admin['email'] ?? null, $admin['ci'] ?? null, $admin['telefono'] ?? null]];
        if ($demo) {
            $accounts = array_merge($accounts, [
                ['lucia.rivera', 'Cajero', 'Lucía Fernanda Rivera', 'lucia.rivera@horizonteandino.test', '6943185', '72836140'],
                ['andres.flores', 'Cajero', 'Andrés Felipe Flores', 'andres.flores@horizonteandino.test', '7359026', '71268439'],
                ['camila.ortiz', 'Oficial de Credito', 'Camila Alejandra Ortiz', 'camila.ortiz@horizonteandino.test', '7064281', '73619502'],
                ['rodrigo.mendez', 'Oficial de Credito', 'Rodrigo Javier Méndez', 'rodrigo.mendez@horizonteandino.test', '7481653', '72180637'],
                ['patricia.suarez', 'Encargado de Agencia', 'Patricia Elena Suárez', 'patricia.suarez@horizonteandino.test', '6719348', '74520861'],
            ]);
        }
        $plainPassword = $demo ? 'admin123' : config('app.bootstrap_admin_password');
        if (!$demo && !DB::table('users')->where('name', 'administrador')->exists()
            && (!is_string($plainPassword) || strlen($plainPassword) < 12)) {
            throw new \LogicException('Configure BOOTSTRAP_ADMIN_PASSWORD con al menos 12 caracteres para la población operativa.');
        }
        if (!$demo) {
            foreach (['personal', 'email', 'ci', 'telefono'] as $field) {
                if (!is_string($admin[$field] ?? null) || trim($admin[$field]) === '') {
                    throw new \LogicException("Configure SEED_ADMIN para el campo {$field}.");
                }
            }
        }
        $password = Hash::make($plainPassword ?? \Illuminate\Support\Str::random(48));
        foreach ($accounts as [$login, $role, $name, $email, $ci, $phone]) {
            // Do not reset an existing user's password on db:seed.
            if (DB::table('users')->where('name', $login)->exists()) continue;
            DB::table('users')->insert([
                'name' => $login, 'personal' => $name, 'email' => $email,
                'password' => $password, 'estado' => 1,
                'id_rol' => DB::table('rol')->where('nombre', $role)->value('id'),
                'ci' => $ci, 'telefono' => $phone,
                'fecha_cambio_password' => today()->toDateString(), 'dias_vigencia' => $demo ? 365 : 90,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }
}
