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
        Schema::table('cuota', function (Blueprint $table) {
            // Agregamos las bolsas de lo que se va pagando. 
            // Las colocamos después del campo 'total' para mantener el orden visual.
            $table->decimal('capital_pagado', 10, 2)->default(0)->after('total');
            $table->decimal('interes_pagado', 10, 2)->default(0)->after('capital_pagado');
            $table->decimal('mora_pagada', 10, 2)->default(0)->after('interes_pagado');
            
            // Nota sobre el 'estado': 
            // 0: Anulado
            // 1: Pendiente
            // 2: Pagado Total
            // 3: Pagado Parcialmente (Nuevo estado lógico)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cuota', function (Blueprint $table) {
            $table->dropColumn(['capital_pagado', 'interes_pagado', 'mora_pagada']);
        });
    }
};