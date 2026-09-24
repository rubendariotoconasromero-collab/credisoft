<?php

namespace Database\Seeders;

use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use LogicException;

/** Deterministic manual scenarios. No production controller is invoked. */
class DevelopmentSeeder extends Seeder
{
    private CarbonImmutable $today;
    private array $users;
    private array $events = [];
    private int $vault;
    private int $vaultCents = 5000000;

    public static function enabled(): bool
    {
        return app()->environment(['local', 'testing']) && config('app.seed_demo_data');
    }

    public static function assertEmpty(): void
    {
        foreach (['cliente', 'codeudor', 'solicitud', 'plan_pago', 'caja', 'boveda', 'socios'] as $table) {
            if (DB::table($table)->exists()) {
                throw new LogicException('La población DEMO requiere tablas de negocio vacías. Use migrate:fresh --seed únicamente en su BD de desarrollo; no se mezclaron datos.');
            }
        }
    }

    public function run(): void
    {
        if (!self::enabled()) return;
        self::assertEmpty();
        DB::beginTransaction();
        try {
            $this->today = CarbonImmutable::instance(today());
            $this->users = DB::table('users')->pluck('id', 'name')->all();
            $this->events = [];
            $this->vaultCents = 5000000;
            $people = $this->people();
            $this->credits($people);
            $this->cashHistory();
            $this->mixedCollection();
            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }
        $this->command?->info('DEMO lista: 16 clientes, 8 codeudores, 13 solicitudes, 11 planes. Bóveda 38.257,00; caja A 2.100,00; caja B 1.500,00 Bs. Ver docs/poblacion-desarrollo.md.');
    }

    private function mixedCollection(): void
    {
        $previous = \Illuminate\Support\Facades\Auth::user();
        try {
            $user = \App\Models\User::findOrFail($this->users['cajero_user']);
            \Illuminate\Support\Facades\Auth::setUser($user);
            $plan = DB::table('plan_pago')->join('solicitud', 'solicitud.id', '=', 'plan_pago.id_solicitud')
                ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')->where('cliente.ci', 'DEMO-C005')->value('plan_pago.id');
            $quota = DB::table('cuota')->where('id_plan_pago', $plan)->orderBy('numero')->value('id');
            $request = new \Illuminate\Http\Request([
                'id_caja' => DB::table('caja')->where('id_usuario', $user->id)->where('estado', 1)->value('id'),
                'id_plan_pago' => $plan, 'cuotas' => [['id_cuota' => $quota]],
                'fecha_pago' => $this->today->toDateString(), 'modalidad_pago' => 'parcial', 'monto_aplicado' => '100.00',
                'clave_operacion' => 'demo-cobro-mixto-001',
                'partes' => [
                    ['forma_pago' => 'efectivo', 'monto' => '40.00'],
                    ['forma_pago' => 'deposito', 'monto' => '30.00', 'referencia' => 'DEMO-DEP-001'],
                    ['forma_pago' => 'transferencia', 'monto' => '30.00', 'referencia' => 'DEMO-QR-001'],
                ],
            ]);
            app(\App\Services\Finance\Collections::class)->collect($request);
            // The identical retry must preserve one operation and one collection.
            app(\App\Services\Finance\Collections::class)->collect($request);
        } finally {
            if ($previous) \Illuminate\Support\Facades\Auth::setUser($previous);
            else \Illuminate\Support\Facades\Auth::forgetGuards();
        }
    }

    private function people(): array
    {
        $activity = DB::table('actividades')->orderBy('id')->value('nombre');
        $labels = [1 => 'Ana - Nuevo cliente', 'Bruno - Solicitud pendiente', 'Carla - Solicitud inactiva',
            'Diego - Por desembolsar', 'Elena - Vigente semanal', 'Fabian - Vigente quincenal',
            'Gabriela - Vigente mensual', 'Hugo - Credito vencido', 'Irene - Pago parcial',
            'Jorge - Solo interes', 'Karla - Solo mora', 'Luis - Interes y mora',
            'Marta - Credito cancelado', 'Nicolas - Condonacion', 'Olga - Cliente inactivo', 'Pablo - Sin credito'];
        $clients = [];
        foreach ($labels as $number => $label) {
            $data = $this->person('DEMO '.sprintf('%02d', $number).' - '.$label, 'DEMO-C'.sprintf('%03d', $number), $activity);
            $data['sexo'] = in_array($number, [1, 3, 5, 7, 9, 11, 13, 15]) ? 'Femenino' : 'Masculino';
            $clients[$number] = DB::table('cliente')->insertGetId($data + [
                'estado' => $number === 15 ? 0 : 1, 'created_at' => $this->today, 'updated_at' => $this->today,
            ]);
            $this->contact('id_cliente', $clients[$number], $number);
        }
        for ($number = 1; $number <= 8; $number++) {
            $id = DB::table('codeudor')->insertGetId($this->person('DEMO '.($number <= 4 ? 'Garante ' : 'Codeudor ').sprintf('%02d', $number),
                'DEMO-G'.sprintf('%03d', $number), $activity) + ['tipo' => $number <= 4 ? 'Garante' : 'Codeudor', 'estado' => 1]);
            $this->contact('id_codeudor', $id, $number + 16);
        }
        foreach ([1, 2] as $number) {
            DB::table('socios')->insert(['nombres' => 'Socio DEMO '.$number, 'apellidos' => 'Pruebas',
                'ci' => 'DEMO-S00'.$number, 'email' => 'socio'.$number.'@example.test', 'estado' => 1,
                'created_at' => $this->today, 'updated_at' => $this->today]);
        }
        return $clients;
    }

    private function person(string $name, string $ci, string $activity): array
    {
        return ['nombre' => $name, 'ci' => $ci, 'fecha_nacimiento' => '1990-05-15', 'lugar_expedicion' => 'SC',
            'sexo' => 'Masculino', 'estado_civil' => 'soltero/a', 'actividad' => $activity,
            'vivienda' => 'Casa propia', 'ingreso_mensual' => 4000, 'imagen' => null];
    }

    private function contact(string $owner, int $id, int $number): void
    {
        DB::table('direccion')->insert([$owner => $id, 'tipo' => 'Casa', 'departamento' => 'Santa Cruz',
            'ciudad' => 'Montero', 'zona' => 'DEMO', 'descripcion' => 'Dirección ficticia '.$number.' para pruebas',
            'referencia' => 'No corresponde a una persona real',
            'lat' => -17.338440 + ($number - 1) * 0.0001, 'lng' => -63.246961,
            'created_at' => $this->today, 'updated_at' => $this->today]);
        DB::table('telefono')->insert([$owner => $id, 'tipo' => 'Numero telefono',
            'numero' => sprintf('0000%04d', $number), 'observacion' => 'Número ficticio no operativo',
            'created_at' => $this->today, 'updated_at' => $this->today]);
    }

    private function credits(array $clients): void
    {
        $guarantors = DB::table('codeudor')->orderBy('id')->pluck('id')->all();
        for ($number = 2; $number <= 14; $number++) {
            $step = $number === 6 ? 15 : ($number === 7 ? 30 : 7);
            $term = $number === 13 ? 2 : 4;
            $startDays = [2 => 0, 3 => 0, 4 => 0, 5 => -3, 6 => -5, 7 => -10, 8 => -35,
                9 => -14, 10 => -14, 11 => -15, 12 => -15, 13 => -21, 14 => -15][$number];
            $start = $this->today->addDays($startDays);
            $interval = [7 => 'Semanal', 15 => 'Quincenal', 30 => 'Mensual'][$step];
            $request = DB::table('solicitud')->insertGetId([
                'importe_solicitud' => 1000, 'moneda' => 'Bolivianos', 'lapso_capital' => $interval,
                'nro_cuotas' => $term, 'tasa' => 10, 'tipo_tasa' => 'fija', 'fecha' => $start,
                'fecha_desembolso' => $start, 'fecha_primera_cuota' => $start->addDays($step),
                'destino_prestamo' => 'Capital de trabajo ficticio', 'tipo_garantia' => 'Garante Personal',
                'tipo_desembolso' => 'Efectivo', 'estado' => $number === 2 ? 1 : ($number === 3 ? 0 : 2),
                'id_cliente' => $clients[$number], 'id_usuario' => $this->users[$number % 2 ? 'oficial_user' : 'oficial2_user'],
                'monto_pago_adm' => 20, 'desembolso' => $number >= 5 ? 1 : 0,
                'observacion' => $number === 3 ? 'Solicitud inactiva ficticia DEMO' : '', 'tipo_solicitud' => 'Nuevo',
                'created_at' => $start, 'updated_at' => $start,
            ]);
            DB::table('solicitud_codeudor')->insert(['id_solicitud' => $request, 'id_codeudor' => $guarantors[($number - 2) % 8]]);
            DB::table('garantia')->insert(['id_solicitud' => $request, 'descripcion' => 'Garantía personal ficticia DEMO',
                'created_at' => $start, 'updated_at' => $start]);
            if ($number < 4) continue;
            $plan = DB::table('plan_pago')->insertGetId([
                'id_solicitud' => $request, 'fecha_inicio' => $start, 'fecha_fin' => $start->addDays($step * $term),
                'fecha_registro' => $start, 'total_pagar' => 1000, 'saldo_pendiente' => 1000,
                'moneda' => 'Bolivianos', 'lapso_capital' => $interval, 'tasa' => 10, 'nro_cuotas' => $term,
                'estado' => 1, 'desembolso' => $number === 4 ? 1 : 0, 'pago_administrativo' => $number === 4 ? 1 : 0,
                'created_at' => $start, 'updated_at' => $start,
            ]);
            $cuotas = [];
            $interest = [7 => 25, 15 => 50, 30 => 100][$step];
            for ($index = 1; $index <= $term; $index++) {
                $cuotas[$index] = DB::table('cuota')->insertGetId([
                    'id_plan_pago' => $plan, 'numero' => $index, 'fecha' => $start->addDays($index * $step),
                    'capital' => 1000 / $term, 'interes' => $interest, 'saldo_capital' => 1000 - $index * (1000 / $term),
                    'ahorro' => 0, 'seguro' => 0, 'total' => 1000 / $term + $interest,
                    'estado' => 1, 'created_at' => $start, 'updated_at' => $start,
                ]);
            }
            if ($number === 4) continue;
            $this->event($start, 'desembolso', 1000, ['id_plan_pago' => $plan]);
            $this->event($start, 'pago_administrativo', 20, ['id_plan_pago' => $plan, 'descripcion' => 'Gastos administrativos DEMO']);
            if ($number === 9) $this->payment($number, $plan, $cuotas[1], $start->addDays(7), 100, 25, 0, 'parcial');
            if ($number === 10) $this->payment($number, $plan, $cuotas[1], $start->addDays(7), 0, 25, 0, 'solo_interes');
            if ($number === 11) $this->payment($number, $plan, $cuotas[1], $this->today->subDay(), 0, 0, 6, 'solo_mora');
            if ($number === 12) $this->payment($number, $plan, $cuotas[1], $this->today->subDay(), 0, 50, 21, 'interes_mora');
            if ($number === 13) {
                foreach ($cuotas as $index => $id) $this->payment($number, $plan, $id, $start->addDays(7 * $index), 500, 25, 0, 'completo', true);
                DB::table('plan_pago')->where('id', $plan)->update(['estado' => 2]);
            }
            if ($number === 14) $this->payment($number, $plan, $cuotas[1], $this->today->subDay(), 250, 30, 0, 'completo', true, 20, 21);
        }
    }

    private function payment(int $scenario, int $plan, int $cuota, CarbonImmutable $date, int $capital, int $interest,
        int $mora, string $type, bool $complete = false, int $waivedInterest = 0, int $waivedMora = 0): void
    {
        DB::table('cuota')->where('id', $cuota)->update(['capital_pagado' => $capital,
            'interes_pagado' => $interest + $waivedInterest, 'mora_pagada' => $mora + $waivedMora,
            'estado' => $complete ? 2 : 3, 'updated_at' => $date]);
        DB::table('plan_pago')->where('id', $plan)->decrement('saldo_pendiente', $capital);
        if ($complete) DB::table('plan_pago')->where('id', $plan)->update(['fecha_ultima_amortizacion' => $date]);
        $this->event($date, 'pago', $capital + $interest + $mora, [
            'id_cuota' => $cuota, 'codigo_transaccion' => 'DEMO-'.sprintf('%02d', $scenario).'-C'.$cuota,
            'pago_capital' => $capital, 'pago_interes' => $interest, 'pago_mora' => $mora,
            'monto_condonado' => $waivedInterest + $waivedMora, 'monto_condonado_interes' => $waivedInterest,
            'monto_condonado_mora' => $waivedMora, 'motivo_condonacion' => $waivedInterest + $waivedMora ? 'Condonación ficticia DEMO' : '',
            'forma_pago' => 'efectivo', 'tipo_pago' => $type, 'estado' => 1,
        ]);
    }

    private function event(CarbonImmutable $date, string $table, int $amount, array $data): void
    {
        $this->events[$date->toDateString()][] = compact('table', 'amount', 'data');
    }

    private function cashHistory(): void
    {
        $initial = $this->today->subDays(60);
        $this->vault = DB::table('boveda')->insertGetId(['saldo_actual' => 50000, 'fecha_apertura' => $initial,
            'id_usuario' => $this->users['administrador'], 'created_at' => $initial, 'updated_at' => $initial]);
        $this->vaultMovement('ingreso', 50000, 'Aporte de capital DEMO', $initial, $this->users['administrador']);
        ksort($this->events);
        foreach ($this->events as $day => $events) {
            $date = CarbonImmutable::parse($day);
            $user = $this->users['cajero_user'];
            $cash = $this->openCash($date, $user, 5000);
            $income = 0; $expense = 0;
            foreach ($events as $event) {
                ['table' => $table, 'amount' => $amount, 'data' => $data] = $event;
                $payload = $data + ['id_caja' => $cash, 'id_usuario' => $user];
                if ($table === 'pago') {
                    $payload += ['monto_pago' => $amount, 'fecha_pago' => $date, 'created_at' => $date->setTime(12, 0), 'updated_at' => $date->setTime(12, 0)];
                } else {
                    $payload += ['monto' => $amount, 'fecha' => $date->setTime(12, 0), 'estado' => 0];
                }
                DB::table($table)->insert($payload);
                if ($table === 'desembolso') $expense += $amount; else $income += $amount;
                $this->cashMovement($cash, $user, $table === 'desembolso' ? 'egreso' : 'ingreso', $amount,
                    'DEMO '.$table.' '.($data['codigo_transaccion'] ?? 'plan '.$data['id_plan_pago']), $date->setTime(12, 0));
            }
            $remaining = 5000 + $income - $expense;
            if ($remaining < 0) throw new LogicException('Caja histórica DEMO sin fondos.');
            $closing = $date->setTime(23, 0);
            DB::table('caja')->where('id', $cash)->update(['estado' => 0, 'fechahora_cierre' => $closing,
                'monto_final' => $remaining, 'efectivo_total' => $remaining, 'efectivo_venta' => $income,
                'efectivo_gasto' => $expense, 'total_ingreso' => $income, 'total_egreso' => $expense,
                'updated_at' => $closing]);
            $this->vaultCents += $remaining * 100;
            // Canonical transfer wording prevents counting the seeded return as external income.
            $this->vaultMovement('ingreso', $remaining, 'Transferencia desde Caja - Retorno de Caja al Cierre DEMO', $closing, $user);
            $this->cashMovement($cash, $user, 'egreso', $remaining, 'Retorno a Bóveda al Cierre DEMO', $closing);
            $this->transfer($cash, 'caja_a_boveda', $remaining, $closing);
        }
        $cashA = $this->openCash($this->today, $this->users['cajero_user'], 2000);
        $this->openCash($this->today, $this->users['cajero2_user'], 1500);
        foreach ([['ingreso', 100, 'Otro ingreso en efectivo DEMO'], ['egreso', 40, 'Gastos de papelería DEMO']] as [$table, $amount, $description]) {
            DB::table($table)->insert(['monto' => $amount, 'descripcion' => $description, 'estado' => 1,
                'fecha' => $this->today, 'id_usuario' => $this->users['cajero_user'], 'id_caja' => $cashA,
                'created_at' => $this->today, 'updated_at' => $this->today]);
            $this->cashMovement($cashA, $this->users['cajero_user'], $table, $amount, $description, $this->today);
        }
        DB::table('boveda')->where('id', $this->vault)->update(['saldo_actual' => $this->vaultCents / 100, 'updated_at' => $this->today]);
    }

    private function openCash(CarbonImmutable $date, int $user, int $amount): int
    {
        if ($this->vaultCents < $amount * 100) throw new LogicException('Bóveda DEMO sin fondos.');
        $id = DB::table('caja')->insertGetId(['fechahora_apertura' => $date, 'monto_inicial' => $amount,
            'id_usuario' => $user, 'estado' => 1, 'monto_final' => 0, 'efectivo_total' => 0, 'deposito_total' => 0,
            'efectivo_venta' => 0, 'deposito_venta' => 0, 'efectivo_gasto' => 0, 'deposito_gasto' => 0,
            'total_ingreso' => 0, 'total_egreso' => 0, 'diferencia' => 0, 'created_at' => $date, 'updated_at' => $date]);
        $this->vaultCents -= $amount * 100;
        $this->vaultMovement('salida', $amount, 'Apertura de caja (Transferencia a Caja) DEMO', $date, $user);
        $this->cashMovement($id, $user, 'ingreso', $amount, 'Apertura de caja DEMO', $date);
        $this->transfer($id, 'boveda_a_caja', $amount, $date);
        return $id;
    }

    private function transfer(int $cash, string $type, int $amount, CarbonImmutable $date): void
    {
        DB::table('transferencia_caja_boveda')->insert(['id_caja' => $cash, 'id_boveda' => $this->vault,
            'tipo_transferencia' => $type, 'monto' => $amount, 'descripcion' => 'Transferencia interna DEMO',
            'fecha' => $date, 'created_at' => $date, 'updated_at' => $date]);
    }

    private function vaultMovement(string $type, int $amount, string $description, CarbonImmutable $date, int $user): void
    {
        DB::table('movimientos_boveda')->insert(['id_boveda' => $this->vault, 'id_usuario' => $user,
            'tipo_movimiento' => $type, 'monto' => $amount, 'descripcion' => $description,
            'fecha' => $date, 'created_at' => $date, 'updated_at' => $date]);
    }

    private function cashMovement(int $cash, int $user, string $type, int $amount, string $description, CarbonImmutable $date): void
    {
        DB::table('movimientos_caja')->insert(['id_caja' => $cash, 'id_usuario' => $user,
            'tipo_movimiento' => $type, 'monto' => $amount, 'descripcion' => $description,
            'fecha' => $date, 'created_at' => $date, 'updated_at' => $date]);
    }
}
