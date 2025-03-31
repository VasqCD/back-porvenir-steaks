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
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users');
            $table->foreignId('pedido_id')->nullable()->constrained('pedidos');
            $table->string('titulo');
            $table->text('mensaje');
            $table->enum('tipo', ['nuevo_pedido', 'pedido_en_cocina', 'pedido_en_camino', 'pedido_entregado']);
            $table->boolean('leida')->default(false);
            $table->timestamps();
            $table->softDeletes(); // Añadimos SoftDelete
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
