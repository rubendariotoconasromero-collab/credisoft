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
        Schema::table('pago', function (Blueprint $table) {
            // Desglose del 'monto_pago' (el billete que trajo el cliente)
            $table->decimal('pago_capital', 12, 2)->default(0)->after('monto_pago');
            $table->decimal('pago_interes', 12, 2)->default(0)->after('pago_capital');
            $table->decimal('pago_mora', 12, 2)->default(0)->after('pago_interes');
            
            // Aseguramos que condonaciones separadas se registren bien
            $table->decimal('monto_condonado_interes', 12, 2)->default(0)->after('monto_condonado');
            $table->decimal('monto_condonado_mora', 12, 2)->default(0)->after('monto_condonado_interes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pago', function (Blueprint $table) {
            $table->dropColumn([
                'pago_capital', 
                'pago_interes', 
                'pago_mora',
                'monto_condonado_interes',
                'monto_condonado_mora'
            ]);
        });
    }
};