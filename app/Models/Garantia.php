<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Garantia extends Model
{
    use HasFactory;

    // --- CORRECCIÓN CRÍTICA ---
    // Aseguramos el nombre exacto de la tabla.
    protected $table = 'garantia';

    protected $fillable = [
        'descripcion',
        'id_solicitud',
    ];

    protected $casts = [
        'id_solicitud' => 'integer',
    ];

    /**
     * Obtener la solicitud a la que pertenece esta garantía.
     */
    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class, 'id_solicitud');
    }

    public function imagenes()
    {
        return $this->hasMany(Imagen::class, 'id_garantia');
    }
}