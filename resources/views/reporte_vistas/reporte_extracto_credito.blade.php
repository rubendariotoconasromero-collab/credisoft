@extends('reporte.plantilla_reporte')

@php
    $money = fn ($value) => number_format((float) $value, 2, ',', '.');
    $signed = fn ($value) => ((float) $value >= 0 ? '+' : '−').$money(abs((float) $value));
    $date = fn ($value) => $value ? \Carbon\Carbon::parse($value)->format('d/m/Y') : '—';
    $state = fn ($value) => match ((int) $value) { 2 => 'Pagada', 3 => 'Pago parcial', 0 => 'Anulada', default => 'Pendiente' };
@endphp

@section('title', 'Extracto de Crédito')
@section('report_title', 'EXTRACTO DE CRÉDITO')

@section('styles')
    .extract-table { width: 100%; border-collapse: collapse; font-size: 7px; }
    .extract-table th, .extract-table td { border: 1px solid #d6e1da; padding: 3px; vertical-align: top; }
    .extract-table th { background: #eaf4ee; color: #204b34; text-transform: uppercase; }
    .extract-table .amount { text-align: right; }
    .extract-table .amount strong { white-space: nowrap; }
    .extract-table .concept { line-height: 1.3; }
    .extract-table .center { text-align: center; }
    .extract-table .payment-detail td { background: #f5f9f6; padding: 6px 10px; }
    .extract-table .payment-detail table { width: 100%; border-collapse: collapse; font-size: 7px; }
    .extract-table .payment-detail table th, .extract-table .payment-detail table td { padding: 3px; }
    .summary-table { width: 100%; border-collapse: collapse; margin: 8px 0; }
    .summary-table td { width: 16.66%; border: 1px solid #d6e1da; padding: 6px; }
    .summary-table span { color: #64748b; font-size: 8px; display: block; }
    .summary-table strong { font-size: 10px; color: #1f2937; }
    .muted { color: #64748b; }
    .positive { color: #15803d; }
    .important { color: #1d4ed8; }
@endsection

@section('info_left')
    <table class="data-table" style="margin-bottom: 0;">
        <tr><th>Cliente</th><td>{{ $credito->cliente_nombre }}</td></tr>
        <tr><th>CI</th><td>{{ $credito->cliente_ci }} {{ $credito->cliente_expedicion }}</td></tr>
        <tr><th>Asesor</th><td>{{ $credito->asesor_nombre }}</td></tr>
    </table>
@endsection

@section('info_right')
    <table class="data-table" style="margin-bottom: 0;">
        <tr><th>Crédito</th><td>#{{ $credito->id }} · {{ (int) $credito->estado === 1 ? 'Vigente' : 'Terminado' }}</td></tr>
        <tr><th>Préstamo / tasa</th><td>{{ $money($credito->importe_solicitud) }} {{ $credito->moneda }} · {{ $credito->tasa }}%</td></tr>
        <tr><th>Plan</th><td>{{ $credito->nro_cuotas }} {{ $credito->lapso_capital }} · {{ $date($credito->fecha_inicio) }} a {{ $date($credito->fecha_fin) }}</td></tr>
        <tr><th>Fecha de corte</th><td>{{ $date($fecha_corte) }}</td></tr>
    </table>
@endsection

@section('totals_box')
    <table class="summary-table">
        <tr>
            <td><span>Programado</span><br><strong>{{ $money($totales['programado']) }} {{ $credito->moneda }}</strong></td>
            <td><span>Variación al corte</span><br><strong>{{ $signed($totales['variacion']) }} {{ $credito->moneda }}</strong></td>
            <td><span>Condonado</span><br><strong>{{ $money($totales['condonado']) }} {{ $credito->moneda }}</strong></td>
            <td><span>Total neto al corte</span><br><strong class="important">{{ $money($totales['exigible']) }} {{ $credito->moneda }}</strong></td>
            <td><span>Cobrado</span><br><strong class="positive">{{ $money($totales['cobrado']) }} {{ $credito->moneda }}</strong></td>
            <td><span>Pendiente</span><br><strong>{{ $money($totales['pendiente']) }} {{ $credito->moneda }}</strong></td>
        </tr>
    </table>
    <p class="muted" style="font-size: 8px; margin: 2px 0 8px;">Programado + variación − condonado = total neto al corte = cobrado + pendiente. El pendiente incluye capital de cuotas futuras; no representa únicamente lo vencido.</p>
@endsection

@section('content')
    <table class="extract-table">
        <thead><tr>
            <th class="center">#</th><th>Vence</th><th class="amount">Saldo cap.</th><th class="amount">Programado</th>
            <th class="amount">Int. devengado</th><th class="amount">Int. moratorio</th><th class="amount">Multa</th>
            <th class="amount">Condonado</th><th class="amount">Total neto</th><th class="amount">Cobrado</th>
            <th class="amount">Pendiente</th><th class="center">Estado</th>
        </tr></thead>
        <tbody>
            @forelse($cuotas as $cuota)
                <tr>
                    <td class="center">{{ $cuota->numero }}</td>
                    <td>{{ $date($cuota->fecha) }}</td>
                    <td class="amount">{{ $money($cuota->saldo_capital) }}<br><span class="muted">Pend. cuota: {{ $money($cuota->capital_neto) }}</span></td>
                    <td class="amount">{{ $money($cuota->total) }}</td>
                    <td class="amount concept">
                        @if($cuota->desglose_cobros_interes_completo)
                            <strong>{{ $money($cuota->interes_ordinario_total_extracto) }}</strong><br>
                            <span class="muted">Cob. {{ $money($cuota->interes_ordinario_cobrado) }} · Cond. {{ $money($cuota->interes_ordinario_condonado) }}</span><br>
                        @else
                            <span class="muted">Sin desglose histórico</span><br>
                        @endif
                        Pend. {{ $money($cuota->interes_devengado_neto) }}
                    </td>
                    <td class="amount concept">
                        @if($cuota->desglose_cobros_interes_completo)
                            <strong>{{ $money($cuota->interes_moratorio_total_extracto) }}</strong><br>
                            <span class="muted">Cob. {{ $money($cuota->interes_moratorio_cobrado) }} · Cond. {{ $money($cuota->interes_moratorio_condonado) }}</span><br>
                        @else
                            <span class="muted">Sin desglose histórico</span><br>
                        @endif
                        Pend. {{ $money($cuota->interes_moratorio_neto) }}
                    </td>
                    <td class="amount concept"><strong>{{ $money($cuota->multa_total_registrada) }}</strong><br>
                        <span class="muted">Cob. {{ $money($cuota->mora_cobrado_total) }} · Cond. {{ $money($cuota->mora_condonado_total) }}</span><br>
                        Pend. {{ $money($cuota->mora_fija_neta) }}
                    </td>
                    <td class="amount">{{ $money($cuota->total_condonado) }}</td>
                    <td class="amount"><strong>{{ $money($cuota->exigible_neto) }}</strong><br><span class="muted">Variación {{ $signed($cuota->variacion_programado) }}</span></td>
                    <td class="amount">{{ $money($cuota->total_cobrado) }}</td>
                    <td class="amount">{{ $money($cuota->total_a_pagar) }}</td>
                    <td class="center">{{ $state($cuota->estado) }}</td>
                </tr>
                @if($cuota->pagos->isNotEmpty())
                    <tr class="payment-detail"><td colspan="12">
                        <strong>Pagos aplicados a la cuota {{ $cuota->numero }}</strong>
                        <table>
                            <thead><tr><th>Fecha / recibo</th><th class="amount">Capital</th><th class="amount">Interés</th><th class="amount">Multa</th><th class="amount">Cobrado</th><th class="amount">Condonado</th><th>Forma</th></tr></thead>
                            <tbody>
                                @foreach($cuota->pagos as $pago)
                                    <tr>
                                        <td>{{ $date($pago->fecha_pago) }} · #{{ $pago->codigo_transaccion ?: $pago->id }}</td>
                                        <td class="amount">{{ $money($pago->pago_capital) }}</td>
                                        <td class="amount">{{ $money($pago->pago_interes) }}</td>
                                        <td class="amount">{{ $money($pago->pago_mora) }}</td>
                                        <td class="amount">{{ $money($pago->monto_pago) }}</td>
                                        <td class="amount">{{ $money($pago->monto_condonado) }}</td>
                                        <td>{{ $pago->forma_pago ?: 'Sin clasificar' }}</td>
                                    </tr>
                                    @if($pago->partes_operacion)
                                        <tr><td colspan="7" class="muted">
                                            Desglose del recibo completo (puede incluir otras cuotas):
                                            @if($pago->monto_operacion !== null) Total {{ $money($pago->monto_operacion) }} {{ $credito->moneda }} · @endif
                                            @foreach($pago->partes_operacion as $parte)
                                                {{ $parte['forma_pago'] ?? '' }} {{ $money($parte['monto'] ?? 0) }}
                                                @if(!empty($parte['referencia'])) ({{ $parte['referencia'] }}) @endif
                                                @if(!$loop->last) · @endif
                                            @endforeach
                                        </td></tr>
                                    @endif
                                    @if((float) $pago->monto_condonado > 0)
                                        <tr><td colspan="7" class="muted">
                                            Condonación: interés {{ $money($pago->monto_condonado_interes) }},
                                            multa {{ $money($pago->monto_condonado_mora) }}.
                                            @if($pago->motivo_condonacion) Motivo: {{ $pago->motivo_condonacion }} @endif
                                        </td></tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </td></tr>
                @endif
            @empty
                <tr><td colspan="12" class="center">No hay cuotas registradas.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection

@section('footer_note', 'Importes calculados al corte indicado. Los abonos de cada fila corresponden a esa cuota; el desglose de formas pertenece al recibo completo.')
