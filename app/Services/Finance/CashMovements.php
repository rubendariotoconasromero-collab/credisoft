<?php

namespace App\Services\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\{Auth, DB};
use Symfony\Component\HttpFoundation\InputBag;

final class CashMovements
{
    public function saveAdministrativeFeeAndDisbursement(Request $request): array
    {
        $request->validate([
            'id_plan_pago' => 'required|integer',
            'monto_pago_adm' => 'required|numeric|min:0.01',
            'monto_desembolso' => 'required|numeric|min:0.01',
            'partes_pago_adm' => 'required|array|min:1|max:20',
            'partes_desembolso' => 'required|array|min:1|max:20',
        ]);

        return app(Operations::class)->run($request, 'administrativo_desembolso', function ($id) use ($request) {
            $key = DB::table('operaciones_financieras')->where('id', $id)->value('clave');
            return $this->writeAdministrativeFeeAndDisbursement($request, $id, $key);
        });
    }

    private function writeAdministrativeFeeAndDisbursement(Request $request, int $operationId, string $parentKey): array
    {
        $cash = Operations::cash($request->integer('id_caja'));
        // The parent stores only the result and cash ownership. Its children are
        // the financial sources; no direction/amount is recorded a second time.
        DB::table('operaciones_financieras')->where('id', $operationId)->update(['id_caja' => $cash->id]);
        $existingChildren = DB::table('operaciones_financieras')->where('id_usuario', Auth::id())
            ->whereIn('clave', [hash('sha256', $parentKey.'|administrativo'), hash('sha256', $parentKey.'|desembolso')])->count();
        abort_if($existingChildren === 1, 409, 'La operación conjunta está incompleta y requiere revisión.');
        $disbursementAmount = Money::cents($request->monto_desembolso);
        $disbursementParts = PaymentParts::prepare($request->partes_desembolso, $disbursementAmount);
        $summary = app(CashSummary::class)->forCash($cash->id);
        $available = Money::cents($summary['saldo_disponible']);
        $physicalAvailable = Money::cents($summary['efectivo_esperado']);
        $requiredCash = Money::cents($disbursementParts['efectivo']);

        if ($existingChildren === 0 && $available < $disbursementAmount) {
            throw new HttpResponseException(response()->json([
                'message' => 'Saldo disponible insuficiente en caja. No se registró el pago administrativo ni el desembolso.',
                'codigo' => 'SALDO_CAJA_INSUFICIENTE',
                'saldo_disponible' => Money::decimal($available),
                'monto_requerido' => Money::decimal($disbursementAmount),
                'faltante' => Money::decimal($disbursementAmount - $available),
            ], 422));
        }
        abort_if($existingChildren === 0 && $physicalAvailable < $requiredCash, 422, 'El saldo total es suficiente, pero no existe efectivo físico suficiente para el componente en efectivo del desembolso.');

        $administrativeData = [
            'id_plan_pago' => $request->id_plan_pago,
            'id_caja' => $cash->id,
            'monto' => $request->monto_pago_adm,
            'descripcion' => 'Pago administrativo',
            'partes' => $request->partes_pago_adm,
        ];
        $administrativeRequest = $request->duplicate(null, $administrativeData);
        $administrativeRequest->setJson(new InputBag($administrativeData));
        $administrativeRequest->headers->set('Idempotency-Key', hash('sha256', $parentKey.'|administrativo'));

        // Operations wraps both writes in one transaction and checks replay first.
        // Existing legacy children still validate their payload hashes in save().
        $administrative = $this->save($administrativeRequest, 'administrativo');

        $disbursementData = [
            'id_plan_pago' => $request->id_plan_pago,
            'id_caja' => $cash->id,
            'monto' => $request->monto_desembolso,
            'descripcion' => 'Desembolso de crédito',
            'partes' => $request->partes_desembolso,
        ];
        $disbursementRequest = $request->duplicate(null, $disbursementData);
        $disbursementRequest->setJson(new InputBag($disbursementData));
        $disbursementRequest->headers->set('Idempotency-Key', hash('sha256', $parentKey.'|desembolso'));

        $disbursement = $this->save($disbursementRequest, 'desembolso');

        return [
            'success' => true,
            'pago_administrativo' => $administrative,
            'desembolso' => $disbursement,
        ];
    }

    public function save(Request $request, string $type): array
    {
        $request->validate(['monto' => 'required|numeric|min:0.01', 'descripcion' => 'nullable|string|max:200', 'descripcion_otro' => 'nullable|string|max:50']);
        return app(Operations::class)->run($request, $type, function ($id) use ($request, $type) {
            $cash = Operations::cash($request->id_caja);
            $amount = Money::cents($request->monto);
            $parts = PaymentParts::prepare(PaymentParts::input($request, $request->monto), $amount);
            $direction = in_array($type, ['egreso', 'desembolso'], true) ? 'salida' : 'ingreso';
            if ($direction === 'salida') {
                $summary = app(CashSummary::class)->forCash($cash->id);
                abort_if(Money::cents($summary['saldo_disponible']) < $amount, 422, 'Saldo disponible insuficiente en caja.');
                abort_if(Money::cents($summary['efectivo_esperado']) < Money::cents($parts['efectivo']), 422, 'Saldo físico insuficiente para el componente en efectivo.');
            }
            $description = trim(($request->descripcion ?? $type).($request->descripcion_otro ? ' - '.$request->descripcion_otro : ''));
            $isTransfer = $type === 'egreso' && (str_contains(mb_strtolower($description), 'transferencia a bóveda') || str_contains(mb_strtolower($description), 'transferencia a boveda'));
            if ($type === 'ingreso') abort_if(str_contains(mb_strtolower($description), 'bóveda') || str_contains(mb_strtolower($description), 'boveda'), 422, 'Registre la transferencia desde el módulo de bóveda para crear ambas contrapartidas.');
            $extra = ['interno' => $isTransfer]; $row = ['id_operacion' => $id, 'id_caja' => $cash->id, 'id_usuario' => Auth::id(), 'fecha' => now(), 'monto' => $parts['monto']];
            if ($type !== 'desembolso') $row['descripcion'] = $description;
            $table = $type === 'administrativo' ? 'pago_administrativo' : $type;
            if (in_array($type, ['administrativo', 'desembolso'], true)) {
                $request->validate(['id_plan_pago' => 'required|integer']);
                Rescheduling::assertReady((int) $request->id_plan_pago);
                \App\Services\CarteraAccess::assertRecord('plan_pago', $request->id_plan_pago);
                $plan = DB::table('plan_pago')->where('id', $request->id_plan_pago)->lockForUpdate()->first();
                $credit = $plan ? DB::table('solicitud')->find($plan->id_solicitud) : null;
                abort_unless($plan && $credit && (int) $credit->estado === 2 && (int) $plan->estado === 1, 422, 'El crédito debe estar aprobado y activo.');
                abort_unless(in_array(mb_strtolower($plan->moneda), ['bs', 'bob', 'bolivianos'], true), 422, 'La caja opera en bolivianos; no se pueden mezclar monedas.');
                $flag = $type === 'desembolso' ? 'desembolso' : 'pago_administrativo';
                $expected = $type === 'desembolso'
                    ? Money::cents(AdministrativeFee::disbursementAmount($credit))
                    : AdministrativeFee::centsForCredit($credit);
                abort_unless((int) $plan->$flag === 1 && !DB::table($table)->where('id_plan_pago', $plan->id)->where('estado', 0)->exists(), 422, 'La operación ya fue registrada.');
                abort_unless($amount === $expected, 422, 'El importe debe coincidir con el crédito aprobado.');
                $extra['id_plan_pago'] = $row['id_plan_pago'] = $plan->id;
                $extra['antes'] = json_encode(['plan' => [$flag => $plan->$flag], 'solicitud' => ['desembolso' => $credit->desembolso]], JSON_THROW_ON_ERROR);
                DB::table('plan_pago')->where('id', $plan->id)->update([$flag => 0]);
                if ($type === 'administrativo') {
                    DB::table('solicitud')->where('id', $credit->id)->update([
                        'monto_pago_adm' => Money::decimal($expected),
                    ]);
                }
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
