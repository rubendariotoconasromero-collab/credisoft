<?php

// Restores ONLY this run's synthetic dump into a new database on the verified disposable server.
$root = dirname(__DIR__, 2);
$options = getopt('', ['mysql-port:', 'mysql-datadir:', 'backup:']);
$port = filter_var($options['mysql-port'] ?? '', FILTER_VALIDATE_INT);
$expected = realpath($options['mysql-datadir'] ?? '');
$backup = realpath($options['backup'] ?? '');
$allowed = realpath($root.'/storage/app/diagnostics');
if (!$port || !$expected || !$backup || !$allowed ||
    !str_starts_with(strtolower($expected), strtolower($allowed.DIRECTORY_SEPARATOR)) ||
    strcasecmp(dirname($backup), dirname($expected)) !== 0) {
    throw new RuntimeException('Invalid isolated restore paths.');
}
$pdo = new PDO("mysql:host=127.0.0.1;port=$port;charset=utf8mb4", 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$actual = realpath($pdo->query('SELECT @@datadir')->fetchColumn());
if (!$actual || strcasecmp($actual, $expected) !== 0) throw new RuntimeException('Unexpected MySQL server; refusing restore.');
$pdo->exec('CREATE DATABASE credisoft_diagnostic_restore CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$pdo->exec('USE credisoft_diagnostic_restore');
$pdo->exec(file_get_contents($backup));
$checks = [];
foreach ($pdo->query('SHOW TABLES FROM credisoft_diagnostic')->fetchAll(PDO::FETCH_COLUMN) as $table) {
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) throw new RuntimeException('Unexpected table identifier.');
    $signatures = [];
    $definitions = [];
    foreach (['credisoft_diagnostic', 'credisoft_diagnostic_restore'] as $db) {
        $pdo->exec("USE `$db`");
        $rows = $pdo->query("SELECT * FROM `$db`.`$table`")->fetchAll(PDO::FETCH_ASSOC);
        $serialized = array_map(fn ($row) => json_encode($row, JSON_UNESCAPED_UNICODE), $rows);
        sort($serialized, SORT_STRING);
        $ddl = $pdo->query("SHOW CREATE TABLE `$db`.`$table`")->fetch(PDO::FETCH_NUM)[1];
        $definitions[$db] = $ddl;
        // mysqldump makes inherited column charsets explicit. Remove only
        // declarations identical to this table's default; preserve collations.
        $canonicalDdl = $ddl;
        if (preg_match('/DEFAULT CHARSET=([a-zA-Z0-9_]+)/', $ddl, $charset)) {
            $canonicalDdl = preg_replace('/ CHARACTER SET '.preg_quote($charset[1], '/').'(?= )/', '', $ddl);
        }
        $signatures[] = ['count' => count($rows), 'sha256' => hash('sha256', implode("\n", $serialized)),
            'ddl_canonical_sha256' => hash('sha256', $canonicalDdl)];
    }
    $checks[$table] = ['match' => $signatures[0] === $signatures[1], 'source' => $signatures[0], 'restored' => $signatures[1]];
    if (!$checks[$table]['match']) $checks[$table]['definitions'] = $definitions;
}
$success = !in_array(false, array_column($checks, 'match'), true);
file_put_contents($root.'/docs/diagnostico-etapa-1/restauracion.json', json_encode([
    'generated_at' => date(DATE_ATOM), 'synthetic_only' => true, 'success' => $success,
    'scope' => 'MySQL row hashes and canonical SHOW CREATE TABLE (explicit inherited column charset normalized); not a production backup or attachment restore',
    'server_version' => $pdo->query('SELECT VERSION()')->fetchColumn(),
    'tables' => $checks,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL);
echo 'Restore: '.($success ? 'MATCH' : 'MISMATCH').' ('.count($checks).' tables)'.PHP_EOL;
exit($success ? 0 : 1);
