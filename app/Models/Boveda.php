<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Boveda extends Model
{
    use HasFactory;

    // Forzamos el nombre exacto de la tabla
    protected $table = 'boveda';

    // Campos que se pueden llenar masivamente
    protected $fillable = [
        'saldo_actual',
        'fecha_apertura',
        'id_usuario',
    ];

    /**
     * Casts para asegurar el formato correcto en el frontend
     */
    protected $casts = [
        'saldo_actual' => 'decimal:2',
        'fecha_apertura' => 'datetime',
    ];
}