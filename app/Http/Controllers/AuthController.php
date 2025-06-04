<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;

/**
 * @OA\Tag(
 *     name="Autenticación",
 *     description="Endpoints para registro, login y operaciones de usuario autenticado"
 * )
 *
 * @OA\Schema(
 *   schema="LoginRequest",
 *   title="LoginRequest",
 *   description="Datos necesarios para iniciar sesión",
 *   required={"email","password"},
 *   @OA\Property(property="email",    type="string", format="email",    example="usuario@example.com", description="Correo electrónico del usuario"),
 *   @OA\Property(property="password", type="string", format="password", example="password",            description="Contraseña del usuario")
 * )
 *
 * @OA\Schema(
 *   schema="UserResource",
 *   title="UserResource",
 *   description="Esquema de representación de usuario autenticado",
 *   @OA\Property(property="id",    type="integer", format="int64", example=1),
 *   @OA\Property(property="name",  type="string",                example="Juan Pérez"),
 *   @OA\Property(property="email", type="string", format="email", example="usuario@example.com")
 * )
 *
 * @OA\Schema(
 *   schema="LoginResponse",
 *   title="LoginResponse",
 *   description="Respuesta al iniciar sesión",
 *   @OA\Property(property="token",      type="string", example="1|abcdef123456", description="Token de acceso Bearer"),
 *   @OA\Property(property="user",       ref="#/components/schemas/UserResource")
 * )
 */
class AuthController extends Controller
{
    // Registro de nuevo usuario (opcional, o elimínalo si no permites self-registration)
    public function register(Request $request)
    {
        $request->validate([
            'nombre_completo' => 'required|string|max:150',
            'email'           => 'required|email|unique:usuarios,email',
            'password'        => 'required|string|min:8|confirmed',
            // Si necesitas id_rol e id_cliente al registro (por ejemplo: solo admin crea usuarios), valida aquí
        ]);

        $user = User::create([
            'nombre_completo'  => $request->nombre_completo,
            'email'            => $request->email,
            'password'         => Hash::make($request->password),
            // Si registras con rol “Cliente”: 'id_rol' => Role::where('nombre_rol', 'Cliente')->first()->id_rol
            // 'id_cliente'      => $request->id_cliente (si aplica),
            'activo'           => true,
            'email_verified_at' => null, // Se enviará correo de verificación
        ]);

        // Disparar el evento para correo de verificación
        event(new Registered($user));

        return response()->json([
            'message' => 'Usuario creado. Revisa tu correo para verificar tu cuenta.',
            'user'    => [
                'id'    => $user->id,
                'email' => $user->email
            ]
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     operationId="loginUser",
     *     tags={"Autenticación"},
     *     summary="Iniciar sesión",
     *     description="Autentica un usuario y devuelve un token Bearer junto con información del usuario.",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Credenciales del usuario",
     *         @OA\JsonContent(ref="#/components/schemas/LoginRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login exitoso",
     *         @OA\JsonContent(ref="#/components/schemas/LoginResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Credenciales inválidas",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Credenciales inválidas.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Usuario no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Usuario no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     )
     * )
     */
    public function login(Request $req)
    {
        // 1) Validar entrada
        $data = $req->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);

        // 2) Obtener usuario por email
        $user = User::where('email', $data['email'])->first();
        if (! $user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        // 3) Verificar contraseña
        if (! Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }

        // 4) Generar token de Sanctum
        $token = $user->createToken('geo-token')->plainTextToken;

        // 5) Devolver token y datos de usuario
        return response()->json([
            'token' => $token,
            'user'  => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     operationId="logoutUser",
     *     tags={"Autenticación"},
     *     summary="Cerrar sesión",
     *     description="Invalida el token Bearer del usuario autenticado.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Sesión cerrada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Desconectado correctamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No autenticado")
     *         )
     *     )
     * )
     */
    public function logout(Request $req)
    {
        // Eliminar el token con el que se hizo la petición
        $req->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada.']);
    }

    /**
     * @OA\Get(
     *     path="/api/user",
     *     operationId="getAuthenticatedUser",
     *     tags={"Autenticación"},
     *     summary="Obtener usuario autenticado",
     *     description="Devuelve los datos del usuario actualmente autenticado.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Operación exitosa",
     *         @OA\JsonContent(ref="#/components/schemas/UserResource")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No autenticado")
     *         )
     *     )
     * )
     */
    public function me(Request $req)
    {
        // Devuelve los datos del usuario autenticado
        $user = $req->user()->load(['role', 'cliente']);
        return response()->json([
            'id'              => $user->id,
            'nombre_completo' => $user->nombre_completo,
            'email'           => $user->email,
            'id_rol'          => $user->id_rol,
            'rol'             => $user->role?->nombre_rol,
            'id_cliente'      => $user->id_cliente,
            'email_verified_at' => $user->email_verified_at,
        ]);
    }
}
