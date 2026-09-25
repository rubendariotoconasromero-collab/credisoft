<?php
namespace App\Services\Finance;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};

final class CancelPlan
{
    public function cancel(Request $request): array
    {
        abort_unless(Auth::user()->tienePermiso('anular_finanzas'), 403, 'Se requiere permiso de anulación.');
        $request->validate(['id_planpago' => 'required|integer', 'motivo' => 'required|string|min:3|max:1000']);
        return app(Operations::class)->run($request, 'anular_plan', function ($op) use ($request) {
            $plan = DB::table('plan_pago')->find($request->id_planpago);
            abort_if(DB::table('reprogramaciones_financieras')->where('id_plan_nuevo', $request->id_planpago)->where('estado', 'aprobada')->exists(),
                422, 'Cancele esta reprogramación desde Solicitudes para restablecer el crédito original.');
            abort_unless($plan && (int) $plan->estado === 1, 422, 'El plan no está activo.');
            $cash = Operations::cash($request->id_caja);
            $ids = DB::table('cuota')->where('id_plan_pago', $plan->id)->pluck('id');
            abort_if(DB::table('pago')->whereIn('id_cuota', $ids)->where('estado', 1)->exists()
                || DB::table('pago_amortizacion')->where('id_plan_pago', $plan->id)->exists(), 422, 'El crédito tiene pagos. No se puede anular el plan.');
            $sources = collect();
            foreach (['desembolso', 'pago_administrativo'] as $table) {
                foreach (DB::table($table)->where('id_plan_pago', $plan->id)->where('estado', 0)->get() as $source) {
                    abort_unless((int) $source->id_caja === (int) $cash->id, 422, 'Todas las operaciones deben pertenecer a su caja abierta.');
                    $sources->push(['table' => $table, 'source' => $source]);
                }
            }
            foreach ($sources->sortByDesc(fn ($s) => $s['source']->id_operacion ?? 0) as $item) {
                $nested = new Request(['motivo' => $request->motivo, 'clave_operacion' => 'anular-plan-'.$op.'-'.$item['table'].'-'.$item['source']->id]);
                app(Reversals::class)->reverseSource($nested, $item['table'], $item['source']->id);
            }
            DB::table('plan_pago')->where('id', $plan->id)->update(['estado' => 0]);
            DB::table('solicitud')->where('id', $plan->id_solicitud)->update(['estado' => 0]);
            DB::table('cuota')->where('id_plan_pago', $plan->id)->update(['estado' => 0]);
            DB::table('operaciones_financieras')->where('id', $op)->update(['id_caja' => $cash->id, 'id_plan_pago' => $plan->id,
                'antes' => json_encode(['motivo' => $request->motivo, 'plan' => $plan], JSON_THROW_ON_ERROR)]);
            return ['success' => true];
        });
    }
}
