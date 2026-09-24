<?php

require dirname(__DIR__, 2).'/vendor/autoload.php';
\Tests\Support\IsolatedApplication::create();
$root = base_path();
$active = [];
$visit = function ($path) use (&$visit, &$active) {
    foreach ([$path, $path.'.js', $path.'.vue', $path.'/index.js'] as $candidate) {
        if (is_file($candidate)) { $file = realpath($candidate); break; }
    }
    if (!isset($file) || isset($active[$file])) return;
    $active[$file] = true;
    preg_match_all('/(?:from\s*|import\s*\(?\s*)[\'\"](\.[^\'\"]+)[\'\"]/', file_get_contents($file), $matches);
    foreach ($matches[1] as $import) $visit(dirname($file).'/'.$import);
};
$visit(resource_path('js/app.js'));
$calls = [];
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(resource_path('js')));
foreach ($files as $file) {
    if (!$file->isFile() || !in_array($file->getExtension(), ['vue', 'js'])) continue;
    $source = file_get_contents($file->getPathname());
    preg_match_all('/axios\.(get|post|put|patch|delete)\(\s*([\'\"`])([^\'\"`]+)\2/', $source, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
    foreach ($matches as $match) {
        $method = strtoupper($match[1][0]); $url = $match[3][0];
        if (!str_starts_with($url, '/')) continue;
        $probe = preg_replace('/\$\{[^}]+\}/', '1', explode('?', $url)[0]);
        $status = 'matched'; $action = null;
        try {
            $route = app('router')->getRoutes()->match(\Illuminate\Http\Request::create($probe, $method));
            $action = $route->getActionName();
            if ($route->getName() === 'spa') $status = 'spa_fallback_not_api';
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $status = 'http_'.$e->getStatusCode();
        }
        $calls[] = ['file' => str_replace('\\', '/', substr($file->getRealPath(), strlen($root) + 1)),
            'line' => substr_count(substr($source, 0, $match[0][1]), "\n") + 1,
            'active_import_graph' => isset($active[$file->getRealPath()]), 'method' => $method,
            'url_literal' => $url, 'probe' => $probe, 'status' => $status, 'action' => $action];
    }
}
preg_match_all('/\{ path: \'([^\']+)\'.*?component:.*?import\(\'([^\']+)\'\).*?permiso: (null|\'[^\']+\')/',
    file_get_contents(resource_path('js/router/routes.js')), $modules, PREG_SET_ORDER);
$screens = array_map(fn ($m) => ['path' => $m[1], 'component' => $m[2], 'permission' => trim($m[3], "'")], $modules);
$output = ['generated_at' => date(DATE_ATOM), 'scope' => 'Static candidate inventory; regex may include comments and cannot resolve variable URLs or concatenation.',
    'screens' => $screens, 'active_files' => count($active), 'calls' => $calls];
file_put_contents(base_path('docs/diagnostico-etapa-1/frontend.json'), json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
$lines = ['# Inventario de pantallas', '', 'Generado desde resources/js/router/routes.js. Estado: existencia y carga declarada verificadas; aceptación visual pendiente.', '',
    '| Ruta | Componente | Permiso de navegación |', '|---|---|---|'];
foreach ($screens as $screen) {
    $lines[] = '| `'.$screen['path'].'` | `'.$screen['component'].'` | '.$screen['permission'].' |';
}
file_put_contents(base_path('docs/diagnostico-etapa-1/pantallas.md'), implode(PHP_EOL, $lines).PHP_EOL);
$missing = array_filter($calls, fn ($c) => $c['active_import_graph'] && $c['status'] !== 'matched');
echo 'Screens: '.count($screens).'; active unresolved API candidates: '.count($missing).PHP_EOL;
foreach ($missing as $call) echo $call['file'].':'.$call['line'].' '.$call['method'].' '.$call['url_literal'].' => '.$call['status'].PHP_EOL;
