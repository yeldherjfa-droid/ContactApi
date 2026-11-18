<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Usuario fijo para probar en Insomnia: test@example.com / password
        $demo = User::factory()->create([
            'name' => 'Usuario Demo',
            'email' => 'test@example.com',
        ]);
        Contact::factory()->count(5)->for($demo)->create();

        // Otros usuarios con sus propios contactos
        User::factory()
            ->count(4)
            ->create()
            ->each(fn (User $user) => Contact::factory()->count(3)->for($user)->create());
    }
}
