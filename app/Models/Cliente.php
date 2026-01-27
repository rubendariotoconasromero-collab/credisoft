<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    /**
     * El nombre de la tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'cliente';

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'fecha_nacimiento',
        'ci',
        'lugar_expedicion',
        'sexo',
        'estado_civil',
        'actividad',
        'vivienda',
        'imagen',
        'ingreso_mensual',
        'estado',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'fecha_nacimiento' => 'date',
        'ingreso_mensual' => 'double', // O 'float'
        'estado' => 'integer',
    ];

    // ---
    // RELACIONES (Basadas en el contexto de tu proyecto)
    // ---

    /**
     * Un cliente puede tener muchas solicitudes de préstamo.
     */
    public function solicitudes()
    {
        return $this->hasMany(Solicitud::class, 'id_cliente');
    }

    /**
     * Obtener los teléfonos asociados al cliente.
     */
    public function telefonos()
    {
        return $this->hasMany(Telefono::class, 'id_cliente');
    }

    /**
     * Obtener las direcciones asociadas al cliente.
     */
    public function direcciones()
    {
        return $this->hasMany(Direccion::class, 'id_cliente');
    }
}