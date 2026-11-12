<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
    }

    public function test_devuelve_un_token_con_credenciales_validas(): void
    {
        $user = User::factory()->create();

        $datos = [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'TestingDevice',
        ];
                                                                                                                                                                                                                      
        $response = $this->postJson('/api/v1/auth/login', $datos);

        $response->assertStatus(200)->assertJsonStructure([
            'token'
        ]);
    }

    public function test_rechaza_crendenciales_invalidas(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'incorrecta',
            'device_name' => 'TestingDevice',
        ])->assertStatus(401); 
    }

    public function test_valida_que_correo_y_contrasena_sean_obligatorios(): void
    {
        $this->postJson('/api/v1/auth/login', [])->assertStatus(422);
    }
}

