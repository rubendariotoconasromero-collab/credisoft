<!doctype html><html lang="es"><head><meta charset="utf-8"><style>
body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color:#222; } table { width:100%; border-collapse:collapse; margin:16px 0; } th,td { border:1px solid #bbb; padding:7px; text-align:left; } h1 {font-size:18px;}
</style></head><body>
<h1>Comprobante de {{ $op->tipo }}</h1>
<p>Operación: {{ $op->codigo }}<br>Fecha efectiva: {{ $op->fecha_efectiva }}<br>Registrado: {{ $op->created_at }}<br>Caja: {{ $op->id_caja }} — Usuario: {{ $usuario }}<br>Crédito: {{ $op->id_plan_pago ?? 'No aplica' }}</p>
@if($op->anulado_at)<h2>ANULADO</h2><p>{{ $op->anulado_at }} — {{ $op->motivo_anulacion }}</p>@endif
<table><thead><tr><th>Forma</th><th>Entregado</th><th>Aplicado (Bs)</th><th>Referencia</th></tr></thead><tbody>
@foreach($op->partes as $part)<tr><td>{{ ['efectivo'=>'Efectivo','deposito'=>'Depósito','transferencia'=>'Transferencia / QR'][$part['forma_pago']] }}</td><td>{{ $part['entregado'] ?? $part['monto'] }}</td><td>{{ $part['monto'] }}</td><td>{{ $part['referencia'] }}</td></tr>@endforeach
</tbody></table><p><strong>Total aplicado: {{ $op->monto }} Bs — Cambio: {{ $op->cambio }} Bs</strong></p>
@if(!empty($op->resultado['detalles']))<table><thead><tr><th>Cuota</th><th>Capital</th><th>Interés</th><th>Multa</th><th>Condonado</th></tr></thead><tbody>
@foreach($op->resultado['detalles'] as $q)<tr><td>{{ $q['cuota'] }}</td><td>{{ $q['capital'] }}</td><td>{{ $q['interes'] }}</td><td>{{ $q['mora'] }}</td><td>{{ $q['condonado'] }}</td></tr>@endforeach
</tbody></table>@endif
<p>Este comprobante conserva los importes aplicados al registrar la operación.</p>
</body></html>
