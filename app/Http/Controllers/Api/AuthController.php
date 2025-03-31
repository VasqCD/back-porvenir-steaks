<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Registro de usuario
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'apellido' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'telefono' => 'nullable|string|max:20',
        ]);

        // Generar código de verificación
        $codigoVerificacion = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user = User::create([
            'name' => $request->name,
            'apellido' => $request->apellido,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'telefono' => $request->telefono,
            'rol' => 'cliente', // Por defecto es un cliente
            'codigo_verificacion' => $codigoVerificacion,
            'fecha_registro' => now(),
        ]);

        // Aquí enviarías el código de verificación al correo
        // Mail::to($user->email)->send(new VerificationCode($user));

        // Crear token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Usuario registrado exitosamente',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    // Login de usuario
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        // Actualizar última conexión
        $user->ultima_conexion = now();
        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Inicio de sesión exitoso',
            'user' => $user,
            'token' => $token,
        ]);
    }

    // Verificar código
    public function verificarCodigo(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'codigo' => 'required|string',
        ]);

        $user = User::where('email', $request->email)
            ->where('codigo_verificacion', $request->codigo)
            ->first();

        if (!$user) {
            return response()->json([
                'message' => 'Código de verificación inválido',
            ], 422);
        }

        // Verificar usuario
        $user->email_verified_at = now();
        $user->codigo_verificacion = null; // Limpiar código
        $user->save();

        return response()->json([
            'message' => 'Correo verificado exitosamente',
            'user' => $user,
        ]);
    }

    // Reenviar código
    public function reenviarCodigo(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'No se encontró usuario con ese correo',
            ], 404);
        }

        // Generar nuevo código
        $codigoVerificacion = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $user->codigo_verificacion = $codigoVerificacion;
        $user->save();

        // Enviar código por correo
        // Mail::to($user->email)->send(new VerificationCode($user));

        return response()->json([
            'message' => 'Código reenviado exitosamente',
        ]);
    }

    // Solicitar recuperación de contraseña
    public function recuperarPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'No se encontró usuario con ese correo',
            ], 404);
        }

        // Generar código temporal para recuperación
        $codigoTemporal = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $user->codigo_verificacion = $codigoTemporal;
        $user->save();

        // Enviar código por correo
        // Mail::to($user->email)->send(new RecuperacionPassword($user));

        return response()->json([
            'message' => 'Se ha enviado un código de recuperación a tu correo',
        ]);
    }

    // Cambiar contraseña con código
    public function cambiarPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'codigo' => 'required|string',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::where('email', $request->email)
            ->where('codigo_verificacion', $request->codigo)
            ->first();

        if (!$user) {
            return response()->json([
                'message' => 'Código inválido',
            ], 422);
        }

        $user->password = Hash::make($request->password);
        $user->codigo_verificacion = null; // Limpiar código
        $user->save();

        return response()->json([
            'message' => 'Contraseña actualizada exitosamente',
        ]);
    }

    // Obtener perfil de usuario
    public function perfil(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }

    // Actualizar perfil de usuario
    public function actualizarPerfil(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'apellido' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
        ]);

        $user->update($request->only(['name', 'apellido', 'telefono']));

        // Si se envía una foto
        if ($request->hasFile('foto_perfil')) {
            $request->validate([
                'foto_perfil' => 'image|mimes:jpeg,png,jpg|max:2048',
            ]);

            $path = $request->file('foto_perfil')->store('perfiles', 'public');
            $user->foto_perfil = $path;
            $user->save();
        }

        return response()->json([
            'message' => 'Perfil actualizado exitosamente',
            'user' => $user,
        ]);
    }

    // Cerrar sesión
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada exitosamente',
        ]);
    }
}
