<?php

namespace App\Console\Commands;

use App\Services\Finance\CashSummary;
use App\Services\Finance\PaymentMethod;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DiagnoseFinance extends Command
{
    protected $signature = 'finanzas:diagnosticar';
    protected $description = 'Diagnóstico financiero de solo lectura, sin reparar ni modificar movimientos';

    public function handle(CashSummary $summary): int
    {
        // A transaction gives a consistent read on MySQL's default REPEATABLE READ isolation.
        $result = DB::transaction(function () use ($summary) {
            return [
                'solo_lectura' => true,
                'diferencias_operaciones' => $this->operationDifferences(),
                'cajas_abiertas_duplicadas' => DB::table('caja')->where('estado', 1)->select('id_usuario')
                    ->selectRaw('COUNT(*) AS cantidad')->groupBy('id_usuario')->havingRaw('COUNT(*) > 1')->get(),
                'bovedas' => DB::table('boveda')->select('id', 'saldo_actual')->get(),
                'desembolsos_activos_duplicados' => DB::table('desembolso')->where('estado', 0)->select('id_plan_pago')
                    ->selectRaw('COUNT(*) AS cantidad')->groupBy('id_plan_pago')->havingRaw('COUNT(*) > 1')->get(),
                'formas_pago_historicas' => DB::table('pago')->select('forma_pago')->selectRaw('COUNT(*) AS cantidad')
                    ->groupBy('forma_pago')->get()->map(fn ($row) => (array) $row + ['clasificacion' => PaymentMethod::historical($row->forma_pago)]),
                'cajas_abiertas' => DB::table('caja')->where('estado', 1)->pluck('id')->map(fn ($id) => $summary->forCash($id)),
                'amortizaciones_legadas_por_revisar' => DB::table('pago_amortizacion')->count(),
            ];
        });
        $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        return self::SUCCESS;
    }
    private function operationDifferences(): array
    {
        $issues = [];
        foreach (DB::table('operaciones_financieras')->whereNotNull('direccion')->cursor() as $op) {
            $parts = json_decode($op->partes ?? '[]', true, 512, JSON_THROW_ON_ERROR);
            $sum = array_sum(array_map(fn ($p) => \App\Services\Finance\Money::cents($p['monto']), $parts));
            if ($sum !== \App\Services\Finance\Money::cents($op->monto)) $issues[] = ['operacion' => $op->codigo, 'error' => 'Desglose distinto del total'];
            $table = match ($op->tipo) { 'cobro' => 'pago', 'administrativo' => 'pago_administrativo', 'transferencia_caja' => 'ingreso', default => $op->tipo };
            if (!in_array($table, ['pago', 'pago_administrativo', 'ingreso', 'egreso', 'desembolso'], true)) continue;
            $field = $table === 'pago' ? 'monto_pago' : 'monto';
            $rows = DB::table($table)->where('id_operacion', $op->id)->get();
            $source = $rows->sum(fn ($r) => \App\Services\Finance\Money::cents($r->$field));
            if ($source !== $sum) $issues[] = ['operacion' => $op->codigo, 'error' => 'Importe fuente distinto del desglose'];
            $active = in_array($table, ['pago_administrativo', 'desembolso'], true) ? 0 : 1;
            foreach ($rows as $row) if (((int) $row->estado === $active) !== ($op->anulado_at === null)) {
                $issues[] = ['operacion' => $op->codigo, 'error' => 'Estado fuente incompatible con la anulación']; break;
            }
        }
        return $issues;
    }

}
