<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;

    protected $table = 'rol';

    protected $fillable = [
        'nombre',
        'estado'
    ];

    // Relación: Un rol tiene muchos usuarios
    public function users()
    {
        return $this->hasMany(User::class, 'id_rol');
    }

    // Relación de muchos a muchos con Permiso
    public function permisos()
    {
        return $this->belongsToMany(Permiso::class, 'permiso_rol', 'id_rol', 'id_permiso')->withTimestamps();
    }
}