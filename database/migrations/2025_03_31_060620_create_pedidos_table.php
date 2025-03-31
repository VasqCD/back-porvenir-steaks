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
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users');
            $table->foreignId('ubicacion_id')->constrained('ubicaciones');
            $table->enum('estado', ['pendiente', 'en_cocina', 'en_camino', 'entregado', 'cancelado'])->default('pendiente');
            $table->decimal('total', 10, 2);
            $table->timestamp('fecha_pedido');
            $table->timestamp('fecha_entrega')->nullable();
            $table->foreignId('repartidor_id')->nullable()->constrained('repartidores');
            $table->integer('calificacion')->nullable();
            $table->text('comentario_calificacion')->nullable();
            $table->timestamps();
            $table->softDeletes(); // Añadimos SoftDelete
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
