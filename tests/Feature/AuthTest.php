<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_devuelve_un_token_con_credenciales_validas(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk()->assertJsonStructure(['token', 'user' => ['id', 'name', 'email']]);
    }

    public function test_rechaza_credenciales_invalidas(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'incorrecta',
        ])->assertStatus(401);
    }

    public function test_valida_que_correo_y_contrasena_sean_obligatorios(): void
    {
        $this->postJson('/api/v1/login', [])->assertStatus(422);
    }
}