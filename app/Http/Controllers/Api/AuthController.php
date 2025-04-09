<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * @group Autenticación y Gestión de Usuarios
 *
 * APIs para gestionar autenticación, registro y perfil de usuarios
 */
class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Registro de usuario
     *
     * Registra un nuevo usuario en el sistema y envía un código de verificación al correo electrónico.
     * Por defecto, todo usuario nuevo es registrado con rol 'cliente'.
     *
     * @bodyParam name string required Nombre del usuario. Example: Juan Pérez
     * @bodyParam apellido string nullable Apellido del usuario. Example: González
     * @bodyParam email string required Email del usuario (debe ser único). Example: usuario@ejemplo.com
     * @bodyParam password string required Contraseña del usuario (mínimo 8 caracteres). Example: Password123
     * @bodyParam telefono string nullable Número telefónico del usuario. Example: +504 9999-9999
     *
     * @response 201 {
     *    "message": "Usuario registrado exitosamente",
     *    "user": {
     *        "id": 1,
     *        "name": "Juan Pérez",
     *        "apellido": "González",
     *        "email": "usuario@ejemplo.com",
     *        "telefono": "+504 9999-9999",
     *        "rol": "cliente",
     *        "fecha_registro": "2025-04-02T10:30:00.000000Z",
     *        "updated_at": "2025-04-02T10:30:00.000000Z",
     *        "created_at": "2025-04-02T10:30:00.000000Z"
     *    },
     *    "token": "1|abcdefghijklmnopqrstuvwxyz"
     * }
     *
     * @response 422 {
     *    "message": "The given data was invalid.",
     *    "errors": {
     *        "email": ["El correo electrónico ya ha sido registrado."]
     *    }
     * }
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'apellido' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'telefono' => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'name' => $request->name,
            'apellido' => $request->apellido,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'telefono' => $request->telefono,
            'rol' => 'cliente', // Por defecto es un cliente
            'fecha_registro' => now(),
        ]);

        // Asignar rol usando Spatie
        $user->assignRole('cliente');

        // Generar y enviar código de verificación
        $this->authService->enviarCodigoVerificacion($user);

        // Crear token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Usuario registrado exitosamente',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    /**
     * Login de usuario
     *
     * Autentica a un usuario con su email y contraseña.
     * Actualiza la fecha de última conexión del usuario.
     *
     * @bodyParam email string required Email del usuario. Example: usuario@ejemplo.com
     * @bodyParam password string required Contraseña del usuario. Example: Password123
     *
     * @response {
     *    "message": "Inicio de sesión exitoso",
     *    "user": {
     *        "id": 1,
     *        "name": "Juan Pérez",
     *        "apellido": "González",
     *        "email": "usuario@ejemplo.com",
     *        "telefono": "+504 9999-9999",
     *        "rol": "cliente",
     *        "ultima_conexion": "2025-04-02T11:45:00.000000Z"
     *    },
     *    "token": "1|abcdefghijklmnopqrstuvwxyz"
     * }
     *
     * @response 422 {
     *    "message": "The given data was invalid.",
     *    "errors": {
     *        "email": ["Las credenciales proporcionadas son incorrectas."]
     *    }
     * }
     * 
     * @response 403 {
     *   "message": "Debe verificar su correo electrónico antes de iniciar sesión.",
     *  "verification_required": true,
     * 
     */
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

        // Verificar si el email ha sido verificado
        if ($user->email_verified_at === null) {
            return response()->json([
                'message' => 'Debe verificar su correo electrónico antes de iniciar sesión.',
                'verification_required' => true,
                'email' => $user->email
            ], 403);
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

    /**
     * Verificar código
     *
     * Verifica el código enviado al correo electrónico del usuario durante el registro.
     *
     * @bodyParam email string required Email del usuario. Example: usuario@ejemplo.com
     * @bodyParam codigo string required Código de verificación recibido por email. Example: 123456
     *
     * @response {
     *    "message": "Correo verificado exitosamente",
     *    "user": {
     *        "id": 1,
     *        "name": "Juan Pérez",
     *        "email": "usuario@ejemplo.com",
     *        "email_verified_at": "2025-04-02T11:50:00.000000Z"
     *    }
     * }
     *
     * @response 404 {
     *    "message": "No se encontró usuario con ese correo"
     * }
     *
     * @response 422 {
     *    "message": "Código de verificación inválido"
     * }
     */
    public function verificarCodigo(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'codigo' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'No se encontró usuario con ese correo',
            ], 404);
        }

        $verificado = $this->authService->verificarCodigo($user, $request->codigo);

        if (!$verificado) {
            return response()->json([
                'message' => 'Código de verificación inválido',
            ], 422);
        }

        return response()->json([
            'message' => 'Correo verificado exitosamente',
            'user' => $user,
        ]);
    }

    /**
     * Reenviar código de verificación
     *
     * Reenvía el código de verificación al correo electrónico del usuario.
     *
     * @bodyParam email string required Email del usuario. Example: usuario@ejemplo.com
     *
     * @response {
     *    "message": "Código reenviado exitosamente"
     * }
     *
     * @response 404 {
     *    "message": "No se encontró usuario con ese correo"
     * }
     */
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

        $this->authService->enviarCodigoVerificacion($user);

        return response()->json([
            'message' => 'Código reenviado exitosamente',
        ]);
    }

    /**
     * Reenviar código de verificación
     *
     * Reenvía el código de verificación al correo electrónico del usuario.
     *
     * @bodyParam email string required Email del usuario. Example: usuario@ejemplo.com
     *
     * @response {
     *    "message": "Código reenviado exitosamente"
     * }
     *
     * @response 404 {
     *    "message": "No se encontró usuario con ese correo"
     * }
     */
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

        $this->authService->enviarCodigoRecuperacion($user);

        return response()->json([
            'message' => 'Se ha enviado un código de recuperación a tu correo',
        ]);
    }

    /**
     * Cambiar contraseña con código
     *
     * Cambia la contraseña del usuario utilizando el código de recuperación enviado por email.
     *
     * @bodyParam email string required Email del usuario. Example: usuario@ejemplo.com
     * @bodyParam codigo string required Código de recuperación recibido por email. Example: 123456
     * @bodyParam password string required Nueva contraseña (mínimo 8 caracteres). Example: NuevaPassword123
     * @bodyParam password_confirmation string required Confirmación de la nueva contraseña. Example: NuevaPassword123
     *
     * @response {
     *    "message": "Contraseña actualizada exitosamente"
     * }
     *
     * @response 422 {
     *    "message": "Código inválido"
     * }
     */
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

    /**
     * Obtener perfil de usuario
     *
     * Obtiene la información del perfil del usuario autenticado.
     *
     * @response {
     *    "user": {
     *        "id": 1,
     *        "name": "Juan Pérez",
     *        "apellido": "González",
     *        "email": "usuario@ejemplo.com",
     *        "telefono": "+504 9999-9999",
     *        "rol": "cliente",
     *        "foto_perfil": "perfiles/usuario1.jpg",
     *        "fecha_registro": "2025-04-01T10:30:00.000000Z",
     *        "ultima_conexion": "2025-04-02T11:45:00.000000Z"
     *    }
     * }
     *
     * @authenticated
     */
    public function perfil(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }

    /**
     * Actualizar perfil de usuario
     *
     * Actualiza la información del perfil del usuario autenticado.
     *
     * @bodyParam name string sometimes Nombre del usuario. Example: Juan Carlos
     * @bodyParam apellido string nullable Apellido del usuario. Example: Pérez González
     * @bodyParam telefono string nullable Número telefónico del usuario. Example: +504 8888-8888
     * @bodyParam foto_perfil file nullable Foto de perfil (jpeg,png,jpg máx: 2MB).
     *
     * @response {
     *    "message": "Perfil actualizado exitosamente",
     *    "user": {
     *        "id": 1,
     *        "name": "Juan Carlos",
     *        "apellido": "Pérez González",
     *        "email": "usuario@ejemplo.com",
     *        "telefono": "+504 8888-8888",
     *        "foto_perfil": "perfiles/usuario1_actualizado.jpg"
     *    }
     * }
     *
     * @authenticated
     */
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

    /**
     * Cerrar sesión
     *
     * Cierra la sesión del usuario eliminando el token de acceso actual.
     *
     * @response {
     *    "message": "Sesión cerrada exitosamente"
     * }
     *
     * @authenticated
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada exitosamente',
        ]);
    }
}
