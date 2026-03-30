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
        Schema::create('boveda', function (Blueprint $table) {
            $table->id();
            $table->decimal('saldo_actual', 12, 2)->default(0.00);
            $table->datetime('fecha_apertura');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boveda');
    }
};
