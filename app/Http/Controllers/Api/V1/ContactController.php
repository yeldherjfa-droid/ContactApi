<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contact\ContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ContactController extends Controller
{
    /**
     * Lista solo los contactos del usuario autenticado.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        return ContactResource::collection($request->user()->contacts()->latest()->get());
    }

    /**
     * Crea un contacto para el usuario autenticado.
     */
    public function store(ContactRequest $request): JsonResponse
    {
        $contact = $request->user()->contacts()->create($request->validated());

        return (new ContactResource($contact))->response()->setStatusCode(201);
    }

    /**
     * Muestra un contacto.
     */
    public function show(Request $request, int $id): ContactResource
    {
        return new ContactResource($this->findOwnContact($request, $id));
    }

    /**
     * Actualiza un contacto.
     */
    public function update(ContactRequest $request, int $id): ContactResource
    {
        $contact = $this->findOwnContact($request, $id);
        $contact->update($request->validated());

        return new ContactResource($contact);
    }

    /**
     * Elimina un contacto (soft delete: solo marca deleted_at).
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->findOwnContact($request, $id)->delete();

        return response()->json(['message' => 'Contacto eliminado correctamente.']);
    }

    /**
     * Busca el contacto y verifica que sea del usuario.
     * 404 si no existe (o está eliminado), 403 si es de otro usuario.
     */
    private function findOwnContact(Request $request, int $id): Contact
    {
        $contact = Contact::find($id);

        abort_if(! $contact, 404);
        abort_if($contact->user_id !== $request->user()->id, 403);

        return $contact;
    }
}
