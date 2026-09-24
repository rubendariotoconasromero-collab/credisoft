<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};

return new class extends Migration {
    public function up(): void
    {
        if (DB::table('caja')->select('id_usuario')->where('estado', 1)->groupBy('id_usuario')->havingRaw('COUNT(*) > 1')->exists()
            || DB::table('desembolso')->select('id_plan_pago')->where('estado', 0)->groupBy('id_plan_pago')->havingRaw('COUNT(*) > 1')->exists()
            || DB::table('boveda')->count() > 1) {
            throw new RuntimeException('Hay duplicados financieros. Ejecute finanzas:diagnosticar y concilie antes de aplicar restricciones.');
        }
        Schema::table('caja', function (Blueprint $t) {
            $t->unsignedBigInteger('usuario_abierto')->nullable()->storedAs('CASE WHEN estado = 1 THEN id_usuario ELSE NULL END');
            $t->unique('usuario_abierto');
            $t->decimal('efectivo_contado', 12, 2)->nullable();
        });
        Schema::table('desembolso', function (Blueprint $t) {
            $t->unsignedBigInteger('plan_activo')->nullable()->storedAs('CASE WHEN estado = 0 THEN id_plan_pago ELSE NULL END');
            $t->unique('plan_activo');
        });
        Schema::table('boveda', function (Blueprint $t) { $t->unsignedInteger('unica')->storedAs('1'); $t->unique('unica'); });
        Schema::table('plan_pago', fn (Blueprint $t) => $t->decimal('tasa', 12, 6)->change());
        Schema::table('cuota', function (Blueprint $t) {
            foreach (['capital', 'interes', 'saldo_capital', 'total'] as $column) $t->decimal($column, 12, 2)->change();
            foreach (['ahorro', 'seguro'] as $column) $t->decimal($column, 12, 2)->nullable()->change();
        });
    }
    public function down(): void
    {
        Schema::table('caja', function (Blueprint $t) { $t->dropUnique(['usuario_abierto']); $t->dropColumn(['usuario_abierto', 'efectivo_contado']); });
        Schema::table('desembolso', function (Blueprint $t) { $t->dropUnique(['plan_activo']); $t->dropColumn('plan_activo'); });
        Schema::table('boveda', function (Blueprint $t) { $t->dropUnique(['unica']); $t->dropColumn('unica'); });
        // Do not truncate fractional rates or monetary precision during recovery.
    }
};
