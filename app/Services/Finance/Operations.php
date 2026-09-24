<?php

namespace App\Services\Finance;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};
use Illuminate\Support\Str;

final class Operations
{
    public function run(Request $request, string $type, callable $write): array
    {
        $key = $request->header('Idempotency-Key', $request->input('clave_operacion'));
        // Legacy callers get an identity; all current forms retain a key across retries.
        $key ??= (string) Str::uuid();
        abort_unless(is_string($key) && preg_match('/^[a-zA-Z0-9_-]{16,80}$/D', $key), 422, 'Clave de operación inválida.');
        $body = $request->except(['_token', 'clave_operacion']);
        $canonical = function ($value) use (&$canonical) {
            if (!is_array($value)) return $value;
            if (!array_is_list($value)) ksort($value);
            return array_map($canonical, $value);
        };
        $hash = hash('sha256', json_encode([$type, $canonical($body)], JSON_THROW_ON_ERROR));
        return DB::transaction(function () use ($key, $hash, $type, $write) {
            FinancialLock::acquire();
            $existing = DB::table('operaciones_financieras')->where('id_usuario', Auth::id())->where('clave', $key)->first();
            if ($existing) {
                abort_unless(hash_equals($existing->huella, $hash), 409, 'La clave de reintento corresponde a otros datos.');
                abort_unless($existing->resultado, 409, 'La operación aún no terminó.');
                return json_decode($existing->resultado, true, 512, JSON_THROW_ON_ERROR);
            }
            $code = (string) Str::uuid();
            $id = DB::table('operaciones_financieras')->insertGetId(['codigo' => $code, 'id_usuario' => Auth::id(), 'clave' => $key,
                'huella' => $hash, 'tipo' => $type, 'fecha_efectiva' => today()->toDateString(), 'created_at' => now(), 'updated_at' => now()]);
            $result = $write($id, $code) + ['id_operacion' => $id, 'codigo_transaccion' => $code];
            DB::table('operaciones_financieras')->where('id', $id)->update(['resultado' => json_encode($result, JSON_THROW_ON_ERROR)]);
            return $result;
        });
    }

    public static function cash(?int $id = null): object
    {
        $query = DB::table('caja');
        $cash = $id ? $query->where('id', $id)->first() : $query->where('id_usuario', Auth::id())->where('estado', 1)->first();
        abort_unless($cash, 422, 'Debe abrir su propia caja.');
        abort_unless((int) $cash->id_usuario === (int) Auth::id(), 403, 'Solo el propietario opera su caja.');
        abort_unless((int) $cash->estado === 1, 422, 'La caja está cerrada.');
        return $cash;
    }

    public static function record(int $id, int $cash, string $direction, array $parts, array $extra = []): void
    {
        DB::table('operaciones_financieras')->where('id', $id)->update($extra + ['id_caja' => $cash, 'direccion' => $direction,
            'monto' => $parts['monto'], 'cambio' => $parts['cambio'], 'partes' => json_encode($parts['partes'], JSON_THROW_ON_ERROR)]);
    }
}
