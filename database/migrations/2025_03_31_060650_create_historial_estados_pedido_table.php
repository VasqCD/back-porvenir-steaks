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
        Schema::create('historial_estados_pedido', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos');
            $table->string('estado_anterior');
            $table->string('estado_nuevo');
            $table->timestamp('fecha_cambio');
            $table->foreignId('usuario_id')->constrained('users'); // Usuario que realizó el cambio
            $table->timestamps();
            $table->softDeletes(); // Añadimos SoftDelete
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_estados_pedido');
    }
};
