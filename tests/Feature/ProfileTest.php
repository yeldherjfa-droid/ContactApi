<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_muestra_el_perfil_con_nombre_fecha_y_telefono(): void
    {
        $user = User::factory()->create([
            'name' => 'Ana',
            'birth_date' => '2000-05-10',
            'phone_number' => '3001234567',
        ]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/profile')
            ->assertOk()
            ->assertJsonPath('data.name', 'Ana')
            ->assertJsonPath('data.birth_date', '2000-05-10')
            ->assertJsonPath('data.phone_number', '3001234567')
            ->assertJsonMissingPath('data.password');
    }

    public function test_actualiza_el_perfil(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->putJson('/api/v1/profile', [
            'name' => 'Nuevo Nombre',
            'birth_date' => '1995-01-20',
            'phone_number' => '3109998877',
        ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Nuevo Nombre');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'phone_number' => '3109998877']);
    }

    public function test_valida_los_campos_del_perfil(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->putJson('/api/v1/profile', ['birth_date' => '2999-01-01'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'birth_date', 'phone_number']);
    }
}
