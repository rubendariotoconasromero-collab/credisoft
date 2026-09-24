<?php

namespace App\Http\Middleware;

use App\Services\CarteraAccess;
use Closure;
use Illuminate\Http\Request;

class AssignedPortfolio
{
    private const RECORDS = [
        'id_solicitud' => 'solicitud', 'id_solicitud_origen' => 'solicitud', 'id_reprogramacion' => 'solicitud',
        'id_plan_pago' => 'plan_pago', 'id_planpago' => 'plan_pago', 'id_plan_pago_principal' => 'plan_pago',
        'id_cuota' => 'cuota', 'id_pago' => 'pago', 'id_garantia' => 'garantia', 'id_respaldo' => 'respaldo',
    ];

    public function handle(Request $request, Closure $next)
    {
        if (CarteraAccess::restricted()) {
            $this->check(array_merge($request->all(), $request->route()->parameters()));
            if ($request->filled('plan_pago.id')) CarteraAccess::assertRecord('plan_pago', $request->input('plan_pago.id'));
            $action = class_basename($request->route()->getActionName());
            if ($request->filled('id_imagen')) {
                CarteraAccess::assertRecord(str_starts_with($action, 'RespaldoController@') ? 'imagenes_respaldo' : 'imagen', $request->id_imagen);
            }
            foreach (['garantias' => 'garantia', 'respaldos' => 'respaldo'] as $key => $table) {
                if (!$request->filled($key)) continue;
                $items = $this->decode($request->input($key));
                foreach ($items as $item) {
                    if (!empty($item['id'])) CarteraAccess::assertRecord($table, $item['id']);
                    foreach ($item['lista_imagenes'] ?? [] as $image) {
                        if (!empty($image['id_imagen'])) CarteraAccess::assertRecord($table === 'garantia' ? 'imagen' : 'imagenes_respaldo', $image['id_imagen']);
                    }
                }
            }
            foreach (['deletedGarantias' => 'garantia', 'deletedRespaldos' => 'respaldo', 'deletedImages' => str_starts_with($action, 'RespaldoController@') ? 'imagenes_respaldo' : 'imagen'] as $key => $table) {
                foreach ($this->decode($request->input($key, [])) as $id) CarteraAccess::assertRecord($table, $id);
            }
        }
        return $next($request);
    }

    private function decode($value): array
    {
        if (is_string($value)) $value = json_decode($value, true);
        abort_unless(is_array($value), 422, 'Datos de cartera inválidos.');
        return $value;
    }

    private function check(array $data): void
    {
        foreach ($data as $key => $value) {
            if (isset(self::RECORDS[$key]) && $value !== null && $value !== '' && $value !== 0 && $value !== '0') {
                CarteraAccess::assertRecord(self::RECORDS[$key], $value);
            } elseif (is_array($value)) {
                $this->check($value);
            } elseif (in_array($key, ['garantias', 'respaldos', 'solicitud', 'plan_pago', 'detalles'], true) && is_string($value)) {
                $this->check($this->decode($value));
            }
        }
    }
}
