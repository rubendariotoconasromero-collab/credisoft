<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CuotaRespaldo extends Model
{
    use HasFactory;

    // Forzamos el nombre de la tabla
    protected $table = 'cuota_respaldo';

    // Todos los campos que se pueden llenar masivamente
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
        'plan_pago_respaldo_id',
        'cuota_original_id',
        'accion',
        'usuario_accion',
        'ip_address',
        'user_agent',
    ];

    /**
     * Casts para asegurar que los formatos de datos sean correctos en Vue
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
    ];

    // --- RELACIONES ---

    /**
     * Obtener el respaldo del plan de pago al que pertenece esta cuota.
     */
    public function planPagoRespaldo()
    {
        return $this->belongsTo(PlanPagoRespaldo::class, 'plan_pago_respaldo_id');
    }

    /**
     * Obtener la cuota original a la que hace referencia este respaldo.
     */
    public function cuotaOriginal()
    {
        return $this->belongsTo(Cuota::class, 'cuota_original_id');
    }

    /**
     * Obtener el usuario que realizó la acción de auditoría.
     */
    public function usuarioAccion()
    {
        return $this->belongsTo(User::class, 'usuario_accion');
    }
}