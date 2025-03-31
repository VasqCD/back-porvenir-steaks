<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Resetear roles y permisos en caché
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permisos
        // Permisos para productos
        Permission::create(['name' => 'ver productos']);
        Permission::create(['name' => 'crear productos']);
        Permission::create(['name' => 'editar productos']);
        Permission::create(['name' => 'eliminar productos']);

        // Permisos para categorías
        Permission::create(['name' => 'ver categorias']);
        Permission::create(['name' => 'crear categorias']);
        Permission::create(['name' => 'editar categorias']);
        Permission::create(['name' => 'eliminar categorias']);

        // Permisos para pedidos
        Permission::create(['name' => 'ver pedidos']);
        Permission::create(['name' => 'crear pedidos']);
        Permission::create(['name' => 'actualizar estado pedidos']);
        Permission::create(['name' => 'asignar repartidor']);
        Permission::create(['name' => 'calificar pedidos']);

        // Permisos para repartidores
        Permission::create(['name' => 'ver repartidores']);
        Permission::create(['name' => 'gestionar repartidores']);

        // Permisos para ubicaciones
        Permission::create(['name' => 'ver ubicaciones']);
        Permission::create(['name' => 'gestionar ubicaciones']);

        // Crear roles y asignar permisos
        // Rol administrador
        $roleAdmin = Role::create(['name' => 'administrador']);
        $roleAdmin->givePermissionTo(Permission::all());

        // Rol repartidor
        $roleRepartidor = Role::create(['name' => 'repartidor']);
        $roleRepartidor->givePermissionTo([
            'ver productos',
            'ver categorias',
            'ver pedidos',
            'actualizar estado pedidos',
            'ver ubicaciones',
        ]);

        // Rol cliente
        $roleCliente = Role::create(['name' => 'cliente']);
        $roleCliente->givePermissionTo([
            'ver productos',
            'ver categorias',
            'ver pedidos',
            'crear pedidos',
            'calificar pedidos',
            'ver ubicaciones',
            'gestionar ubicaciones',
        ]);
    }
}
