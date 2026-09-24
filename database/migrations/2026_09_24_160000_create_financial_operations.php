<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};

return new class extends Migration {
    public function up(): void
    {
        Schema::create('operaciones_financieras', function (Blueprint $t) {
            $t->id();
            $t->uuid('codigo')->unique();
            $t->foreignId('id_usuario')->constrained('users');
            $t->string('clave', 80);
            $t->string('huella', 64);
            $t->string('tipo', 40);
            $t->foreignId('id_caja')->nullable()->constrained('caja');
            $t->foreignId('id_plan_pago')->nullable()->constrained('plan_pago');
            $t->foreignId('id_boveda')->nullable()->constrained('boveda');
            $t->date('fecha_efectiva');
            $t->string('direccion', 10)->nullable();
            $t->boolean('interno')->default(false);
            $t->decimal('monto', 12, 2)->default(0);
            $t->decimal('cambio', 12, 2)->default(0);
            $t->json('partes')->nullable();
            $t->json('antes')->nullable();
            $t->json('resultado')->nullable();
            $t->timestamp('anulado_at')->nullable();
            $t->foreignId('anulado_por')->nullable()->constrained('users');
            $t->text('motivo_anulacion')->nullable();
            $t->timestamps();
            $t->unique(['id_usuario', 'clave']);
            $t->index(['id_caja', 'anulado_at']);
        });
        foreach (['pago', 'ingreso', 'egreso', 'desembolso', 'pago_administrativo', 'movimientos_caja', 'movimientos_boveda'] as $table) {
            Schema::table($table, fn (Blueprint $t) => $t->foreignId('id_operacion')->nullable()->constrained('operaciones_financieras'));
        }
        foreach (['condonar' => 'Condonar interés y multa', 'anular_finanzas' => 'Anular operaciones financieras propias', 'pagos_retroactivos' => 'Registrar pagos con fecha efectiva anterior'] as $name => $description) {
            DB::table('permiso')->updateOrInsert(['nombre' => $name], ['descripcion' => $description, 'estado' => 1]);
            $role = DB::table('rol')->where('nombre', 'Administrador')->value('id');
            if ($role) DB::table('permiso_rol')->updateOrInsert(['id_rol' => $role, 'id_permiso' => DB::table('permiso')->where('nombre', $name)->value('id')], []);
        }
    }

    public function down(): void
    {
        foreach (['pago', 'ingreso', 'egreso', 'desembolso', 'pago_administrativo', 'movimientos_caja', 'movimientos_boveda'] as $table) {
            Schema::table($table, fn (Blueprint $t) => $t->dropConstrainedForeignId('id_operacion'));
        }
        Schema::dropIfExists('operaciones_financieras');
    }
};
