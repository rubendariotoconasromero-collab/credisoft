<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CarteraAccess
{
    public static function restricted(): bool
    {
        return Auth::check() && Auth::user()->rol()->where('nombre', 'Oficial de Credito')->exists();
    }

    public static function solicitudes()
    {
        return DB::table('solicitud')->where('solicitud.id_usuario', Auth::id())->select('solicitud.id');
    }

    public static function query(string $table)
    {
        $query = DB::table($table);
        if (!self::restricted()) return $query;
        $plans = DB::table('plan_pago')->whereIn('id_solicitud', self::solicitudes())->select('id');
        return match ($table) {
            'solicitud' => $query->where('solicitud.id_usuario', Auth::id()),
            'plan_pago', 'garantia', 'respaldo' => $query->whereIn($table.'.id_solicitud', self::solicitudes()),
            'cuota', 'pago_amortizacion', 'desembolso' => $query->whereIn($table.'.id_plan_pago', $plans),
            'pago' => $query->whereIn('pago.id_cuota', DB::table('cuota')->whereIn('id_plan_pago', $plans)->select('id')),
            'imagen' => $query->whereIn('imagen.id_garantia', DB::table('garantia')->whereIn('id_solicitud', self::solicitudes())->select('id')),
            'imagenes_respaldo' => $query->whereIn('imagenes_respaldo.id_respaldo', DB::table('respaldo')->whereIn('id_solicitud', self::solicitudes())->select('id')),
            default => $query,
        };
    }

    public static function assertRecord(string $table, $id): void
    {
        abort_unless(is_scalar($id) && ctype_digit((string) $id), 422, 'Identificador inválido.');
        abort_unless(self::query($table)->where($table.'.id', $id)->exists(), 403, 'El registro no pertenece a su cartera asignada.');
    }
}
