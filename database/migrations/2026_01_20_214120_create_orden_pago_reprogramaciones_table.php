<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orden_pago_reprogramaciones', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            // 1. La nueva solicitud de reprogramación que se está creando
            $table->unsignedBigInteger('id_solicitud_nueva'); 
            // 2. El plan de pagos antiguo del cual provienen los intereses/multas
            $table->unsignedBigInteger('id_plan_pago_origen'); 
            
            // Montos Base (Lo que sumaste de la tabla)
            $table->decimal('monto_interes_calculado', 10, 2)->default(0);
            $table->decimal('monto_mora_calculado', 10, 2)->default(0);
            
            // Condonaciones
            $table->boolean('se_condono_interes')->default(false);
            $table->decimal('monto_condonado_interes', 10, 2)->default(0);
            
            $table->boolean('se_condono_mora')->default(false);
            $table->decimal('monto_condonado_mora', 10, 2)->default(0);
            
            $table->string('motivo_condonacion')->nullable(); // Descripción opcional
            
            // El total final que el cajero debe cobrar
            // Formula: (Interés Calc + Mora Calc) - (Cond. Interés + Cond. Mora)
            $table->decimal('total_a_pagar', 10, 2); 

            // Detalle JSON: Guardamos los IDs de las cuotas seleccionadas [15, 16, 17]
            // Para saber qué cuotas específicas mató este pago
            $table->json('ids_cuotas_afectadas')->nullable(); 

            // Estados de la Orden
            // 0: NO DISPONIBLE (Creada, pero la reprogramación aun no se aprueba)
            // 1: POR PAGAR (La reprogramación se aprobó, el cliente debe pasar por caja)
            // 2: PAGADO (El dinero ingresó a caja)
            // 3: ANULADO (Se rechazó la reprogramación)
            $table->tinyInteger('estado')->default(0);

            $table->timestamps();
            
            // Foreign Keys (Ajusta los nombres de tus tablas si son diferentes)
            $table->foreign('id_solicitud_nueva')->references('id')->on('solicitud')->onDelete('cascade');
            $table->foreign('id_plan_pago_origen')->references('id')->on('plan_pago')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orden_pago_reprogramaciones');
    }
};
