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
        Schema::create('repartidores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users')->unique();
            $table->boolean('disponible')->default(true);
            $table->decimal('ultima_ubicacion_lat', 10, 8)->nullable();
            $table->decimal('ultima_ubicacion_lng', 11, 8)->nullable();
            $table->timestamp('ultima_actualizacion')->nullable();
            $table->timestamps();
            $table->softDeletes(); // Añadimos SoftDelete
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repartidores');
    }
};
