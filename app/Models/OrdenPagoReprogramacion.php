<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdenPagoReprogramacion extends Model
{
    use HasFactory;

    protected $table = 'orden_pago_reprogramaciones';

    protected $fillable = [
        'id_solicitud_nueva',
        'id_plan_pago_origen',
        'monto_interes_calculado',
        'monto_mora_calculado',
        'se_condono_interes',
        'monto_condonado_interes',
        'se_condono_mora',
        'monto_condonado_mora',
        'motivo_condonacion',
        'total_a_pagar',
        'ids_cuotas_afectadas',
        'estado'
    ];

    // CASTS: Para manejar tipos de datos automáticamente
    protected $casts = [
        'ids_cuotas_afectadas' => 'array', // Laravel lo trata como array en PHP y JSON en BD
        'se_condono_interes' => 'boolean',
        'se_condono_mora' => 'boolean',
        'monto_interes_calculado' => 'decimal:2',
        'monto_mora_calculado' => 'decimal:2',
        'total_a_pagar' => 'decimal:2',
    ];

    // CONSTANTES DE ESTADO (Para usar en tu código y evitar números mágicos)
    const ESTADO_NO_DISPONIBLE = 0;
    const ESTADO_POR_PAGAR = 1;
    const ESTADO_PAGADO = 2;
    const ESTADO_ANULADO = 3;

    // --- RELACIONES ---

    // Relación con la nueva solicitud (Hijo -> Padre)
    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class, 'id_solicitud_nueva');
    }

    // Relación con el plan antiguo
    public function planPagoOrigen()
    {
        return $this->belongsTo(PlanPago::class, 'id_plan_pago_origen');
    }
}