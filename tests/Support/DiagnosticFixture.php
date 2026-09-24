<?php

namespace Tests\Support;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class DiagnosticFixture
{
    public static function seed(): array
    {
        $role = DB::table('rol')->insertGetId(['nombre' => 'Diagnostico caja', 'estado' => 1]);
        foreach (['controlcaja', 'boveda', 'cliente', 'codeudores'] as $name) {
            $id = DB::table('permiso')->where('nombre', $name)->value('id')
                ?? DB::table('permiso')->insertGetId(['nombre' => $name, 'descripcion' => $name, 'estado' => 1]);
            DB::table('permiso_rol')->insert(['id_permiso' => $id, 'id_rol' => $role]);
        }
        $readerRole = DB::table('rol')->insertGetId(['nombre' => 'Sin permisos', 'estado' => 1]);
        $users = [];
        foreach (['cajero_a', 'cajero_b', 'inhabilitado', 'sin_permisos'] as $name) {
            $users[$name] = User::create(['name' => 'diagnostico_'.$name, 'personal' => 'Persona ficticia',
                'password' => Hash::make('solo-diagnostico-ficticio'), 'estado' => $name === 'inhabilitado' ? 0 : 1,
                'id_rol' => $name === 'sin_permisos' ? $readerRole : $role]);
        }
        $person = ['nombre' => 'Cliente ficticio 1', 'fecha_nacimiento' => '1990-01-01',
            'ci' => 'TEST-0001', 'lugar_expedicion' => 'SC', 'sexo' => 'Masculino', 'estado_civil' => 'Soltero',
            'actividad' => 'Comercio', 'vivienda' => 'Propia', 'ingreso_mensual' => 3000];
        $client = DB::table('cliente')->insertGetId($person);
        DB::table('codeudor')->insert($person + ['tipo' => 'Personal']);
        $userId = $users['cajero_a']->id;
        $request = DB::table('solicitud')->insertGetId(['importe_solicitud' => 1000, 'moneda' => 'Bs',
            'lapso_capital' => 'Mensual', 'nro_cuotas' => 1, 'tasa' => 10,
            'fecha' => '2026-09-01', 'fecha_desembolso' => '2026-09-01', 'fecha_primera_cuota' => '2026-10-01',
            'destino_prestamo' => 'Prueba ficticia', 'tipo_garantia' => 'Personal', 'tipo_desembolso' => 'Efectivo',
            'id_cliente' => $client, 'id_usuario' => $userId]);
        $plan = DB::table('plan_pago')->insertGetId(['fecha_inicio' => '2026-09-01', 'fecha_fin' => '2026-10-01',
            'total_pagar' => 1100, 'id_solicitud' => $request, 'moneda' => 'Bs', 'lapso_capital' => 'Mensual',
            'nro_cuotas' => 1, 'tasa' => 10]);
        $installment = DB::table('cuota')->insertGetId(['numero' => 1, 'fecha' => '2026-10-01',
            'capital' => 1000, 'interes' => 100, 'saldo_capital' => 0, 'total' => 1100, 'id_plan_pago' => $plan]);
        $vault = DB::table('boveda')->insertGetId(['saldo_actual' => 900, 'fecha_apertura' => '2026-09-01', 'id_usuario' => $userId]);
        $cash = DB::table('caja')->insertGetId(['fechahora_apertura' => '2026-09-24 08:00:00',
            'monto_inicial' => 100, 'id_usuario' => $userId]);
        DB::table('movimientos_boveda')->insert([
            ['tipo_movimiento' => 'ingreso', 'monto' => 1000, 'descripcion' => 'Capital ficticio inicial',
                'fecha' => '2026-09-01', 'id_boveda' => $vault, 'id_usuario' => $userId],
            ['tipo_movimiento' => 'salida', 'monto' => 100, 'descripcion' => 'Apertura de caja (Transferencia a Caja)',
                'fecha' => '2026-09-24', 'id_boveda' => $vault, 'id_usuario' => $userId],
        ]);
        return compact('users', 'client', 'request', 'plan', 'installment', 'vault', 'cash');
    }
}
