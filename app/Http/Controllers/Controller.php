<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="GEO Encuestas API",
 *      description="Documentación de la API para el Sistema de Gestión de Encuestas GEO. Utiliza autenticación por Token Bearer (Sanctum) para los endpoints protegidos.",
 *      @OA\Contact(
 *          email="tu_email_de_soporte@example.com",
 *          name="Equipo de Desarrollo GEO"
 *      ),
 *      @OA\License(
 *          name="Tu Licencia (ej. MIT)",
 *          url="Enlace a tu licencia si la tienes"
 *      )
 * )
 *
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST, 
 *      description="Servidor API Principal (Local/Producción)"
 * )
 *
 * @OA\SecurityScheme(
 *      securityScheme="bearerAuth",
 *      type="http",
 *      scheme="bearer",
 *      bearerFormat="JWT",
 *      description="Ingresar el token JWT con el prefijo Bearer. Ejemplo: 'Bearer {token}'"
 * )
 *
 * @OA\Tag(name="Autenticación", description="Endpoints para registro y login de usuarios")
 * @OA\Tag(name="Usuarios", description="Operaciones sobre usuarios...")
 * @OA\Tag(name="Clientes", description="Gestión de Clientes")
 * @OA\Tag(name="Encuestas", description="Gestión de Encuestas, Secciones, Preguntas y Opciones")
 * @OA\Tag(name="Respuestas", description="Endpoints para responder encuestas")
 * @OA\Tag(name="Reportes", description="Endpoints para obtener datos de reportes y exportaciones")
 * @OA\Tag(name="Configuración del Sistema", description="Gestión de tipos de pregunta y parámetros del sistema")
 * 
 * @OA\Schema(
 *   schema="ValidationErrorResponse",
 *   title="Validation Error Response",
 *   description="Respuesta estándar para errores de validación (422)",
 *   @OA\Property(property="message", type="string", example="Error de validación de datos."),
 *   @OA\Property(
 *     property="errors",
 *     type="object",
 *     description="Objeto con los errores de validación por campo",
 *     example={"email": {"El correo electrónico ya está registrado."}, "password": {"La contraseña debe tener al menos 8 caracteres."}}
 *   )
 * )
 */
abstract class Controller extends BaseController // Es abstracto porque 
{
    use AuthorizesRequests, ValidatesRequests;
}
