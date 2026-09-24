<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [['administrador', 'Administrador', 'Administrador DEMO']];
        if (DevelopmentSeeder::enabled()) {
            $accounts = array_merge($accounts, [
                ['cajero_user', 'Cajero', 'Cajero A DEMO'],
                ['cajero2_user', 'Cajero', 'Cajero B DEMO'],
                ['oficial_user', 'Oficial de Credito', 'Oficial A DEMO'],
                ['oficial2_user', 'Oficial de Credito', 'Oficial B DEMO'],
                ['encargado_user', 'Encargado de Agencia', 'Encargado DEMO'],
            ]);
        }
        $password = Hash::make('admin123'); // Existing bootstrap credential, documented for development.
        foreach ($accounts as $index => [$login, $role, $name]) {
            // Do not reset an existing user's password on db:seed.
            if (DB::table('users')->where('name', $login)->exists()) continue;
            DB::table('users')->insert([
                'name' => $login, 'personal' => $name, 'email' => $login.'@example.test',
                'password' => $password, 'estado' => 1,
                'id_rol' => DB::table('rol')->where('nombre', $role)->value('id'),
                'ci' => 'DEMO-U'.($index + 1), 'telefono' => '0000000'.($index + 1),
                'fecha_cambio_password' => today()->toDateString(), 'dias_vigencia' => 365,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }
}
