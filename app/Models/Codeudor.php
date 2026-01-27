<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Codeudor extends Model
{
    use HasFactory;

    /**
     * El nombre de la tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'codeudor';

    /**
     * Indicamos que la tabla no tiene columnas de timestamps (created_at, updated_at).
     * (Basado en el SQL proporcionado).
     *
     * @var bool
     */
    public $timestamps = false;

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
        'tipo',
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
    // RELACIONES
    // ---

    /**
     * Los codeudores pertenecen a muchas solicitudes (Relación Muchos a Muchos).
     */
    public function solicitudes()
    {
        return $this->belongsToMany(Solicitud::class, 'solicitud_codeudor', 'id_codeudor', 'id_solicitud')
                    ->using(SolicitudCodeudor::class);
    }

    public function telefonos()
    {
        return $this->hasMany(Telefono::class, 'id_codeudor');
    }

    /**
     * Obtener las direcciones asociadas al cliente.
     */
    public function direcciones()
    {
        return $this->hasMany(Direccion::class, 'id_codeudor');
    }
}