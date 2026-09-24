<?php

require dirname(__DIR__, 2).'/vendor/autoload.php';
\Tests\Support\IsolatedApplication::create();
set_exception_handler(static function (Throwable $e) { fwrite(STDERR, $e->__toString().PHP_EOL); exit(1); });
\Carbon\Carbon::setTestNow(\Carbon\Carbon::parse('2026-09-24 12:00:00'));
$options = getopt('', ['mysql-port:', 'mysql-datadir:', 'worker:', 'user:', 'cash:', 'ready:']);
$port = filter_var($options['mysql-port'] ?? '', FILTER_VALIDATE_INT);
$expected = realpath($options['mysql-datadir'] ?? '');
$allowed = realpath(storage_path('app/diagnostics'));
if (!$port || $port < 1024 || !$expected || !$allowed || !str_starts_with(strtolower($expected), strtolower($allowed.DIRECTORY_SEPARATOR))) {
    throw new RuntimeException('Only a disposable diagnostics server is permitted.');
}
$pdo = new PDO("mysql:host=127.0.0.1;port=$port;charset=utf8mb4", 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$actual = realpath($pdo->query('SELECT @@datadir')->fetchColumn());
if (!$actual || strcasecmp($actual, $expected) !== 0) throw new RuntimeException('Unexpected datadir; refusing writes.');
config(['database.default' => 'finance_test', 'database.connections' => ['finance_test' => [
    'driver' => 'mysql', 'host' => '127.0.0.1', 'port' => $port, 'database' => 'credisoft_finance_test',
    'username' => 'root', 'password' => '', 'charset' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '', 'strict' => true,
]]]);
