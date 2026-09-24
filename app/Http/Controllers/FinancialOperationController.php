<?php
namespace App\Http\Controllers;

use App\Services\CarteraAccess;
use App\Services\Finance\Reversals;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinancialOperationController extends Controller
{
    public function operation(string $code): object
    {
        $op = DB::table('operaciones_financieras')->where('codigo', $code)->first();
        abort_unless($op, 404);
        if ($op->id_plan_pago) CarteraAccess::assertRecord('plan_pago', $op->id_plan_pago);
        elseif (CarteraAccess::restricted()) abort_unless((int) $op->id_usuario === (int) auth()->id(), 403);
        $op->partes = json_decode($op->partes ?? '[]', true, 512, JSON_THROW_ON_ERROR);
        $op->resultado = json_decode($op->resultado ?? '{}', true, 512, JSON_THROW_ON_ERROR);
        return $op;
    }

    public function detail(string $codigo)
    {
        $op = $this->operation($codigo);
        $cash = $op->id_caja ? DB::table('caja')->find($op->id_caja) : null;
        $canAttach = $cash && (int) $cash->estado === 1 && (int) $cash->id_usuario === (int) auth()->id()
            && auth()->user()->tienePermiso('controlcaja') && !$op->anulado_at;
        return response()->json([
            'codigo' => $op->codigo, 'tipo' => $op->tipo, 'monto' => $op->monto,
            'cambio' => $op->cambio, 'fecha' => $op->fecha_efectiva, 'anulado_at' => $op->anulado_at,
            'partes' => array_map(fn ($part, $index) => [
                'indice' => $index, 'forma_pago' => $part['forma_pago'], 'monto' => $part['monto'],
                'referencia' => $part['referencia'] ?? null,
                'comprobante_url' => !empty($part['imagen']) ? url('/operaciones/'.$op->codigo.'/comprobante/'.$index) : null,
                'puede_adjuntar' => $canAttach && in_array($part['forma_pago'], ['deposito', 'transferencia'], true) && empty($part['imagen']),
            ], $op->partes, array_keys($op->partes)),
        ]);
    }

    public function receipt(string $codigo)
    {
        $op = $this->operation($codigo);
        $user = DB::table('users')->where('id', $op->id_usuario)->value('personal');
        return \Barryvdh\DomPDF\Facade\Pdf::loadView('reporte.operacion_financiera', ['op' => $op, 'usuario' => $user])->stream('operacion-'.$codigo.'.pdf');
    }

    public function reverse(Request $request)
    {
        $request->validate(['codigo' => 'required|string']);
        if (!DB::table('operaciones_financieras')->where('codigo', $request->codigo)->exists()) {
            $query = \App\Models\Pago::query();
            if (preg_match('/^historico-(\d+)$/', $request->codigo, $match)) $query->where('id', $match[1])->whereNull('codigo_transaccion');
            else $query->where('codigo_transaccion', $request->codigo);
            $source = $query->firstOrFail();
            return app(Reversals::class)->reverseSource($request, 'pago', $source->id);
        }
        if (!DB::table('operaciones_financieras')->where('codigo', $request->codigo)->exists()) {
            $query = \App\Models\Pago::query();
            if (preg_match('/^historico-(\d+)$/', $request->codigo, $match)) $query->where('id', $match[1])->whereNull('codigo_transaccion');
            else $query->where('codigo_transaccion', $request->codigo);
            $source = $query->firstOrFail();
            return app(Reversals::class)->reverseSource($request, 'pago', $source->id);
        }
        $op = $this->operation($request->codigo);
        return app(Reversals::class)->reverse($request, $op->id);
    }

    public function attach(Request $request, string $codigo)
    {
        $request->validate(['indice' => 'required|integer|min:0|max:19', 'imagen' => 'required|file|image|mimes:jpg,jpeg,png,gif|max:2048|dimensions:max_width=6000,max_height=6000']);
        $path = null;
        try {
            return DB::transaction(function () use ($request, $codigo, &$path) {
                \App\Services\Finance\FinancialLock::acquire();
                $op = $this->operation($codigo);
                \App\Services\Finance\Operations::cash($op->id_caja);
                abort_if($op->anulado_at, 422, 'La operación está anulada.');
                $index = (int) $request->indice;
                abort_unless(isset($op->partes[$index]) && empty($op->partes[$index]['imagen']), 422, 'Componente inexistente o con comprobante ya adjunto.');
                abort_unless(in_array($op->partes[$index]['forma_pago'], ['deposito', 'transferencia'], true), 422, 'Solo depósitos y transferencias/QR admiten comprobantes bancarios.');
                $name = 'operacion-'.\Illuminate\Support\Str::uuid().'.'.$request->file('imagen')->extension();
                $path = \App\Services\PrivateImages::path('pago', $name);
                abort_unless($request->file('imagen')->storeAs('pago', $name, 'expedientes'), 500, 'No se pudo guardar el comprobante.');
                $op->partes[$index]['imagen'] = $name;
                DB::table('operaciones_financieras')->where('id', $op->id)->update(['partes' => json_encode($op->partes, JSON_THROW_ON_ERROR)]);
                return ['success' => true];
            });
        } catch (\Throwable $e) {
            if ($path && is_file($path)) unlink($path);
            throw $e;
        }
    }

    public function proof(string $codigo, int $indice)
    {
        $op = $this->operation($codigo);
        abort_unless(isset($op->partes[$indice]['imagen']), 404);
        $path = \App\Services\PrivateImages::path('pago', $op->partes[$indice]['imagen']);
        abort_unless(is_file($path), 404);
        return response()->file($path, ['X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store']);
    }
}
