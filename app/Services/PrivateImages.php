<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/** Physical paths only; HTTP authorization belongs to PrivateImageController. */
final class PrivateImages
{
    public const CATEGORIES = ['cliente', 'codeudor', 'pago', 'garantia', 'garantias', 'respaldos'];

    public static function directory(string $category): string
    {
        abort_unless(in_array($category, self::CATEGORIES, true), 404);
        $root = config('filesystems.disks.expedientes.root');
        $path = $root.DIRECTORY_SEPARATOR.$category;
        // Neither the private root nor a category may redirect to public storage.
        abort_if(is_link($root) || is_link($path), 404);
        return $path;
    }

    public static function path(string $category, string $reference): string
    {
        foreach (['img/'.$category.'/', $category.'/'] as $prefix) {
            if (str_starts_with($reference, $prefix)) {
                $reference = substr($reference, strlen($prefix));
                break;
            }
        }
        abort_unless(preg_match('/\A[a-zA-Z0-9_.-]+\z/', $reference) && !str_contains($reference, '..'), 404);
        $path = self::directory($category).DIRECTORY_SEPARATOR.$reference;
        abort_if(is_link($path), 404);
        // Generic placeholders contain no customer data and remain distributed with the app.
        if ($reference === 'default.png' && !is_file($path)) {
            return public_path('img/'.$category.'/default.png');
        }
        return $path;
    }

    public static function deleteAfterCommit(string $category, ?string $reference): void
    {
        if (!$reference || basename($reference) === 'default.png') return;
        $path = self::path($category, $reference);
        DB::afterCommit(function () use ($path) {
            if (is_file($path) && !is_link($path) && !unlink($path)) {
                logger()->warning('No se pudo eliminar un adjunto sustituido.', ['path' => $path]);
            }
        });
    }
}
