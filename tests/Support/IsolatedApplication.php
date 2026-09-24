<?php

namespace Tests\Support;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use RuntimeException;

/** Only synthetic data: never load application database credentials. */
final class IsolatedApplication
{
    public static function create(): Application
    {
        $root = dirname(__DIR__, 2);
        $cache = $root.'/storage/framework/testing/unused-config.php';
        if (is_file($cache)) {
            throw new RuntimeException('Remove the isolated config cache before testing.');
        }

        foreach ([
            'APP_ENV' => 'testing',
            'APP_CONFIG_CACHE' => $cache,
            'APP_KEY' => 'base64:'.base64_encode(str_repeat('t', 32)),
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => ':memory:',
            'DATABASE_URL' => '',
            'DB_USERNAME' => '',
            'DB_PASSWORD' => '',
            'CACHE_DRIVER' => 'array',
            'SESSION_DRIVER' => 'array',
            'MAIL_MAILER' => 'array',
            'QUEUE_CONNECTION' => 'sync',
            'LOG_CHANNEL' => 'null',
        ] as $key => $value) {
            putenv($key.'='.$value);
            $_ENV[$key] = $_SERVER[$key] = $value;
        }

        $app = require $root.'/bootstrap/app.php';
        $app->useEnvironmentPath(__DIR__);
        $app->loadEnvironmentFrom('isolated.env');
        // Restrict named connections before service providers can use them.
        $app->afterBootstrapping(\Illuminate\Foundation\Bootstrap\LoadConfiguration::class, function ($app) {
            $app['config']->set('database.default', 'sqlite');
            $app['config']->set('database.connections', [
                'sqlite' => [
                    'driver' => 'sqlite', 'database' => ':memory:',
                    'prefix' => '', 'foreign_key_constraints' => true,
                ],
            ]);
            $app['config']->set('session.driver', 'array');
            $app['config']->set('cache.default', 'array');
            $app['config']->set('mail.default', 'array');
        });
        $app->make(Kernel::class)->bootstrap();
        return $app;
    }
}
