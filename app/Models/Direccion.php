<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Direccion extends Model
{
    use HasFactory;

    protected $table = 'direccion';

    protected $fillable = [
        'tipo',        // Domicilio, Trabajo, etc.
        'departamento',
        'ciudad',
        'zona',
        'descripcion', // Calle/Av/Número
        'lat',
        'lng',
        'referencia',
        'id_cliente',
        'id_codeudor',
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
    ];

    // Relación con Cliente
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    // Relación con Codeudor
    public function codeudor()
    {
        return $this->belongsTo(Codeudor::class, 'id_codeudor');
    }
}