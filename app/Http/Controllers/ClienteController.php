<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Services\ClienteService;
use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Http\Resources\ClienteResource;
use Illuminate\Http\Request; // Para el logo
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection; // Para colecciones
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ClienteController extends Controller
{
    protected ClienteService $clienteService;

    public function __construct(ClienteService $clienteService)
    {
        $this->clienteService = $clienteService;
        // Aplicar middleware de autenticación a todo el controlador
        $this->middleware('auth:sanctum');
    }

    public function index(): AnonymousResourceCollection|JsonResponse
    {
        // Gate::authorize('viewAny', Cliente::class); // Ejemplo de autorización con Policy
        if (!Auth::user()->esRol('Administrador')) { // Ejemplo simple de autorización
            return response()->json(['message' => 'No autorizado'], 403); // Devolver una respuesta JSON en lugar de una excepción
        }
        $clientes = $this->clienteService->obtenerTodos();
        return ClienteResource::collection($clientes);
    }

    public function store(StoreClienteRequest $request): JsonResponse
    {
        // La autorización ya se hizo en StoreClienteRequest
        $datosValidados = $request->validated();
        try {
            $cliente = $this->clienteService->crear($datosValidados);
            return response()->json(new ClienteResource($cliente), 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 409); // 409 Conflict para alias duplicado
        }
    }

    public function show(Cliente $cliente): ClienteResource // Route-Model Binding
    {
        // Gate::authorize('view', $cliente); // Ejemplo de Policy
        if (!Auth::user()->esRol('Administrador') && Auth::user()->id_cliente !== $cliente->id_cliente) {
            // Un usuario cliente solo puede ver su propia info (si IdCliente está en User)
            // O si es un admin
            // Esta lógica puede ser más compleja y moverse a una policy
            abort(403, 'No autorizado para ver este cliente.');
        }
        return new ClienteResource($cliente);
    }

    public function update(UpdateClienteRequest $request, Cliente $cliente): JsonResponse
    {
        // La autorización ya se hizo en UpdateClienteRequest
        $datosValidados = $request->validated();
        try {
            $clienteActualizado = $this->clienteService->actualizar($cliente, $datosValidados);
            return response()->json(new ClienteResource($clienteActualizado), 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }
    }

    public function destroy(Cliente $cliente): JsonResponse
    {
        if (!Auth::user()->esRol('Administrador')) {
            return response()->json(['message' => 'No autorizado'], 403);
        }
        // Gate::authorize('delete', $cliente); // Ejemplo de Policy
        $this->clienteService->eliminar($cliente);
        return response()->json(null, 204); // No Content
    }

    public function subirLogo(Request $request, Cliente $cliente): JsonResponse
    {
        if (!Auth::user()->esRol('Administrador')) {
            return response()->json(['message' => 'No autorizado'], 403);
        }
        // Gate::authorize('update', $cliente); // Necesita poder actualizar el cliente

        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validación del archivo
        ]);

        if ($request->hasFile('logo')) {
            $rutaLogo = $this->clienteService->actualizarLogo($cliente, $request->file('logo'));
            return response()->json([
                'message' => 'Logo subido exitosamente.',
                'ruta_logo_url' => Storage::disk('public')->url($rutaLogo)
            ], 200);
        }
        return response()->json(['message' => 'No se proporcionó archivo de logo.'], 400);
    }
}
