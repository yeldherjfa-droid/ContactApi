<?php

namespace App\Http\Requests\Contact;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre admite máximo 100 caracteres.',
            'address.required' => 'La dirección es obligatoria.',
            'address.max' => 'La dirección admite máximo 255 caracteres.',
            'phone_number.required' => 'El teléfono es obligatorio.',
            'phone_number.max' => 'El teléfono admite máximo 20 caracteres.',
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'El correo no tiene un formato válido.',
            'email.max' => 'El correo admite máximo 100 caracteres.',
        ];
    }
}
