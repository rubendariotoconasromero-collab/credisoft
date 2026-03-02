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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            
            // Sugerencia: Si 'name' es el usuario de login, ponle ->unique()
            $table->string('name')->unique(); 
            $table->string('personal');
            
            // Sugerencia: El email en los sistemas de Laravel suele ser único
            $table->string('email')->unique()->nullable(); 
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            
            $table->integer('estado')->default(1);
            
            // Llave foránea segura
            $table->unsignedBigInteger('id_rol');
            $table->foreign('id_rol')->references('id')->on('rol'); 
            
            // OPTIMIZACIÓN: Quitamos el default('0'). Si no hay dato, que sea NULL.
            $table->string('ci', 30)->nullable();
            $table->string('telefono', 20)->nullable();
            
            // CORRECCIÓN CRÍTICA: Quitamos el default('0') porque causaba error SQL en tipo 'date'
            $table->date('fecha_cambio_password')->nullable();
            
            $table->integer('dias_vigencia')->default(1);
            
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
