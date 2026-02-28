<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Egreso extends Model
{
    use HasFactory;

    protected $table = 'egreso';

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
     * Obtener el usuario que registró este egreso.
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    /**
     * Obtener la caja a la que pertenece este egreso.
     */
    public function caja()
    {
        return $this->belongsTo(Caja::class, 'id_caja');
    }
}