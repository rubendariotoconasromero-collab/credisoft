<?php

namespace Database\Seeders;

use LogicException;

final class SeedProfile
{
    public static function requested(): string
    {
        $configured = config('app.seed_profile');
        if ($configured === null || $configured === '') {
            // Compatibility with existing installations that still use SEED_DEMO_DATA.
            return config('app.seed_demo_data') ? 'demo' : 'operativo';
        }
        $value = is_string($configured) ? mb_strtolower(trim($configured)) : '';
        if (!in_array($value, ['demo', 'operativo'], true)) {
            throw new LogicException('SEED_PROFILE debe ser demo u operativo.');
        }
        return $value;
    }

    public static function resolve(): string
    {
        $profile = self::requested();
        if ($profile === 'demo' && !app()->environment(['local', 'testing'])) {
            if (config('app.seed_profile')) {
                throw new LogicException('SEED_PROFILE=demo solo se permite en local o testing.');
            }
            return 'operativo';
        }
        return $profile;
    }
}
