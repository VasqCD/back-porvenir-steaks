<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AgregarRepartidorAsignadoATipoNotificacion extends Migration
{
    /**
     * Run the migration.
     */
    public function up(): void
    {
        // Para MySQL podemos alterar la columna ENUM directamente
        if (DB::connection()->getDriverName() === 'mysql') {
            // Primero necesitamos obtener los valores actuales del enum
            $tablePrefix = DB::getTablePrefix();
            $tableName = 'notificaciones';
            $columnName = 'tipo';
            
            $columnInfo = DB::select("SHOW COLUMNS FROM {$tablePrefix}{$tableName} WHERE Field = '{$columnName}'");
            
            if (isset($columnInfo[0])) {
                $enumString = $columnInfo[0]->Type;
                // Extraer valores entre paréntesis, ejemplo: enum('valor1','valor2')
                preg_match('/^enum\((.*)\)$/', $enumString, $matches);
                
                if (isset($matches[1])) {
                    $enumValues = str_getcsv($matches[1], ',', "'");
                    
                    // Verificar si el valor 'repartidor_asignado' ya existe
                    if (!in_array('repartidor_asignado', $enumValues)) {
                        // Añadir el nuevo valor
                        $enumValues[] = 'repartidor_asignado';
                        
                        // Construir nueva definición de ENUM
                        $newEnumValuesString = "'" . implode("','", $enumValues) . "'";
                        
                        // Ejecutar ALTER TABLE
                        DB::statement("ALTER TABLE {$tablePrefix}{$tableName} MODIFY COLUMN {$columnName} ENUM({$newEnumValuesString})");
                    }
                }
            }
        } else {
            // Para otros motores como PostgreSQL, SQLite, etc. tendrías que usar un enfoque diferente
            throw new \Exception('Esta migración solo está implementada para MySQL. Para otros motores, ajusta el código.');
        }
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        // Para MySQL podemos revertir el cambio
        if (DB::connection()->getDriverName() === 'mysql') {
            $tablePrefix = DB::getTablePrefix();
            $tableName = 'notificaciones';
            $columnName = 'tipo';
            
            $columnInfo = DB::select("SHOW COLUMNS FROM {$tablePrefix}{$tableName} WHERE Field = '{$columnName}'");
            
            if (isset($columnInfo[0])) {
                $enumString = $columnInfo[0]->Type;
                preg_match('/^enum\((.*)\)$/', $enumString, $matches);
                
                if (isset($matches[1])) {
                    $enumValues = str_getcsv($matches[1], ',', "'");
                    
                    // Eliminar 'repartidor_asignado' del array
                    $enumValues = array_filter($enumValues, function ($value) {
                        return $value !== 'repartidor_asignado';
                    });
                    
                    // Construir nueva definición de ENUM
                    $newEnumValuesString = "'" . implode("','", $enumValues) . "'";
                    
                    // Ejecutar ALTER TABLE
                    DB::statement("ALTER TABLE {$tablePrefix}{$tableName} MODIFY COLUMN {$columnName} ENUM({$newEnumValuesString})");
                }
            }
        }
    }
}