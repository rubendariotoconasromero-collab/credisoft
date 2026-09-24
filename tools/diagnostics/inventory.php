<?php

// Read source and registered routes, without connecting to the usual database.
require dirname(__DIR__, 2).'/vendor/autoload.php';
\Tests\Support\IsolatedApplication::create();
$routes = [];
$missing = [];
foreach (app('router')->getRoutes() as $route) {
    $action = $route->getActionName();
    $exists = true;
    if (str_contains($action, '@')) {
        [$class, $method] = explode('@', $action, 2);
        $exists = class_exists($class) && method_exists($class, $method);
    }
    $row = ['methods' => $route->methods(), 'uri' => $route->uri(),
        'action' => $action, 'middleware' => $route->gatherMiddleware(), 'method_exists' => $exists];
    $routes[] = $row;
    if (!$exists) {
        $missing[] = $row;
    }
}
$schema = [];
foreach (glob(base_path('database/migrations/*.php')) as $file) {
    $source = file_get_contents($file);
    preg_match_all('/Schema::(create|table)\([\'\"]([^\'\"]+)/', $source, $matches, PREG_SET_ORDER);
    foreach ($matches as $match) {
        $schema[$match[2]][] = ['operation' => $match[1], 'migration' => basename($file)];
    }
}
$tablesByController = [];
foreach (glob(app_path('Http/Controllers/*.php')) as $file) {
    preg_match_all('/(?:table|join|leftJoin)\([\'\"]([^\'\"]+)/', file_get_contents($file), $matches);
    $tablesByController[basename($file)] = array_values(array_unique($matches[1]));
}
$installed = json_decode(file_get_contents(base_path('vendor/composer/installed.json')), true);
$versions = [];
foreach ($installed['packages'] as $package) {
    if (in_array($package['name'], ['laravel/framework', 'phpunit/phpunit', 'mpdf/mpdf', 'dompdf/dompdf'])) {
        $versions[$package['name']] = $package['version'];
    }
}
$output = ['generated_at' => date(DATE_ATOM), 'php' => PHP_VERSION, 'versions' => $versions,
    'route_count' => count($routes), 'missing_methods' => $missing, 'routes' => $routes,
    'schema_from_migrations' => $schema, 'controller_tables_static' => $tablesByController];
$target = base_path('docs/diagnostico-etapa-1');
if (!is_dir($target)) mkdir($target, 0777, true);
file_put_contents($target.'/inventario.json', json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).PHP_EOL);
echo 'Routes: '.count($routes).'; missing controller methods: '.count($missing).PHP_EOL;
