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
            $table->string('name');
            $table->string('personal');
            $table->string('email')->nullable();
            $table->integer('estado')->default(1);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            
            // Asumimos que la tabla 'rol' ya existe
            $table->unsignedBigInteger('id_rol');
            $table->foreign('id_rol')->references('id')->on('rol'); 

            $table->rememberToken();
            $table->string('ci', 30)->nullable()->default('0');
            $table->string('telefono', 20)->nullable()->default('0');
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
