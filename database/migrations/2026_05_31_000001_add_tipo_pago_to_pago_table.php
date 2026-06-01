<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pago', function (Blueprint $table) {
            $table->string('tipo_pago', 20)->default('completo')->after('monto_condonado_mora');
        });

        // Clasificar registros existentes con heurística basada en componentes pagados
        DB::statement("
            UPDATE pago SET tipo_pago = CASE
                WHEN pago_capital = 0 AND pago_mora = 0 AND pago_interes > 0 THEN 'solo_interes'
                WHEN pago_capital = 0 AND pago_interes = 0 AND pago_mora > 0 THEN 'solo_mora'
                WHEN pago_capital = 0 AND pago_interes > 0 AND pago_mora > 0 THEN 'interes_mora'
                ELSE 'completo'
            END
        ");
    }

    public function down(): void
    {
        Schema::table('pago', function (Blueprint $table) {
            $table->dropColumn('tipo_pago');
        });
    }
};
