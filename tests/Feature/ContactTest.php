<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private array $datos = [
        'name' => 'Carlos Pérez',
        'address' => 'Calle 10 # 20-30',
        'phone_number' => '3001112233',
        'email' => 'carlos@example.com',
        'avatar' => 'https://i.pravatar.cc/150?u=carlos',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_la_tabla_contacts_tiene_los_campos_correctos(): void
    {
        $this->assertTrue(Schema::hasColumns('contacts', [
            'id', 'user_id', 'name', 'address', 'phone_number', 'email', 'avatar',
            'created_at', 'updated_at', 'deleted_at',
        ]));
    }

    public function test_lista_solo_los_contactos_del_usuario(): void
    {
        Contact::factory()->count(3)->for($this->user)->create();
        Contact::factory()->count(2)->create();

        $this->getJson('/api/v1/contacts')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_crea_un_contacto(): void
    {
        $this->postJson('/api/v1/contacts', $this->datos)
            ->assertCreated()
            ->assertJsonPath('data.name', 'Carlos Pérez')
            ->assertJsonPath('data.email', 'carlos@example.com');

        $this->assertDatabaseHas('contacts', ['user_id' => $this->user->id, 'name' => 'Carlos Pérez']);
    }

    public function test_valida_los_campos_del_contacto(): void
    {
        $this->postJson('/api/v1/contacts', ['email' => 'no-es-correo'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'address', 'phone_number', 'email']);
    }

    public function test_muestra_un_contacto_propio(): void
    {
        $contact = Contact::factory()->for($this->user)->create();

        $this->getJson("/api/v1/contacts/{$contact->id}")
            ->assertOk()
            ->assertJsonStructure(['data' => ['id', 'name', 'address', 'phone_number', 'email', 'avatar']]);
    }

    public function test_actualiza_un_contacto_propio(): void
    {
        $contact = Contact::factory()->for($this->user)->create();

        $this->putJson("/api/v1/contacts/{$contact->id}", $this->datos)
            ->assertOk()
            ->assertJsonPath('data.name', 'Carlos Pérez');
    }

    public function test_elimina_con_soft_delete(): void
    {
        $contact = Contact::factory()->for($this->user)->create();

        $this->deleteJson("/api/v1/contacts/{$contact->id}")
            ->assertOk()
            ->assertExactJson(['message' => 'Contacto eliminado correctamente.']);

        $this->assertSoftDeleted($contact);
        $this->getJson("/api/v1/contacts/{$contact->id}")->assertNotFound();
    }

    public function test_no_puede_ver_editar_ni_eliminar_contactos_de_otro_usuario(): void
    {
        $ajeno = Contact::factory()->create();
        $error = ['message' => 'usted no tiene los permisos necesarios para ver este recurso'];

        $this->getJson("/api/v1/contacts/{$ajeno->id}")->assertForbidden()->assertExactJson($error);
        $this->putJson("/api/v1/contacts/{$ajeno->id}", $this->datos)->assertForbidden()->assertExactJson($error);
        $this->deleteJson("/api/v1/contacts/{$ajeno->id}")->assertForbidden()->assertExactJson($error);

        $this->assertNotSoftDeleted($ajeno);
    }

    public function test_contacto_inexistente_responde_404_en_json(): void
    {
        $this->getJson('/api/v1/contacts/9999')
            ->assertNotFound()
            ->assertExactJson(['message' => 'recurso no encontrado']);
    }

    public function test_ruta_inexistente_responde_404_en_json(): void
    {
        $this->getJson('/api/v1/no-existe')
            ->assertNotFound()
            ->assertExactJson(['message' => 'recurso no encontrado']);
    }
}
