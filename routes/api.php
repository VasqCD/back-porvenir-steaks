<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductoController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\PedidoController;
use App\Http\Controllers\Api\UbicacionController;
use App\Http\Controllers\Api\RepartidorController;
use App\Http\Controllers\Api\NotificacionController;
use App\Http\Controllers\Api\DetallePedidoController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Rutas públicas
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verificar-codigo', [AuthController::class, 'verificarCodigo']);
Route::post('/reenviar-codigo', [AuthController::class, 'reenviarCodigo']);
Route::post('/recuperar-password', [AuthController::class, 'recuperarPassword']);
Route::post('/cambiar-password', [AuthController::class, 'cambiarPassword']);

// Categorías y productos (acceso público)
Route::get('/categorias', [CategoriaController::class, 'index']);
Route::get('/categorias/{id}', [CategoriaController::class, 'show']);
Route::get('/productos', [ProductoController::class, 'index']);
Route::get('/productos/{id}', [ProductoController::class, 'show']);

// Rutas protegidas
Route::middleware('auth:sanctum')->group(function () {
    // Rutas para FCM
    Route::post('/fcm/register', [FcmController::class, 'registerToken']);
    Route::post('/fcm/test', [FcmController::class, 'testNotification']);

    // Perfil de usuario
    Route::get('/user', [AuthController::class, 'perfil']);
    Route::post('/user/update', [AuthController::class, 'actualizarPerfil']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Ubicaciones - solo usuarios con permiso
    Route::middleware('permission:gestionar ubicaciones')->group(function () {
        Route::apiResource('ubicaciones', UbicacionController::class)->except(['index', 'show']);
    });
    Route::middleware('permission:ver ubicaciones')->group(function () {
        Route::get('ubicaciones', [UbicacionController::class, 'index']);
        Route::get('ubicaciones/{id}', [UbicacionController::class, 'show']);
    });

    // Pedidos
    Route::middleware('permission:crear pedidos')->post('/pedidos', [PedidoController::class, 'store']);
    Route::middleware('permission:ver pedidos')->get('/pedidos', [PedidoController::class, 'index']);
    Route::middleware('permission:ver pedidos')->get('/pedidos/{id}', [PedidoController::class, 'show']);
    Route::middleware('permission:actualizar estado pedidos')->post('/pedidos/{id}/estado', [PedidoController::class, 'actualizarEstado']);
    Route::middleware('permission:calificar pedidos')->post('/pedidos/{id}/calificar', [PedidoController::class, 'calificar']);
    Route::middleware('permission:ver pedidos')->get('/pedidos-pendientes', [PedidoController::class, 'pendientes']);
    Route::middleware('permission:asignar repartidor')->post('/pedidos/{id}/asignar-repartidor', [PedidoController::class, 'asignarRepartidor']);

    // Notificaciones
    Route::get('/notificaciones', [NotificacionController::class, 'index']);
    Route::post('/notificaciones/{id}/marcar-leida', [NotificacionController::class, 'marcarLeida']);
    Route::post('/notificaciones/marcar-todas-leidas', [NotificacionController::class, 'marcarTodasLeidas']);

    // Rutas para administradores
    Route::middleware('role:administrador')->group(function () {
        Route::apiResource('categorias', CategoriaController::class)->except(['index', 'show']);
        Route::apiResource('productos', ProductoController::class)->except(['index', 'show']);
        Route::apiResource('repartidores', RepartidorController::class);
    });

    // Rutas para repartidores
    Route::middleware(['auth:sanctum', 'role:repartidor'])->group(function () {
        Route::post('/repartidor/actualizar-ubicacion', [RepartidorController::class, 'actualizarUbicacion']);
        Route::post('/repartidor/cambiar-disponibilidad', [RepartidorController::class, 'cambiarDisponibilidad']);
    });

});
