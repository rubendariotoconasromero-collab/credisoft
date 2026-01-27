<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Solicitud extends Model
{
    use HasFactory;

    /**
     * El nombre de la tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'solicitud';

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
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
        'desembolso',
        'monto_pago_adm',
        'estado',
        'id_cliente',
        'id_usuario',
        'observacion', // Añadido del esquema completo
        'tipo_solicitud', 
        'cantidad_reprogramaciones', 
        'cantidad_refinanciamientos', 
        'monto_refinanciamiento', 
        'id_solicitud_origen',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'fecha' => 'date',
        'fecha_desembolso' => 'date',
        'fecha_primera_cuota' => 'date',
        'importe_solicitud' => 'float',
        'tasa' => 'float', // Se usa float o decimal(11,2) en BD, cambiamos a float para evitar problemas
        'monto_pago_adm' => 'float',
    ];
    
    // ---
    // RELACIONES
    // ---

    /**
     * Obtener el cliente asociado a la solicitud.
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    /**
     * Obtener el usuario que registró la solicitud.
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    /**
     * Obtener todas las garantías asociadas a esta solicitud.
     */
    public function garantias()
    {
        return $this->hasMany(Garantia::class, 'id_solicitud');
    }

    /**
     * Obtener todos los codeudores asociados a esta solicitud (Relación Many-to-Many).
     * Nota: Asumo que tienes un modelo 'Codeudor' y la tabla pivot es 'solicitud_codeudor'.
     */
    public function codeudores()
    {
        return $this->belongsToMany(Codeudor::class, 'solicitud_codeudor', 'id_solicitud', 'id_codeudor')
                    ->using(SolicitudCodeudor::class);
    }
    
    /**
     * Obtener todos los documentos de respaldo (fotos, archivos) asociados.
     */
    public function respaldos()
    {
        return $this->hasMany(Respaldo::class, 'id_solicitud');
    }

    /**
     * Obtener el plan de pago asociado a la solicitud (Relación One-to-One).
     */
    public function planPago()
    {
        return $this->hasOne(PlanPago::class, 'id_solicitud');
    }

    public function ordenPagoReprogramacion()
    {
        // 'id_solicitud_nueva' es la llave foránea en la tabla orden_pago_reprogramaciones
        return $this->hasOne(OrdenPagoReprogramacion::class, 'id_solicitud_nueva');
    }
}