<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Inicia sesión y entrega un token para la app.
     *
     * @unauthenticated
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->validated('email'))->first();

        // CORRECCIÓN 1: Retornamos un 401 explícito si las credenciales fallan
        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            return response()->json([
                'message' => 'Las credenciales no son correctas.',
                'errors' => [
                    'email' => ['Las credenciales no son correctas.']
                ]
            ], 401); 
        }

        // CORRECCIÓN 2: Eliminamos el corchete extra que rompía el flujo hacia abajo
        if (! $user->is_active) {
            abort(403, 'La cuenta está desactivada.');
        }

        $token = $user->createToken($request->validated('device_name'))->plainTextToken;

        // CORRECCIÓN 3: Ajustamos la respuesta para que incluya exactamente la estructura que busca tu test ('token' y 'user')
        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ], 200);
    }

    /**
     * Perfil del usuario autenticado.
     */
    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    /**
     * Cierra la sesión y revoca el token actual.
     */
    public function logout(Request $request): Response
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }
}
