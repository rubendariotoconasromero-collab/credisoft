<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudRespaldo extends Model
{
    use HasFactory;

    protected $table = 'solicitud_respaldo';

    protected $fillable = [
        'importe_solicitud',
        'moneda',
        'lapso_capital',
        'nro_cuotas',
        'tasa',
        'fecha',
        'fecha_desembolso',
        'fecha_primera_cuota',
        'destino_prestamo',
        'tipo_garantia',
        'tipo_desembolso',
        'tipo_tasa',
        'monto_pago_adm',
        'estado',
        'id_cliente',
        'id_usuario',
        'solicitud_id', // ID original de la solicitud
        'accion',       // Acción realizada (update, delete, etc.)
        'usuario_accion' // Quién hizo la acción
    ];

    /**
     * Casts para mantener los tipos de datos en la lógica de Laravel
     */
    protected $casts = [
        'importe_solicitud' => 'decimal:2',
        'tasa' => 'decimal:2',
        'monto_pago_adm' => 'decimal:2',
        'fecha' => 'date',
        'fecha_desembolso' => 'date',
        'fecha_primera_cuota' => 'date',
        'nro_cuotas' => 'integer',
        'estado' => 'integer',
    ];

    // --- RELACIONES ---

    /**
     * La solicitud original a la que pertenece este respaldo.
     */
    public function solicitudOriginal()
    {
        return $this->belongsTo(Solicitud::class, 'solicitud_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    /**
     * El usuario que fue registrado originalmente en la solicitud.
     */
    public function usuarioCreador()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    /**
     * El usuario que desencadenó la acción de respaldo (ej. quien modificó o eliminó).
     */
    public function usuarioAccion()
    {
        return $this->belongsTo(User::class, 'usuario_accion');
    }
}