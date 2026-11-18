<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    public function test_devuelve_un_token_con_credenciales_validas(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'TestingDevice',
        ])
            ->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email']]);
    }

    public function test_rechaza_credenciales_invalidas(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'incorrecta',
            'device_name' => 'TestingDevice',
        ])
            ->assertStatus(401)
            ->assertExactJson(['message' => 'Las credenciales no son correctas.']);
    }

    public function test_valida_que_correo_y_contrasena_sean_obligatorios(): void
    {
        $this->postJson('/api/v1/auth/login', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password', 'device_name']);
    }

    public function test_logout_borra_el_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/auth/logout')
            ->assertOk()
            ->assertExactJson(['message' => 'Sesión cerrada correctamente.']);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_sin_token_responde_401_en_json(): void
    {
        $this->postJson('/api/v1/auth/logout')
            ->assertStatus(401)
            ->assertExactJson(['message' => 'No autenticado.']);
    }

    public function test_limita_a_5_intentos_de_login_por_minuto(): void
    {
        $this->withMiddleware(ThrottleRequests::class);
        $datos = ['email' => 'x@example.com', 'password' => 'mala', 'device_name' => 'test'];

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login', $datos)->assertStatus(401);
        }

        $this->postJson('/api/v1/auth/login', $datos)->assertStatus(429);
    }

    public function test_un_usuario_autenticado_puede_usar_rutas_protegidas(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/v1/profile')->assertOk();
    }
}
