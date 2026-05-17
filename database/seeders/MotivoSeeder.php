<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MotivoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Motivos de Ingreso
        $motivosIngreso = [
            // Boveda
            ['nombre' => 'Aporte de capital', 'tipo' => 'boveda', 'estado' => 1],
            ['nombre' => 'Recuperación de préstamo', 'tipo' => 'boveda', 'estado' => 1],
            ['nombre' => 'Transferencia desde Caja', 'tipo' => 'boveda', 'estado' => 1],
            ['nombre' => 'Rendimientos financieros', 'tipo' => 'boveda', 'estado' => 1],
            ['nombre' => 'otro', 'tipo' => 'boveda', 'estado' => 1],
            
            // Caja
            ['nombre' => 'Cobro de cuota', 'tipo' => 'caja', 'estado' => 1],
            ['nombre' => 'Desembolso revertido', 'tipo' => 'caja', 'estado' => 1],
            ['nombre' => 'Transferencia desde Bóveda', 'tipo' => 'caja', 'estado' => 1],
            ['nombre' => 'Ingreso por mora', 'tipo' => 'caja', 'estado' => 1],
            ['nombre' => 'otro', 'tipo' => 'caja', 'estado' => 1],
        ];

        foreach ($motivosIngreso as $motivo) {
            DB::table('motivo_ingreso')->updateOrInsert(
                ['nombre' => $motivo['nombre'], 'tipo' => $motivo['tipo']],
                ['estado' => $motivo['estado'], 'created_at' => now(), 'updated_at' => now()]
            );
        }

        // Motivos de Gasto
        $motivosGasto = [
            // Boveda
            ['nombre' => 'Pago de dividendos', 'tipo' => 'boveda', 'estado' => 1],
            ['nombre' => 'Transferencia a Caja', 'tipo' => 'boveda', 'estado' => 1],
            ['nombre' => 'Gastos de operación central', 'tipo' => 'boveda', 'estado' => 1],
            ['nombre' => 'Capitalización', 'tipo' => 'boveda', 'estado' => 1],
            ['nombre' => 'otro', 'tipo' => 'boveda', 'estado' => 1],

            // Caja
            ['nombre' => 'Desembolso de préstamo', 'tipo' => 'caja', 'estado' => 1],
            ['nombre' => 'Gasto administrativo', 'tipo' => 'caja', 'estado' => 1],
            ['nombre' => 'Transferencia a Bóveda', 'tipo' => 'caja', 'estado' => 1],
            ['nombre' => 'Pago de servicios', 'tipo' => 'caja', 'estado' => 1],
            ['nombre' => 'otro', 'tipo' => 'caja', 'estado' => 1],
        ];

        foreach ($motivosGasto as $motivo) {
            DB::table('motivo_gasto')->updateOrInsert(
                ['nombre' => $motivo['nombre'], 'tipo' => $motivo['tipo']],
                ['estado' => $motivo['estado'], 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
