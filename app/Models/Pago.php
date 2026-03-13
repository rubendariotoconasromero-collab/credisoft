<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'pago';

    protected $fillable = [
        'codigo_transaccion', // <--- Agregado
        'fecha_pago', 
        'monto_pago', 
        'estado', 
        'dias_retrasados',
        'multa_dia', 
        'multa_total', 
        'monto_condonado', 
        'motivo_condonacion',
        'forma_pago', 
        'imagen', 
        'id_usuario', 
        'id_cuota', 
        'id_caja', 
        'monto_cuota',

        'pago_capital',
        'pago_interes',
        'pago_mora',
        'monto_condonado_interes',
        'monto_condonado_mora',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function cuota()
    {
        return $this->belongsTo(Cuota::class, 'id_cuota');
    }

    public function caja()
    {
        return $this->belongsTo(Caja::class, 'id_caja');
    }
}
