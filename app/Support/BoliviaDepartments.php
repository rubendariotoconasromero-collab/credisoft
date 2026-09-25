<?php

namespace App\Support;

use Illuminate\Support\Str;
use RuntimeException;

final class BoliviaDepartments
{
    private static ?array $catalog = null;

    public static function all(): array
    {
        if (self::$catalog !== null) {
            return self::$catalog;
        }

        $catalog = json_decode((string) file_get_contents(
            resource_path('js/data/departamentos-bolivia.json')
        ), true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($catalog) || count($catalog) !== 9) {
            throw new RuntimeException('El catálogo de departamentos de Bolivia no es válido.');
        }

        return self::$catalog = $catalog;
    }

    public static function names(): array
    {
        return array_column(self::all(), 'nombre');
    }

    public static function normalize(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $value = trim(preg_replace('/\s+/u', ' ', $value));
        $key = Str::lower(Str::ascii($value));

        foreach (self::names() as $name) {
            if (Str::lower(Str::ascii($name)) === $key) {
                return $name;
            }
        }

        return $value;
    }

    public static function normalizeDirections(mixed $directions): mixed
    {
        if (!is_array($directions)) {
            return $directions;
        }

        return array_map(function ($direction) {
            if (is_array($direction) && array_key_exists('departamento', $direction)) {
                $direction['departamento'] = self::normalize($direction['departamento']);
            }

            return $direction;
        }, $directions);
    }
}
