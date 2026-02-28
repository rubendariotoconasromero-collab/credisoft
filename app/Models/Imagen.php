<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Imagen extends Model
{
    use HasFactory;

    // Especificamos el nombre exacto de la tabla para evitar que Laravel busque en plural
    protected $table = 'imagen';

    // Los atributos que podemos guardar de forma masiva
    protected $fillable = [
        'imagen',
        'id_garantia',
    ];

    /**
     * Relación: Una imagen pertenece a una garantía específica.
     */
    public function garantia()
    {
        return $this->belongsTo(Garantia::class, 'id_garantia');
    }
}