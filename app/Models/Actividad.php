<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Actividad extends Model
{
    use HasFactory;

    // Especificamos el nombre de la tabla ya que Laravel por defecto buscaría "actividads"
    protected $table = 'actividades';

    protected $fillable = [
        'nombre',
        'estado',
    ];

    /**
     * Casts para asegurar que los tipos de datos sean correctos al enviarlos a Vue
     */
    protected $casts = [
        'estado' => 'integer',
    ];
}