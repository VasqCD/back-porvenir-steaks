<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Obtener la definición actual de la columna enum
        $enumString = DB::selectOne("SHOW COLUMNS FROM notificaciones WHERE Field = 'tipo'")->Type;
        
        // Verificar si ya contiene solicitud_repartidor
        if (strpos($enumString, 'solicitud_repartidor') === false) {
            // Eliminar prefijo enum( y sufijo )
            $enumString = substr($enumString, 5, -1);
            
            // Añadir nuevo valor
            $newEnumString = $enumString . ",'solicitud_repartidor'";
            
            // Modificar la columna
            DB::statement("ALTER TABLE notificaciones MODIFY tipo ENUM($newEnumString) NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Obtener la definición actual
        $enumString = DB::selectOne("SHOW COLUMNS FROM notificaciones WHERE Field = 'tipo'")->Type;
        
        // Eliminar prefijo enum( y sufijo )
        $enumString = substr($enumString, 5, -1);
        
        // Reemplazar 'solicitud_repartidor' en la cadena
        $enumString = str_replace(",'solicitud_repartidor'", "", $enumString);
        $enumString = str_replace("'solicitud_repartidor',", "", $enumString);
        $enumString = str_replace("'solicitud_repartidor'", "", $enumString);
        
        // Modificar la columna
        DB::statement("ALTER TABLE notificaciones MODIFY tipo ENUM($enumString) NOT NULL");
    }
};