<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use App\Models\Repartidor;

// Añadir esta ruta
Route::get('/admin/api/repartidores-disponibles', function () {
    return Repartidor::with('usuario')
        ->where('disponible', true)
        ->whereNotNull('ultima_ubicacion_lat')
        ->whereNotNull('ultima_ubicacion_lng')
        ->get();
})->middleware(['auth']);