<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // La API no tiene página de login: sin token se responde 401, no se redirige.
        $middleware->redirectGuestsTo(fn () => null);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
            return $request->is('api/*') || $request->expectsJson();
        });

        // Sin token o con token inválido: 401 en JSON.
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            return response()->json(['message' => 'No autenticado.'], 401);
        });

        // Errores HTTP de la API: siempre un objeto JSON {"message": "..."}.
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            $message = match ($e->getStatusCode()) {
                403 => 'usted no tiene los permisos necesarios para ver este recurso',
                404 => 'recurso no encontrado',
                default => $e->getMessage(),
            };

            return response()->json(['message' => $message], $e->getStatusCode(), $e->getHeaders());
        });
    })->create();
