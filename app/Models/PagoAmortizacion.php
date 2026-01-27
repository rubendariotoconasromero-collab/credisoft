<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagoAmortizacion extends Model
{
    use HasFactory;
    protected $table = 'pago_amortizacion';

    protected $fillable = [
        'fecha',
        'monto_pago',
        'capital_pagado',
        'interes_pagado',
        'multa_pagada',
        'saldo_pendiente',
        'total_seguro',
        'forma_pago',
        'tipo',
        'estado',
        'monto_desembolso',
        'fecha_desembolso',
        'id_caja',
        'id_plan_pago',
        'id_cuota',
        'id_usuario',
    ];

    protected $casts = [
        // 'fecha' => 'date',
        // 'fecha_desembolso' => 'date',
        'monto_pago' => 'decimal:2',
        'capital_pagado' => 'decimal:2',
        'interes_pagado' => 'decimal:2',
        'multa_pagada' => 'decimal:2',
        'saldo_pendiente' => 'decimal:2',
        'total_seguro' => 'decimal:2',
        'monto_desembolso' => 'decimal:2',
        // 'estado' => 'boolean',
    ];

    // Relación con Caja
    public function caja()
    {
        return $this->belongsTo(Caja::class, 'id_caja');
    }

    // Relación con PlanPago
    public function planPago()
    {
        return $this->belongsTo(PlanPago::class, 'id_plan_pago');
    }

    // Relación con Cuota
    public function cuota()
    {
        return $this->belongsTo(Cuota::class, 'id_cuota');
    }

    // Relación con Usuario
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
