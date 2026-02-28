<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MiEmpresa extends Model
{
    use HasFactory;

    // Especificamos el nombre exacto de la tabla en singular
    protected $table = 'mi_empresa';

    // Definimos los campos que se pueden guardar de forma masiva
    protected $fillable = [
        'nombre',
        'nit',
        'direccion',
        'telefono',
        'email',
        'logo'
    ];
}