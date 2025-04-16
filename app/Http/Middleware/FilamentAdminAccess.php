<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FilamentAdminAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        
        // Lista de roles permitidos
        $rolesPermitidos = ['administrador'];
        
        // Verificar si el usuario tiene un rol permitido en nuestra aplicación
        $tieneRolPermitido = in_array($user->rol ?? '', $rolesPermitidos);
        
        // Verificar si el usuario tiene un rol de Shield permitido
        $tieneRolShield = $user && ($user->hasRole('super-admin') || $user->hasRole('filament_user'));
        
        // Permitir acceso solo a roles autorizados
        if ($user && !$tieneRolPermitido && !$tieneRolShield) {
            Auth::logout();
            return redirect()->route('filament.admin.auth.login')
                ->with('error', 'Solo administradores pueden acceder al panel.');
        }
        
        return $next($request);
    }
}