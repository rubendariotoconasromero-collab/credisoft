<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

// SolicitudCodeudor es una tabla pivot (sin su propio ID)
class SolicitudCodeudor extends Pivot
{
    // No usamos HasFactory en pivots
    
    protected $table = 'solicitud_codeudor';

    // Indicamos a Laravel que no use auto-increment (porque usa una clave compuesta)
    public $incrementing = false;

    // Definimos las claves primarias compuestas
    protected $primaryKey = ['id_codeudor', 'id_solicitud'];
    
    // Deshabilitamos el uso de timestamps ya que la tabla no las tiene
    public $timestamps = false;

    protected $fillable = [
        'id_codeudor',
        'id_solicitud',
    ];
    
    // Nota: Como esta tabla no tiene created_at/updated_at, al usar Eloquent
    // se necesita configurar estos campos o usar el Facade DB para inserción.
    // En el controlador usaremos el Facade DB para ser más directos.
}