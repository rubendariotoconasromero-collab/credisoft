<?php

namespace App\Console\Commands;

use App\Services\PrivateImages;
use Database\Seeders\SeedProfile;
use Illuminate\Console\Command;

class CheckSecurityConfiguration extends Command
{
    protected $signature = 'seguridad:verificar {--production : Exigir configuración de producción}';
    protected $description = 'Verifica configuración y exposición de archivos; no cambia datos ni muestra secretos.';

    public function handle(): int
    {
        $publicFiles = 0;
        foreach (PrivateImages::CATEGORIES as $category) {
            $dir = public_path('img/'.$category);
            if (!is_dir($dir)) continue;
            if (is_link($dir)) { $publicFiles++; continue; }
            foreach (new \DirectoryIterator($dir) as $file) {
                if (!$file->isDot() && $file->getFilename() !== 'default.png') $publicFiles++;
            }
        }
        $root = str_replace('\\', '/', config('filesystems.disks.expedientes.root'));
        $public = str_replace('\\', '/', public_path()).'/';
        $checks = [
            'Clave de aplicación configurada' => (bool) config('app.key'),
            'Expedientes fuera de public' => !str_starts_with(strtolower($root).'/', strtolower($public)) && !is_link($root),
            'Sin expedientes históricos públicos' => $publicFiles === 0,
            'Cookies HttpOnly' => (bool) config('session.http_only'),
            'Cookies SameSite' => in_array(config('session.same_site'), ['lax', 'strict'], true),
            'Dompdf sin PHP ni recursos remotos' => !config('dompdf.options.enable_php') && !config('dompdf.options.enable_remote'),
        ];
        if ($this->option('production') || app()->environment('production')) {
            $checks += [
                'APP_ENV=production' => app()->environment('production'),
                'APP_DEBUG=false' => !config('app.debug'),
                'URL HTTPS' => str_starts_with(config('app.url'), 'https://'),
                'Cookies Secure' => (bool) config('session.secure'),
                'Población de escenarios deshabilitada' => SeedProfile::requested() !== 'demo',
                'Sin archivo public/hot de desarrollo' => !is_file(public_path('hot')),
                'Assets compilados' => is_file(public_path('build/manifest.json')),
            ];
        }
        foreach ($checks as $label => $ok) $this->line(($ok ? '[OK] ' : '[PENDIENTE] ').$label);
        return in_array(false, $checks, true) ? self::FAILURE : self::SUCCESS;
    }
}
