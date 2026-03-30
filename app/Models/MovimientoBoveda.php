<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoBoveda extends Model
{
    use HasFactory;

    // Indicamos el nombre exacto de la tabla (ya que está en plural)
    protected $table = 'movimientos_boveda';

    protected $fillable = [
        'tipo_movimiento',
        'monto',
        'descripcion',
        'fecha',
        'id_boveda',
        'id_usuario',
        'id_socio',
    ];

    /**
     * Casts para formatear la fecha y forzar los dos decimales del dinero en Vue
     */
    protected $casts = [
        'monto' => 'decimal:2',
        'fecha' => 'datetime',
    ];

    // --- RELACIONES ---

    /**
     * Obtener la bóveda afectada por este movimiento.
     */
    public function boveda()
    {
        return $this->belongsTo(Boveda::class, 'id_boveda');
    }

    /**
     * Obtener el usuario que registró este movimiento.
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}