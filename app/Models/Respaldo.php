<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Respaldo extends Model
{
    use HasFactory;

    // --- CORRECCIÓN CRÍTICA ---
    // Aseguramos el nombre exacto de la tabla.
    protected $table = 'respaldo';

    protected $fillable = [
        'descripcion',
        'imagen',
        'id_solicitud',
    ];

    protected $casts = [
        'id_solicitud' => 'integer',
    ];

    /**
     * Obtener la solicitud a la que pertenece este respaldo.
     */
    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class, 'id_solicitud');
    }


    public function lista_imagenes()
    {
        return $this->hasMany(ImagenRespaldo::class, 'id_respaldo');
    }
}