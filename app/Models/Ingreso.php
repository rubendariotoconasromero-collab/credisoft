<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingreso extends Model
{
    use HasFactory;

    // Forzamos el nombre de la tabla en singular
    protected $table = 'ingreso';

    protected $fillable = [
        'monto',
        'descripcion',
        'estado',
        'fecha',
        'id_usuario',
        'id_caja',
    ];

    /**
     * Casts para formatear los datos correctamente para el frontend
     */
    protected $casts = [
        'monto' => 'decimal:2',
        'fecha' => 'date',
    ];

    // --- RELACIONES ---

    /**
     * Obtener el usuario que registró este ingreso.
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    /**
     * Obtener la caja a la que pertenece este ingreso.
     */
    public function caja()
    {
        return $this->belongsTo(Caja::class, 'id_caja');
    }
}