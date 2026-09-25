<?php

require __DIR__.'/finance-bootstrap.php';
use App\Services\Finance\{CashSessions, VaultOperations};

$ready = $options['ready'] ?? '';
if (realpath(dirname($ready)) !== realpath(dirname($expected))) throw new RuntimeException('Invalid barrier path.');
file_put_contents($ready, 'ready');
\Illuminate\Support\Facades\Auth::setUser(\App\Models\User::findOrFail((int) $options['user']));
try {
    match ($options['worker']) {
        'open' => app(CashSessions::class)->open((int) $options['user'], '100.00'),
        'close' => app(CashSessions::class)->close((int) $options['user'], (int) $options['cash']),
        'withdraw' => app(VaultOperations::class)->move((int) $options['user'], 'salida', '600.00', 'Retiro de prueba', null, null),
        'transfer' => app(VaultOperations::class)->move((int) $options['user'], 'salida', '50.00', 'Transferencia a Caja', (int) $options['cash'], null),
        'collect' => app(\App\Services\Finance\Collections::class)->collect(new \Illuminate\Http\Request([
            'id_plan_pago' => 1, 'id_caja' => (int) $options['cash'], 'fecha_pago' => '2026-09-24', 'modalidad_pago' => 'completo',
            'cuotas' => [['id_cuota' => 1]], 'monto_recibido' => '1076.67', 'forma_pago' => 'efectivo',
        ])),
        'disburse' => app(\App\Services\Finance\CashMovements::class)->save(new \Illuminate\Http\Request([
            'id_plan_pago' => 1, 'id_caja' => (int) $options['cash'], 'monto' => '1000.00', 'forma_pago' => 'transferencia', 'referencia' => 'DISB-CONCURRENCY',
        ]), 'desembolso'),
        'collect-order' => app(\App\Services\Finance\Rescheduling::class)->collect(new \Illuminate\Http\Request([
            'id_orden' => 1, 'id_caja' => (int) $options['cash'], 'clave_operacion' => 'mysql-order-retry-0001',
            'partes' => [['forma_pago' => 'transferencia', 'monto' => '76.67', 'referencia' => 'MYSQL-ORDER']],
        ])),
    };
    echo json_encode(['status' => 200]);
} catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e) {
    echo json_encode(['status' => $e->getStatusCode()]);
}
