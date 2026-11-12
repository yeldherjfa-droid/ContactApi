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

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            return response()->json([
                'message' => 'Las credenciales no son correctas.',
                'errors' => [
                    'email' => ['Las credenciales no son correctas.'],
                ],
            ], 401);
        }

        if ( $user->is_active) {
            abort(403, 'La cuenta está desactivada.');
        }

        $token = $user->createToken($request->validated('device_name'))->plainTextToken;

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
