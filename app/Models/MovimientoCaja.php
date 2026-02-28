<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoCaja extends Model
{
    use HasFactory;

    // Especificamos el nombre exacto de la tabla
    protected $table = 'movimientos_caja';

    protected $fillable = [
        'tipo_movimiento',
        'descripcion',
        'monto',
        'fecha',
        'id_caja',
        'id_usuario',
    ];

    /**
     * Casts para formatear los datos correctamente para el frontend (Vue)
     */
    protected $casts = [
        'monto' => 'decimal:2',
        'fecha' => 'datetime',
    ];

    // --- RELACIONES ---

    /**
     * Obtener la caja a la que pertenece este movimiento.
     */
    public function caja()
    {
        return $this->belongsTo(Caja::class, 'id_caja');
    }

    /**
     * Obtener el usuario que registró este movimiento.
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}