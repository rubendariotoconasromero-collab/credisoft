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
        return SeedProfile::resolve() === 'demo';
    }

    public static function assertEmpty(): void
    {
        foreach (['cliente', 'codeudor', 'solicitud', 'plan_pago', 'caja', 'boveda', 'socios'] as $table) {
            if (DB::table($table)->exists()) {
                throw new LogicException('La población de escenarios requiere tablas de negocio vacías. Use migrate:fresh --seed únicamente en su BD de desarrollo; no se mezclaron datos.');
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
        $this->command?->info('Escenarios listos: 16 clientes, 8 codeudores, 13 solicitudes, 11 planes. Bóveda 38.157,00; caja A 2.160,00 disponibles (2.100,00 en efectivo); caja B 1.500,00 Bs. Ver docs/poblacion-desarrollo.md.');
    }

    private function mixedCollection(): void
    {
        $previous = \Illuminate\Support\Facades\Auth::user();
        try {
            $user = \App\Models\User::findOrFail($this->users['lucia.rivera']);
            \Illuminate\Support\Facades\Auth::setUser($user);
            $plan = DB::table('plan_pago')->join('solicitud', 'solicitud.id', '=', 'plan_pago.id_solicitud')
                ->join('cliente', 'cliente.id', '=', 'solicitud.id_cliente')->where('cliente.ci', '7294168')->value('plan_pago.id');
            $quota = DB::table('cuota')->where('id_plan_pago', $plan)->orderBy('numero')->value('id');
            $request = new \Illuminate\Http\Request([
                'id_caja' => DB::table('caja')->where('id_usuario', $user->id)->where('estado', 1)->value('id'),
                'id_plan_pago' => $plan, 'cuotas' => [['id_cuota' => $quota]],
                'fecha_pago' => $this->today->toDateString(), 'modalidad_pago' => 'parcial', 'monto_aplicado' => '100.00',
                'clave_operacion' => 'cobro-mixto-inicial-001',
                'partes' => [
                    ['forma_pago' => 'efectivo', 'monto' => '40.00'],
                    ['forma_pago' => 'deposito', 'monto' => '30.00', 'referencia' => 'DEP-'.$this->today->format('ymd').'-0184'],
                    ['forma_pago' => 'transferencia', 'monto' => '30.00', 'referencia' => 'QR-'.$this->today->format('ymd').'-0327'],
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
        $activities = DB::table('actividades')->orderBy('id')->pluck('nombre')->all();
        $people = [
            1 => ['Ana María Vargas Quispe', '7128043', 'Femenino'],
            2 => ['Bruno Alejandro Salazar Rocha', '6851327', 'Masculino'],
            3 => ['Carla Fernanda Ríos Mendoza', '7482036', 'Femenino'],
            4 => ['Diego Armando Cabrera López', '7016854', 'Masculino'],
            5 => ['Elena Patricia Guzmán Vera', '7294168', 'Femenino'],
            6 => ['Fabián Mauricio Herrera Soria', '6748901', 'Masculino'],
            7 => ['Gabriela Lucía Flores Arce', '7531069', 'Femenino'],
            8 => ['Hugo Daniel Rojas Méndez', '6982475', 'Masculino'],
            9 => ['Irene Valeria Choque Aguilar', '7163842', 'Femenino'],
            10 => ['Jorge Luis Molina Paredes', '7428150', 'Masculino'],
            11 => ['Karla Beatriz Sandoval Céspedes', '6835097', 'Femenino'],
            12 => ['Luis Fernando Suárez Velasco', '7316548', 'Masculino'],
            13 => ['Marta Isabel Quiroga Salinas', '7059283', 'Femenino'],
            14 => ['Nicolás Adrián Vaca Rivero', '7593186', 'Masculino'],
            15 => ['Olga Teresa Barrientos Cruz', '6917425', 'Femenino'],
            16 => ['Pablo Andrés Mendoza Ávila', '7258031', 'Masculino'],
        ];
        $clients = [];
        foreach ($people as $number => [$name, $ci, $sex]) {
            $data = $this->person($name, $ci, $activities[($number * 37) % count($activities)], $number, $sex);
            $clients[$number] = DB::table('cliente')->insertGetId($data + [
                'estado' => $number === 15 ? 0 : 1, 'created_at' => $this->today, 'updated_at' => $this->today,
            ]);
            $this->contact('id_cliente', $clients[$number], $number);
        }
        $guarantors = [
            ['Ricardo Alberto Peña Ortiz', '6572038', 'Masculino'],
            ['Verónica Isabel Arias Rojas', '7149062', 'Femenino'],
            ['Miguel Ángel Poma Castro', '6895174', 'Masculino'],
            ['Rosa Elena Méndez Peña', '7351806', 'Femenino'],
            ['César Augusto Terrazas Lima', '7084259', 'Masculino'],
            ['Daniela Paola Flores Vargas', '7620913', 'Femenino'],
            ['Fernando Javier Cuéllar Soto', '6817304', 'Masculino'],
            ['María Eugenia Roca Suárez', '7465821', 'Femenino'],
        ];
        foreach ($guarantors as $index => [$name, $ci, $sex]) {
            $number = $index + 1;
            $id = DB::table('codeudor')->insertGetId($this->person($name, $ci,
                $activities[(($number + 16) * 37) % count($activities)], $number + 16, $sex)
                + ['tipo' => $number <= 4 ? 'Garante' : 'Codeudor', 'estado' => 1]);
            $this->contact('id_codeudor', $id, $number + 16);
        }
        foreach ([
            ['Roberto Carlos', 'Salinas Gutiérrez', '7264813', 'roberto.salinas@horizonteandino.test'],
            ['María Teresa', 'Vargas Céspedes', '6942057', 'maria.vargas@horizonteandino.test'],
        ] as [$names, $lastNames, $ci, $email]) {
            DB::table('socios')->insert(['nombres' => $names, 'apellidos' => $lastNames,
                'ci' => $ci, 'email' => $email, 'estado' => 1,
                'created_at' => $this->today, 'updated_at' => $this->today]);
        }
        return $clients;
    }

    private function person(string $name, string $ci, string $activity, int $number, string $sex): array
    {
        return ['nombre' => $name, 'ci' => $ci,
            'fecha_nacimiento' => sprintf('%04d-%02d-%02d', 1968 + ($number * 3) % 28,
                1 + ($number * 5) % 12, 1 + ($number * 7) % 27),
            'lugar_expedicion' => 'SC', 'sexo' => $sex,
            'estado_civil' => $number % 3 === 0 ? 'soltero/a' : 'casado/a', 'actividad' => $activity,
            'vivienda' => $number % 4 === 0 ? 'Alquiler' : 'Casa propia',
            'ingreso_mensual' => 2400 + $number * 175, 'imagen' => null];
    }

    private function contact(string $owner, int $id, int $number): void
    {
        $streets = ['Calle Bolívar', 'Av. Circunvalación', 'Calle Sucre', 'Av. Barrientos',
            'Calle Warnes', 'Av. Guabirá', 'Calle Potosí', 'Av. 25 de Mayo'];
        $zones = ['Centro', 'Villa Busch', 'Norte', 'Santa Rosa', 'Los Ángeles', 'San José'];
        $references = ['Cerca de la plaza', 'Frente a la farmacia', 'A media cuadra del mercado',
            'Junto al centro de salud', 'Esquina con la avenida principal', 'Frente a la unidad educativa'];
        DB::table('direccion')->insert([$owner => $id, 'tipo' => 'Casa', 'departamento' => 'Santa Cruz',
            'ciudad' => 'Montero', 'zona' => $zones[($number - 1) % count($zones)],
            'descripcion' => $streets[($number - 1) % count($streets)].' N.º '.(100 + $number * 13),
            'referencia' => $references[($number - 1) % count($references)],
            'lat' => -17.338440 + ($number - 1) * 0.0001, 'lng' => -63.246961,
            'created_at' => $this->today, 'updated_at' => $this->today]);
        DB::table('telefono')->insert([$owner => $id, 'tipo' => 'Numero telefono',
            'numero' => sprintf('7%07d', 1300000 + $number * 3791), 'observacion' => null,
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
                'destino_prestamo' => 'Capital de trabajo para actividad comercial', 'tipo_garantia' => 'Garante Personal',
                'tipo_desembolso' => 'Efectivo', 'estado' => $number === 2 ? 1 : ($number === 3 ? 0 : 2),
                'id_cliente' => $clients[$number], 'id_usuario' => $this->users[$number % 2 ? 'camila.ortiz' : 'rodrigo.mendez'],
                'monto_pago_adm' => 10, 'desembolso' => $number >= 5 ? 1 : 0,
                'observacion' => $number === 3 ? 'Solicitud archivada por desistimiento del cliente' : '', 'tipo_solicitud' => 'Nuevo',
                'created_at' => $start, 'updated_at' => $start,
            ]);
            DB::table('solicitud_codeudor')->insert(['id_solicitud' => $request, 'id_codeudor' => $guarantors[($number - 2) % 8]]);
            DB::table('garantia')->insert(['id_solicitud' => $request, 'descripcion' => 'Garantía personal respaldada por codeudor',
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
            $this->event($start, 'pago_administrativo', 10, ['id_plan_pago' => $plan, 'descripcion' => 'Gastos administrativos de apertura']);
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
        $quota = DB::table('cuota')->find($cuota);
        $planBefore = DB::table('plan_pago')->find($plan);
        // Explicit fictional scenarios: payments are at/after maturity; 12 and 14
        // include seven days of moratory interest. Never backfill existing payments.
        $ordinary = (float) $quota->interes;
        $moratory = in_array($scenario, [11, 12, 14], true) ? 25 : 0;
        $detail = ['id_cuota' => $cuota, 'cuota' => $quota->numero,
            'interes_ordinario_generado' => number_format($ordinary, 2, '.', ''),
            'interes_moratorio_generado' => number_format($moratory, 2, '.', ''),
            'desglose_interes' => \App\Services\Finance\InterestBreakdown::allocate(
                (object) ['interes_devengado_neto' => $ordinary], $interest * 100, $waivedInterest * 100),
            'pagado' => number_format($capital + $interest + $mora, 2, '.', ''),
            'capital' => number_format($capital, 2, '.', ''), 'interes' => number_format($interest, 2, '.', ''),
            'mora' => number_format($mora, 2, '.', ''), 'condonado' => number_format($waivedInterest + $waivedMora, 2, '.', '')];
        $before = ['cuotas' => [['id' => $cuota, 'capital_pagado' => $quota->capital_pagado,
            'interes_pagado' => $quota->interes_pagado, 'mora_pagada' => $quota->mora_pagada, 'estado' => $quota->estado]],
            'plan' => ['saldo_pendiente' => $planBefore->saldo_pendiente, 'estado' => $planBefore->estado,
                'fecha_ultima_amortizacion' => $planBefore->fecha_ultima_amortizacion]];
        DB::table('cuota')->where('id', $cuota)->update(['capital_pagado' => $capital,
            'interes_pagado' => $interest + $waivedInterest, 'mora_pagada' => $mora + $waivedMora,
            'estado' => $complete ? 2 : 3, 'updated_at' => $date]);
        DB::table('plan_pago')->where('id', $plan)->decrement('saldo_pendiente', $capital);
        if ($complete) DB::table('plan_pago')->where('id', $plan)->update(['fecha_ultima_amortizacion' => $date]);
        $this->event($date, 'pago', $capital + $interest + $mora, [
            '_operation' => ['plan' => $plan, 'antes' => $before, 'detalle' => $detail],
            'id_cuota' => $cuota, 'codigo_transaccion' => 'RC-'.sprintf('%04d', $scenario).'-'.sprintf('%05d', $cuota),
            'pago_capital' => $capital, 'pago_interes' => $interest, 'pago_mora' => $mora,
            'monto_condonado' => $waivedInterest + $waivedMora, 'monto_condonado_interes' => $waivedInterest,
            'monto_condonado_mora' => $waivedMora, 'motivo_condonacion' => $waivedInterest + $waivedMora ? 'Acuerdo de regularización aprobado' : '',
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
        $this->vaultMovement('ingreso', 50000, 'Aporte inicial de capital', $initial, $this->users['administrador']);
        ksort($this->events);
        foreach ($this->events as $day => $events) {
            $date = CarbonImmutable::parse($day);
            $user = $this->users['lucia.rivera'];
            $cash = $this->openCash($date, $user, 5000);
            $income = 0; $expense = 0;
            foreach ($events as $event) {
                ['table' => $table, 'amount' => $amount, 'data' => $data] = $event;
                $operation = $data['_operation'] ?? null;
                unset($data['_operation']);
                $payload = $data + ['id_caja' => $cash, 'id_usuario' => $user];
                if ($operation) {
                    $parts = [['forma_pago' => 'efectivo', 'monto' => number_format($amount, 2, '.', ''),
                        'entregado' => number_format($amount, 2, '.', ''), 'referencia' => null]];
                    $payload['id_operacion'] = DB::table('operaciones_financieras')->insertGetId([
                        'codigo' => $data['codigo_transaccion'], 'clave' => $data['codigo_transaccion'],
                        'huella' => hash('sha256', $data['codigo_transaccion']), 'tipo' => 'cobro',
                        'id_usuario' => $user, 'id_caja' => $cash, 'id_plan_pago' => $operation['plan'],
                        'fecha_efectiva' => $date, 'direccion' => 'ingreso', 'monto' => $amount,
                        'partes' => json_encode($parts, JSON_THROW_ON_ERROR),
                        'antes' => json_encode($operation['antes'], JSON_THROW_ON_ERROR),
                        'resultado' => json_encode(['detalles' => [$operation['detalle']], 'fecha_pago' => $date->toDateString()], JSON_THROW_ON_ERROR),
                        'created_at' => $date->setTime(12, 0), 'updated_at' => $date->setTime(12, 0),
                    ]);
                }
                if ($table === 'pago') {
                    $payload += ['monto_pago' => $amount, 'fecha_pago' => $date, 'created_at' => $date->setTime(12, 0), 'updated_at' => $date->setTime(12, 0)];
                } else {
                    $payload += ['monto' => $amount, 'fecha' => $date->setTime(12, 0), 'estado' => 0];
                }
                DB::table($table)->insert($payload);
                if ($table === 'desembolso') $expense += $amount; else $income += $amount;
                $this->cashMovement($cash, $user, $table === 'desembolso' ? 'egreso' : 'ingreso', $amount,
                    ucfirst(str_replace('_', ' ', $table)).' '.($data['codigo_transaccion'] ?? 'crédito '.$data['id_plan_pago']), $date->setTime(12, 0));
            }
            $remaining = 5000 + $income - $expense;
            if ($remaining < 0) throw new LogicException('Caja histórica sin fondos.');
            $closing = $date->setTime(23, 0);
            DB::table('caja')->where('id', $cash)->update(['estado' => 0, 'fechahora_cierre' => $closing,
                'monto_final' => $remaining, 'efectivo_total' => $remaining, 'efectivo_venta' => $income,
                'efectivo_gasto' => $expense, 'total_ingreso' => $income, 'total_egreso' => $expense,
                'updated_at' => $closing]);
            $this->vaultCents += $remaining * 100;
            // Canonical transfer wording prevents counting the seeded return as external income.
            $this->vaultMovement('ingreso', $remaining, 'Transferencia desde Caja - Retorno de Caja al Cierre', $closing, $user);
            $this->cashMovement($cash, $user, 'egreso', $remaining, 'Retorno a Bóveda al Cierre', $closing);
            $this->transfer($cash, 'caja_a_boveda', $remaining, $closing);
        }
        $cashA = $this->openCash($this->today, $this->users['lucia.rivera'], 2000);
        $this->openCash($this->today, $this->users['andres.flores'], 1500);
        foreach ([['ingreso', 100, 'Ingreso extraordinario en efectivo'], ['egreso', 40, 'Gastos de papelería']] as [$table, $amount, $description]) {
            DB::table($table)->insert(['monto' => $amount, 'descripcion' => $description, 'estado' => 1,
                'fecha' => $this->today, 'id_usuario' => $this->users['lucia.rivera'], 'id_caja' => $cashA,
                'created_at' => $this->today, 'updated_at' => $this->today]);
            $this->cashMovement($cashA, $this->users['lucia.rivera'], $table, $amount, $description, $this->today);
        }
        DB::table('boveda')->where('id', $this->vault)->update(['saldo_actual' => $this->vaultCents / 100, 'updated_at' => $this->today]);
    }

    private function openCash(CarbonImmutable $date, int $user, int $amount): int
    {
        if ($this->vaultCents < $amount * 100) throw new LogicException('Bóveda sin fondos.');
        $id = DB::table('caja')->insertGetId(['fechahora_apertura' => $date, 'monto_inicial' => $amount,
            'id_usuario' => $user, 'estado' => 1, 'monto_final' => 0, 'efectivo_total' => 0, 'deposito_total' => 0,
            'efectivo_venta' => 0, 'deposito_venta' => 0, 'efectivo_gasto' => 0, 'deposito_gasto' => 0,
            'total_ingreso' => 0, 'total_egreso' => 0, 'diferencia' => 0, 'created_at' => $date, 'updated_at' => $date]);
        $this->vaultCents -= $amount * 100;
        $this->vaultMovement('salida', $amount, 'Apertura de caja (Transferencia a Caja)', $date, $user);
        $this->cashMovement($id, $user, 'ingreso', $amount, 'Apertura de caja', $date);
        $this->transfer($id, 'boveda_a_caja', $amount, $date);
        return $id;
    }

    private function transfer(int $cash, string $type, int $amount, CarbonImmutable $date): void
    {
        DB::table('transferencia_caja_boveda')->insert(['id_caja' => $cash, 'id_boveda' => $this->vault,
            'tipo_transferencia' => $type, 'monto' => $amount, 'descripcion' => 'Transferencia interna',
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
