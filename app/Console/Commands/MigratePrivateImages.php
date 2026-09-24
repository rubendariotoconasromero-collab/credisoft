<?php

namespace App\Console\Commands;

use App\Services\PrivateImages;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MigratePrivateImages extends Command
{
    protected $signature = 'expedientes:privatizar {--apply : Copiar, verificar SHA-256 y retirar originales públicos}';
    protected $description = 'Inventaría y traslada adjuntos históricos fuera de public; sin --apply solo revisa.';

    public function handle(): int
    {
        $items = [];
        try {
            // Validate the entire inventory first. A collision never overwrites either file.
            foreach (PrivateImages::CATEGORIES as $category) {
                $directory = public_path('img/'.$category);
                if (!is_dir($directory)) continue;
                if (is_link($directory)) throw new \RuntimeException('Carpeta pública enlazada: '.$category);
                foreach (new \DirectoryIterator($directory) as $file) {
                    if ($file->isDot() || $file->getFilename() === 'default.png') continue;
                    if ($file->isLink() || !$file->isFile()) throw new \RuntimeException('Entrada no regular: '.$file->getFilename());
                    $source = $file->getPathname();
                    $target = PrivateImages::path($category, $file->getFilename());
                    $hash = hash_file('sha256', $source);
                    if (!$hash || (file_exists($target) && (!is_file($target) || hash_file('sha256', $target) !== $hash))) {
                        throw new \RuntimeException('Colisión o archivo ilegible: '.$category.'/'.$file->getFilename());
                    }
                    $items[] = compact('source', 'target', 'hash');
                }
            }
            $this->info(count($items).' archivos pendientes; los default.png genéricos permanecen públicos.');
            if (!$this->option('apply') || !$items) return self::SUCCESS;

            // Exclusive manifest also serves as a migration lock; never publish this file.
            $root = config('filesystems.disks.expedientes.root');
            File::ensureDirectoryExists($root, 0700);
            $lock = fopen($root.'/migration.lock', 'c');
            if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) throw new \RuntimeException('Otra migración está en curso.');
            try {
                foreach ($items as $item) {
                    File::ensureDirectoryExists(dirname($item['target']), 0700);
                    if (!file_exists($item['target'])) {
                        // Exclusive create protects against races and accidental overwrites.
                        $out = fopen($item['target'], 'xb');
                        $in = fopen($item['source'], 'rb');
                        if (!$out || !$in) throw new \RuntimeException('No se pudo copiar un adjunto.');
                        try { stream_copy_to_stream($in, $out); }
                        finally { fclose($in); fclose($out); }
                    }
                    if (hash_file('sha256', $item['target']) !== $item['hash'] || hash_file('sha256', $item['source']) !== $item['hash']) {
                        throw new \RuntimeException('Falló la verificación SHA-256; se conserva el original.');
                    }
                    $manifest = ['file' => basename(dirname($item['target'])).'/'.basename($item['target']), 'sha256' => $item['hash'], 'at' => now()->toIso8601String()];
                    if (file_put_contents($root.'/migration.jsonl', json_encode($manifest).PHP_EOL, FILE_APPEND | LOCK_EX) === false) {
                        throw new \RuntimeException('No se pudo guardar el manifiesto.');
                    }
                    if (!unlink($item['source'])) throw new \RuntimeException('Copia verificada; no se pudo retirar el original público.');
                }
            } finally { flock($lock, LOCK_UN); fclose($lock); }
            $this->info('Traslado verificado. Referencias de BD conservadas; no se modificó la base de datos.');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }
}
