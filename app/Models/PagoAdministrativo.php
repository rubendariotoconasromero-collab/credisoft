<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagoAdministrativo extends Model
{
    use HasFactory;

    // Nombre exacto de la tabla
    protected $table = 'pago_administrativo';

    // ¡CRÍTICO! Desactivamos los timestamps porque no existen en esta tabla
    public $timestamps = false;

    // Campos asignables masivamente
    protected $fillable = [
        'monto',
        'fecha',
        'estado',
        'descripcion',
        'id_plan_pago',
        'id_usuario',
        'id_caja',
    ];

    /**
     * Casts para formatear los datos correctamente para el frontend (Vue)
     */
    protected $casts = [
        'monto' => 'decimal:2',
        'fecha' => 'datetime',
        'estado' => 'integer',
    ];

    // --- RELACIONES ---

    /**
     * Obtener el plan de pago asociado a este cobro administrativo.
     */
    public function planPago()
    {
        return $this->belongsTo(PlanPago::class, 'id_plan_pago');
    }

    /**
     * Obtener el usuario que registró este pago administrativo.
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    /**
     * Obtener la caja donde ingresó este pago administrativo.
     */
    public function caja()
    {
        return $this->belongsTo(Caja::class, 'id_caja');
    }
}