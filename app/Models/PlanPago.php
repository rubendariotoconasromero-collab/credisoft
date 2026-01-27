<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanPago extends Model
{
    use HasFactory;

    /**
     * El nombre de la tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'plan_pago';

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'fecha_inicio',
        'fecha_fin',
        'total_pagar',
        'estado',
        'desembolso',
        'pago_administrativo',
        'id_solicitud',
        'id_plan_aux',
        'moneda',
        'lapso_capital',
        'nro_cuotas',
        'tasa',
        'fecha_ultima_amortizacion',
        'saldo_pendiente',
        'fecha_registro',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'total_pagar' => 'decimal:2',
        'estado' => 'integer',
        'desembolso' => 'integer',
        'pago_administrativo' => 'integer',
        'nro_cuotas' => 'integer',
        'tasa' => 'integer',
        'fecha_ultima_amortizacion' => 'date',
        'saldo_pendiente' => 'decimal:2',
        'fecha_registro' => 'date',
    ];

    // ---
    // RELACIONES
    // ---

    /**
     * Obtener la solicitud a la que pertenece este plan de pago.
     */
    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class, 'id_solicitud');
    }

    /**
     * Obtener todas las cuotas de este plan de pago.
     */
    public function cuotas()
    {
        return $this->hasMany(Cuota::class, 'id_plan_pago');
    }
}