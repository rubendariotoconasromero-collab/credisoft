<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransferenciaCajaBoveda extends Model
{
    use HasFactory;

    // Indicamos explícitamente el nombre de la tabla
    protected $table = 'transferencia_caja_boveda';

    protected $fillable = [
        'tipo_transferencia',
        'descripcion',
        'monto',
        'fecha',
        'id_boveda',
        'id_caja',
    ];

    /**
     * Casts para asegurar el formato de los datos en el panel (Vue)
     */
    protected $casts = [
        'monto' => 'decimal:2',
        'fecha' => 'datetime',
    ];

    // --- RELACIONES ---

    /**
     * Obtener la bóveda involucrada en la transferencia.
     */
    public function boveda()
    {
        return $this->belongsTo(Boveda::class, 'id_boveda');
    }

    /**
     * Obtener la caja involucrada en la transferencia.
     */
    public function caja()
    {
        return $this->belongsTo(Caja::class, 'id_caja');
    }
}