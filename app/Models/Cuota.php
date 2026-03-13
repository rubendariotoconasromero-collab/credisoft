<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuota extends Model
{
    use HasFactory;

    /**
     * El nombre de la tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'cuota';

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'numero',
        'fecha',
        'capital',
        'interes',
        'saldo_capital',
        'ahorro',
        'seguro',
        'total',
        'estado',
        'amortizado',
        'id_plan_pago',

        'capital_pagado',
        'interes_pagado',
        'mora_pagada',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'numero' => 'integer',
        'fecha' => 'date',
        'capital' => 'decimal:2',
        'interes' => 'decimal:2',
        'saldo_capital' => 'decimal:2',
        'ahorro' => 'decimal:2',
        'seguro' => 'decimal:2',
        'total' => 'decimal:2',
        'estado' => 'integer',
        'amortizado' => 'integer',

        'capital_pagado' => 'decimal:2',
        'interes_pagado' => 'decimal:2',
        'mora_pagada'    => 'decimal:2',
    ];

    // ---
    // RELACIONES
    // ---

    /**
     * Obtener el plan de pago al que pertenece esta cuota.
     */
    public function planPago()
    {
        return $this->belongsTo(PlanPago::class, 'id_plan_pago');
    }
}