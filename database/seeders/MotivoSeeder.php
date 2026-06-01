<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MotivoSeeder extends Seeder
{
    public function run(): void
    {
        // ══════════════════════════════════════════════════════════════════════
        // MOTIVOS DE INGRESO
        // Usados en: frmBoveda (tipo=boveda) y módulo de ingresos extra (tipo=caja)
        // ══════════════════════════════════════════════════════════════════════
        $motivosIngreso = [

            // ── BÓVEDA ────────────────────────────────────────────────────────
            // Ingresos que aumentan el capital central de la institución
            ['nombre' => 'Aporte de capital',              'tipo' => 'boveda', 'estado' => 1],
            ['nombre' => 'Recuperación de préstamo',       'tipo' => 'boveda', 'estado' => 1],
            ['nombre' => 'Transferencia desde Caja',       'tipo' => 'boveda', 'estado' => 1], // transferencia interna (neta cero en flujo)
            ['nombre' => 'Rendimientos financieros',       'tipo' => 'boveda', 'estado' => 1],
            ['nombre' => 'Cobro de cartera vencida',       'tipo' => 'boveda', 'estado' => 1], // recuperación de créditos en mora prolongada
            ['nombre' => 'Devolución de préstamo anticipado', 'tipo' => 'boveda', 'estado' => 1],
            ['nombre' => 'Ingresos por inversiones',       'tipo' => 'boveda', 'estado' => 1],
            ['nombre' => 'otro',                           'tipo' => 'boveda', 'estado' => 1],

            // ── CAJA ──────────────────────────────────────────────────────────
            // Ingresos extras que registra el cajero durante el turno
            // (los cobros de cuotas se registran directamente desde el módulo de pagos)
            ['nombre' => 'Transferencia desde Bóveda',      'tipo' => 'caja', 'estado' => 1], // apertura / refuerzo de caja
            ['nombre' => 'Cobro de gastos administrativos', 'tipo' => 'caja', 'estado' => 1], // comisión de desembolso cobrada en caja
            ['nombre' => 'Cobro de interés ordinario',      'tipo' => 'caja', 'estado' => 1], // interés cobrado manualmente
            ['nombre' => 'Cobro de interés moratorio',      'tipo' => 'caja', 'estado' => 1], // mora cobrada manualmente
            ['nombre' => 'Ingreso por multa',               'tipo' => 'caja', 'estado' => 1], // multa por incumplimiento de contrato
            ['nombre' => 'Depósito bancario recibido',      'tipo' => 'caja', 'estado' => 1], // cliente deposita en banco y entrega comprobante
            ['nombre' => 'Recuperación de crédito vencido', 'tipo' => 'caja', 'estado' => 1], // cobro extrajudicial
            ['nombre' => 'Pago de cuota atrasada',          'tipo' => 'caja', 'estado' => 1],
            ['nombre' => 'Cobro de seguro de desgravamen',  'tipo' => 'caja', 'estado' => 1],
            ['nombre' => 'otro',                            'tipo' => 'caja', 'estado' => 1],
        ];

        foreach ($motivosIngreso as $motivo) {
            DB::table('motivo_ingreso')->updateOrInsert(
                ['nombre' => $motivo['nombre'], 'tipo' => $motivo['tipo']],
                ['estado' => $motivo['estado'], 'created_at' => now(), 'updated_at' => now()]
            );
        }

        // ══════════════════════════════════════════════════════════════════════
        // MOTIVOS DE GASTO
        // Usados en: frmBoveda (tipo=boveda) y módulo de egresos extra (tipo=caja)
        // ══════════════════════════════════════════════════════════════════════
        $motivosGasto = [

            // ── BÓVEDA ────────────────────────────────────────────────────────
            // Egresos que reducen el capital central de la institución
            ['nombre' => 'Pago de dividendos',             'tipo' => 'boveda', 'estado' => 1], // utilidades a socios
            ['nombre' => 'Transferencia a Caja',           'tipo' => 'boveda', 'estado' => 1], // apertura / refuerzo de caja (neta cero en flujo)
            ['nombre' => 'Gastos de operación central',    'tipo' => 'boveda', 'estado' => 1], // gastos generales de la institución
            ['nombre' => 'Pago de planilla',               'tipo' => 'boveda', 'estado' => 1], // sueldos y salarios del personal
            ['nombre' => 'Pago de impuestos y tasas',      'tipo' => 'boveda', 'estado' => 1], // impuestos nacionales, municipales, etc.
            ['nombre' => 'Capitalización',                 'tipo' => 'boveda', 'estado' => 1], // reinversión de utilidades
            ['nombre' => 'Compra de activos',              'tipo' => 'boveda', 'estado' => 1], // equipos, mobiliario, vehículos
            ['nombre' => 'Pago de honorarios profesionales', 'tipo' => 'boveda', 'estado' => 1], // abogados, contadores, auditores
            ['nombre' => 'Pago de préstamo institucional', 'tipo' => 'boveda', 'estado' => 1], // devolución a entidad financiera externa
            ['nombre' => 'otro',                           'tipo' => 'boveda', 'estado' => 1],

            // ── CAJA ──────────────────────────────────────────────────────────
            // Egresos extras que registra el cajero durante el turno
            // (los desembolsos de créditos se registran desde el módulo de planes de pago)
            ['nombre' => 'Transferencia a Bóveda',         'tipo' => 'caja', 'estado' => 1], // envío de recaudación a bóveda al cierre
            ['nombre' => 'Pago de servicios básicos',      'tipo' => 'caja', 'estado' => 1], // electricidad, agua, internet, teléfono
            ['nombre' => 'Pago de alquiler',               'tipo' => 'caja', 'estado' => 1], // arriendo de local/oficina
            ['nombre' => 'Gastos de papelería',            'tipo' => 'caja', 'estado' => 1], // hojas, tóner, formularios, etc.
            ['nombre' => 'Compra de suministros de oficina','tipo' => 'caja', 'estado' => 1],
            ['nombre' => 'Gastos de refrigerio',           'tipo' => 'caja', 'estado' => 1], // alimentación del personal en turno
            ['nombre' => 'Pago de transporte y movilidad', 'tipo' => 'caja', 'estado' => 1], // visitas a clientes, cobranza externa
            ['nombre' => 'Gastos de mantenimiento',        'tipo' => 'caja', 'estado' => 1], // reparaciones menores de equipo o instalaciones
            ['nombre' => 'Pago de publicidad',             'tipo' => 'caja', 'estado' => 1], // volantes, redes sociales, avisos
            ['nombre' => 'Gasto administrativo',           'tipo' => 'caja', 'estado' => 1], // gestión interna no categorizada
            ['nombre' => 'Devolución a cliente',           'tipo' => 'caja', 'estado' => 1], // reintegro por cobro en exceso
            ['nombre' => 'otro',                           'tipo' => 'caja', 'estado' => 1],
        ];

        foreach ($motivosGasto as $motivo) {
            DB::table('motivo_gasto')->updateOrInsert(
                ['nombre' => $motivo['nombre'], 'tipo' => $motivo['tipo']],
                ['estado' => $motivo['estado'], 'created_at' => now(), 'updated_at' => now()]
            );
        }

        $this->command->info('MotivoSeeder: motivos de ingreso y gasto poblados correctamente.');
    }
}
