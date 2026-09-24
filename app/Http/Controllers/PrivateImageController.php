<?php

namespace App\Http\Controllers;

use App\Services\CarteraAccess;
use Illuminate\Http\Request;

class PrivateImageController extends Controller
{
    public function show(Request $request, string $category, string $filename)
    {
        $map = [
            'cliente' => [['cliente'], ['cliente', 'solicitudprestamos', 'planpagos', 'controlcaja', 'reportes']],
            'codeudor' => [['codeudor'], ['codeudores', 'solicitudprestamos', 'planpagos']],
            'pago' => [['pago'], ['listadopagos', 'controlcaja']],
            'garantia' => [['imagen'], ['solicitudprestamos', 'planpagos']],
            'garantias' => [['imagen'], ['solicitudprestamos', 'planpagos']],
            'respaldos' => [['respaldo', 'imagenes_respaldo'], ['solicitudprestamos', 'planpagos']],
        ];
        abort_unless(isset($map[$category]) && preg_match('/\A[a-zA-Z0-9_.-]+\z/', $filename) && !str_contains($filename, '..'), 404);
        [$tables, $permissions] = $map[$category];
        abort_if($request->user()->permisosActivos()->intersect($permissions)->isEmpty(), 403);
        $found = false;
        foreach ($tables as $table) {
            if (CarteraAccess::query($table)->whereIn('imagen', [$filename, $category.'/'.$filename, 'img/'.$category.'/'.$filename])->exists()) {
                $found = true;
                break;
            }
        }
        abort_unless($found || $filename === 'default.png', 404);
        $path = \App\Services\PrivateImages::path($category, $filename);
        abort_unless(is_file($path) && !is_link($path), 404);
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);
        abort_unless(in_array($mime, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], true), 404);
        return response()->file($path, [
            'Content-Type' => $mime, 'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store, max-age=0',
        ]);
    }
}
