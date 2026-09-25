<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OwnCashBox
{
    public function handle(Request $request, Closure $next)
    {
        $action = class_basename($request->route()->getActionName());
        if (in_array($action, ['PagoController@save', 'PagoController@pagarCuotas'], true)) {
            $planId = $request->input('id_plan_pago');
            $ids = $request->input('cuotas', []);
            abort_unless(is_array($ids), 422, 'Cuotas inválidas.');
            $ids = array_column($ids, 'id_cuota');
            if ($request->filled('id_cuota')) $ids[] = $request->id_cuota;
            if ($ids) {
                $request->validate(['id_plan_pago' => 'required|integer|exists:plan_pago,id']);
                abort_unless(count(array_unique($ids)) === count($ids)
                    && DB::table('cuota')->where('id_plan_pago', $planId)->whereIn('id', $ids)->count() === count($ids), 422, 'Las cuotas no pertenecen al plan indicado.');
            }
        }
        $operations = [
            'IngresoController@save', 'GastoController@save', 'PagoController@save',
            'PagoController@pagarCuotas', 'CajaController@closeCaja',
            'CajaController@actualizarDesembolso', 'CajaController@registrarDesembolso',
            'CajaController@guardarPagoAdministrativo', 'CajaController@registrarPagoAdministrativoYDesembolso',
            'PlanPagoController@anularPlanPago', 'PlanPagoController@saveAmortizacion',
            'PlanPagoController@saveAmortizacionNuevo', 'PlanPagoController@liquidarDeuda',
        ];
        $records = [
            'IngresoController@anularIngreso' => ['ingreso', 'id'],
            'GastoController@anularGasto' => ['egreso', 'id'],
            'PagoController@anularPago' => ['pago', 'id_pago'],
            'PagoController@imagenRespaldo' => ['pago', 'id_pago'],
            'CajaController@anularDesembolso' => ['desembolso', 'id'],
            'HistorialPagosController@anular' => ['pago', 'id'],
        ];
        if ($action === 'BovedaController@retirarBoveda' && str_contains(mb_strtolower((string) $request->input('descripcion')), 'transferencia a caja')) {
            $operations[] = $action;
        }
        if (!$request->isMethodSafe() && (in_array($action, $operations, true) || isset($records[$action]))) {
            abort_unless($request->user()->tienePermiso('controlcaja'), 403, 'La operación requiere permiso de caja.');
            $cashId = $request->input('id_caja');
            if ($action === 'PlanPagoController@anularPlanPago') {
                $disbursement = DB::table('desembolso')->where('id_plan_pago', $request->input('id_planpago'))->orderByDesc('id')->first();
                if ($disbursement) $cashId = $disbursement->id_caja;
            }
            if (isset($records[$action])) {
                [$table, $field] = $records[$action];
                $record = DB::table($table)->where('id', $request->route($field) ?? $request->input($field))->first();
                abort_unless($record, 404);
                abort_if($cashId && (int) $cashId !== (int) $record->id_caja, 422, 'La operación no pertenece a esa caja.');
                $cashId = $record->id_caja;
                if ($action === 'PagoController@anularPago') {
                    $quota = DB::table('cuota')->where('id', $record->id_cuota)->first();
                    abort_unless($quota, 422, 'Cuota inválida.');
                    $request->merge(['id_cuota' => $quota->id, 'id_plan_pago' => $quota->id_plan_pago]);
                }
            }
            $query = DB::table('caja');
            $cash = $cashId ? $query->where('id', $cashId)->first()
                : $query->where('id_usuario', $request->user()->id)->where('estado', 1)->first();
            abort_unless($cash, 422, 'Debe abrir su propia caja para realizar esta operación.');
            abort_unless((int) $cash->id_usuario === (int) $request->user()->id, 403, 'Solo el propietario puede operar su caja.');
            $retryKey = $request->header('Idempotency-Key', $request->input('clave_operacion'));
            $retry = $retryKey && DB::table('operaciones_financieras')->where('id_usuario', $request->user()->id)->where('clave', $retryKey)->where('id_caja', $cash->id)->exists();
            abort_unless((int) $cash->estado === 1 || $retry, 422, 'La caja está cerrada.');
            $request->merge(['id_caja' => $cash->id]);
            $level = DB::transactionLevel();
            DB::beginTransaction();
            try {
                \App\Services\Finance\FinancialLock::acquire();
                $locked = DB::table('caja')->where('id', $cash->id)->lockForUpdate()->first();
                abort_unless($locked && (int) $locked->id_usuario === (int) $request->user()->id, 403);
                abort_unless((int) $locked->estado === 1 || $retry, 422, 'La caja está cerrada.');
                $response = $next($request);
                if ($response->getStatusCode() >= 400) {
                    DB::rollBack($level);
                } else {
                    if (DB::transactionLevel() !== $level + 1) {
                        throw new \LogicException('La operación dejó una transacción incompleta.');
                    }
                    DB::commit();
                }
                return $response;
            } catch (\Throwable $exception) {
                DB::rollBack($level);
                throw $exception;
            }
        }
        return $next($request);
    }
}
