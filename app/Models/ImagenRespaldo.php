<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImagenRespaldo extends Model
{
    use HasFactory;

    // Definimos explícitamente la tabla porque no sigue la convención plural estándar (imagenes_respaldos)
    protected $table = 'imagenes_respaldo';

    protected $fillable = [
        'id_respaldo',
        'imagen'
    ];

    /**
     * Relación inversa: Una imagen pertenece a un Respaldo.
     */
    public function respaldo()
    {
        return $this->belongsTo(Respaldo::class, 'id_respaldo');
    }
}