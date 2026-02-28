<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Desembolso extends Model
{
    use HasFactory;

    protected $table = 'desembolso';

    // ¡CRÍTICO! Desactivamos los timestamps porque la tabla no tiene created_at / updated_at
    public $timestamps = false;

    protected $fillable = [
        'monto',
        'fecha',
        'estado',
        'id_plan_pago',
        'id_usuario',
        'id_caja',
    ];

    /**
     * Casts para formatear los datos correctamente para el frontend
     */
    protected $casts = [
        'monto' => 'decimal:2',
        'fecha' => 'datetime',
        'estado' => 'integer',
    ];

    // --- RELACIONES ---

    /**
     * Obtener el plan de pago asociado a este desembolso.
     */
    public function planPago()
    {
        return $this->belongsTo(PlanPago::class, 'id_plan_pago');
    }

    /**
     * Obtener el usuario que registró el desembolso.
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    /**
     * Obtener la caja desde donde salió el dinero del desembolso.
     */
    public function caja()
    {
        return $this->belongsTo(Caja::class, 'id_caja');
    }
}