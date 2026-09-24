<?php

namespace App\Services\Finance;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};

final class CashMovements
{
    public function save(Request $request, string $type): array
    {
        $request->validate(['monto' => 'required|numeric|min:0.01', 'descripcion' => 'nullable|string|max:200', 'descripcion_otro' => 'nullable|string|max:50']);
        return app(Operations::class)->run($request, $type, function ($id) use ($request, $type) {
            $cash = Operations::cash($request->id_caja);
            $amount = Money::cents($request->monto);
            $parts = PaymentParts::prepare(PaymentParts::input($request, $request->monto), $amount);
            $direction = in_array($type, ['egreso', 'desembolso'], true) ? 'salida' : 'ingreso';
            if ($direction === 'salida') abort_if(Money::cents(app(CashSummary::class)->forCash($cash->id)['efectivo_esperado']) < Money::cents($parts['efectivo']), 422, 'Saldo físico insuficiente en caja.');
            $description = trim(($request->descripcion ?? $type).($request->descripcion_otro ? ' - '.$request->descripcion_otro : ''));
            $isTransfer = $type === 'egreso' && (str_contains(mb_strtolower($description), 'transferencia a bóveda') || str_contains(mb_strtolower($description), 'transferencia a boveda'));
            if ($type === 'ingreso') abort_if(str_contains(mb_strtolower($description), 'bóveda') || str_contains(mb_strtolower($description), 'boveda'), 422, 'Registre la transferencia desde el módulo de bóveda para crear ambas contrapartidas.');
            $extra = ['interno' => $isTransfer]; $row = ['id_operacion' => $id, 'id_caja' => $cash->id, 'id_usuario' => Auth::id(), 'fecha' => now(), 'monto' => $parts['monto']];
            if ($type !== 'desembolso') $row['descripcion'] = $description;
            $table = $type === 'administrativo' ? 'pago_administrativo' : $type;
            if (in_array($type, ['administrativo', 'desembolso'], true)) {
                $request->validate(['id_plan_pago' => 'required|integer']);
                \App\Services\CarteraAccess::assertRecord('plan_pago', $request->id_plan_pago);
                $plan = DB::table('plan_pago')->where('id', $request->id_plan_pago)->lockForUpdate()->first();
                $credit = $plan ? DB::table('solicitud')->find($plan->id_solicitud) : null;
                abort_unless($plan && $credit && (int) $credit->estado === 2 && (int) $plan->estado === 1, 422, 'El crédito debe estar aprobado y activo.');
                abort_unless(in_array(mb_strtolower($plan->moneda), ['bs', 'bob', 'bolivianos'], true), 422, 'La caja opera en bolivianos; no se pueden mezclar monedas.');
                $flag = $type === 'desembolso' ? 'desembolso' : 'pago_administrativo';
                $expected = Money::cents($type === 'desembolso' ? $credit->importe_solicitud : $credit->monto_pago_adm);
                abort_unless((int) $plan->$flag === 1 && !DB::table($table)->where('id_plan_pago', $plan->id)->where('estado', 0)->exists(), 422, 'La operación ya fue registrada.');
                abort_unless($amount === $expected, 422, 'El importe debe coincidir con el crédito aprobado.');
                $extra['id_plan_pago'] = $row['id_plan_pago'] = $plan->id;
                $extra['antes'] = json_encode(['plan' => [$flag => $plan->$flag], 'solicitud' => ['desembolso' => $credit->desembolso]], JSON_THROW_ON_ERROR);
                DB::table('plan_pago')->where('id', $plan->id)->update([$flag => 0]);
                if ($type === 'desembolso') DB::table('solicitud')->where('id', $credit->id)->update(['desembolso' => 1]);
            }
            if ($isTransfer) {
                abort_unless(Money::cents($parts['efectivo']) === $amount, 422, 'La transferencia física a bóveda solo admite efectivo.');
                $vault = DB::table('boveda')->lockForUpdate()->first();
                abort_unless($vault, 422, 'No existe bóveda.');
                $extra['id_boveda'] = $vault->id;
                DB::table('boveda')->where('id', $vault->id)->update(['saldo_actual' => Money::decimal(Money::cents($vault->saldo_actual) + $amount)]);
                DB::table('movimientos_boveda')->insert(['id_operacion' => $id, 'id_boveda' => $vault->id, 'id_usuario' => Auth::id(), 'tipo_movimiento' => 'ingreso', 'monto' => $parts['monto'], 'descripcion' => 'Transferencia desde Caja', 'fecha' => now()]);
            }
            $source = DB::table($table)->insertGetId($row);
            Operations::record($id, $cash->id, $direction, $parts, $extra);
            DB::table('movimientos_caja')->insert(['id_operacion' => $id, 'id_caja' => $cash->id, 'id_usuario' => Auth::id(), 'tipo_movimiento' => $direction, 'monto' => $parts['monto'], 'descripcion' => $description, 'fecha' => now()]);
            return $parts + ['success' => true, 'id_registro' => $source];
        });
    }
}
