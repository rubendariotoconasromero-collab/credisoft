<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttachmentOwnership
{
    public function handle(Request $request, Closure $next)
    {
        $action = class_basename($request->route()->getActionName());
        $batch = match ($action) {
            'SolicitudController@guardarGarantiasImagenes' => ['garantias', 'garantia', 'imagen', 'id_garantia'],
            'RespaldoController@guardarRespaldosImagenes' => ['respaldos', 'respaldo', 'imagenes_respaldo', 'id_respaldo'],
            default => null,
        };
        if ($batch) {
            [$key, $table, $images, $foreignKey] = $batch;
            $items = json_decode($request->input($key, ''), true);
            abort_unless(is_array($items) && count($items) > 0, 422, 'Debe indicar los adjuntos.');
            foreach ($items as $item) {
                abort_unless(is_array($item) && isset($item['id_solicitud']) && DB::table('solicitud')->where('id', $item['id_solicitud'])->exists(), 422, 'Solicitud inválida.');
                if (!empty($item['id'])) {
                    abort_unless(DB::table($table)->where('id', $item['id'])->where('id_solicitud', $item['id_solicitud'])->exists(), 422, 'El adjunto no pertenece a la solicitud.');
                }
                foreach ($item['lista_imagenes'] ?? [] as $image) {
                    if (!empty($image['id_imagen'])) {
                        abort_unless(!empty($item['id']) && DB::table($images)->where('id', $image['id_imagen'])->where($foreignKey, $item['id'])->exists(), 422, 'La imagen no pertenece al adjunto.');
                    }
                }
            }
        }
        return $next($request);
    }
}
