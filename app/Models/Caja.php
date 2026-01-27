<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    protected $table = 'caja';

    protected $fillable = [
        'fechahora_apertura', 'fechahora_cierre', 'monto_inicial', 'monto_final',
        'efectivo_total', 'deposito_total', 'efectivo_venta', 'deposito_venta',
        'efectivo_gasto', 'deposito_gasto', 'total_ingreso', 'total_egreso',
        'estado', 'diferencia', 'id_usuario'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'id_caja');
    }
}
