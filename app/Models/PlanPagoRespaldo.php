<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanPagoRespaldo extends Model
{
    use HasFactory;

    // Forzar el nombre de la tabla
    protected $table = 'plan_pago_respaldo';

    protected $fillable = [
        'fecha_inicio',
        'fecha_ultima_amortizacion',
        'fecha_fin',
        'total_pagar',
        'saldo_pendiente',
        'moneda',
        'lapso_capital',
        'nro_cuotas',
        'tasa',
        'tipo_tasa',
        'estado',
        'desembolso',
        'pago_administrativo',
        'id_solicitud',
        'id_plan_aux',
        'solicitud_respaldo_id',
        'plan_pago_original_id', // ID del plan original
        'accion',                // Ej. create, update, delete
        'usuario_accion',
        'ip_address',
        'user_agent',
    ];

    /**
     * Casts para procesar los datos al consultarlos
     */
    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_ultima_amortizacion' => 'date',
        'fecha_fin' => 'date',
        'total_pagar' => 'decimal:2',
        'saldo_pendiente' => 'decimal:2',
        'nro_cuotas' => 'integer',
        'tasa' => 'integer',
        'estado' => 'integer',
    ];

    // --- RELACIONES ---

    /**
     * Obtener el registro de respaldo de la solicitud que engloba este plan.
     */
    public function solicitudRespaldo()
    {
        return $this->belongsTo(SolicitudRespaldo::class, 'solicitud_respaldo_id');
    }

    /**
     * Obtener el plan de pago original al que pertenece este respaldo.
     */
    public function planPagoOriginal()
    {
        return $this->belongsTo(PlanPago::class, 'plan_pago_original_id');
    }

    /**
     * Obtener el usuario que realizó la acción de cambio (auditoría).
     */
    public function usuarioAccion()
    {
        return $this->belongsTo(User::class, 'usuario_accion');
    }
}