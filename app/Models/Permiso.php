<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    use HasFactory;

    // Especificamos el nombre exacto de la tabla
    protected $table = 'permiso';

    // Campos que se pueden llenar masivamente
    protected $fillable = [
        'nombre',
        'descripcion',
        'estado'
    ];

    // Relación de muchos a muchos con Rol
    public function roles()
    {
        // Especificamos la tabla pivote 'permiso_rol' y las llaves foráneas
        return $this->belongsToMany(Rol::class, 'permiso_rol', 'id_permiso', 'id_rol')->withTimestamps();
    }
}