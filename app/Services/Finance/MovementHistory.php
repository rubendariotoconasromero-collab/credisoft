<?php

namespace App\Services\Finance;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

final class MovementHistory
{
    public function list(Request $request, string $table, bool $openOnly = false): array
    {
        abort_unless(in_array($table, ['ingreso', 'egreso'], true), 404);
        $request->validate([
            'fecha_inicio' => 'required|date_format:Y-m-d',
            'fecha_final' => 'required|date_format:Y-m-d|after_or_equal:fecha_inicio',
            'criterio' => ['nullable', Rule::in(['users.name', 'users.personal', $table.'.descripcion'])],
            'buscar' => 'nullable|string|max:200', 'page' => 'nullable|integer|min:1',
        ]);
        $query = DB::table($table)
            ->join('users', $table.'.id_usuario', '=', 'users.id')
            ->join('caja', 'caja.id', '=', $table.'.id_caja')
            ->leftJoin('operaciones_financieras as op', 'op.id', '=', $table.'.id_operacion')
            ->whereDate($table.'.fecha', '>=', $request->fecha_inicio)
            ->whereDate($table.'.fecha', '<=', $request->fecha_final)
            ->where($request->input('criterio') ?: 'users.name', 'like', '%'.$request->input('buscar', '').'%');
        if ($openOnly) $query->where('caja.estado', 1);
        $total = (clone $query)->where($table.'.estado', 1)->whereNull('op.anulado_at')->sum($table.'.monto');
        $amountAlias = $table === 'ingreso' ? 'monto_ingreso' : 'monto_gasto';
        $rows = $query->select('users.name as asesor', 'users.personal', $table.'.id', $table.'.fecha',
            $table.'.descripcion', $table.'.estado', $table.'.monto as '.$amountAlias,
            $table.'.id_caja', 'caja.id_usuario as propietario_caja', 'caja.estado as estado_caja',
            'op.codigo as codigo_operacion', 'op.tipo as tipo_operacion', 'op.anulado_at')
            ->orderByDesc($table.'.id')->paginate(50);
        $listKey = $table === 'ingreso' ? 'ingresos' : ($openOnly ? 'egresos' : 'gastos');
        $totalKey = $table === 'ingreso' ? 'totalIngresos' : ($openOnly ? 'totalEgresos' : 'totalGastos');
        return [$listKey => $rows, $totalKey => Money::decimal(Money::cents($total))];
    }
}
