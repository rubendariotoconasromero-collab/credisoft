<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Telefono extends Model
{
    use HasFactory;

    protected $table = 'telefono';

    protected $fillable = [
        'tipo',       // Celular, Fijo, Trabajo
        'numero',
        'observacion',
        'nombre',     // Referencia personal
        'apellidos',
        'relacion',   // Parentesco
        'id_cliente',
        'id_codeudor',
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