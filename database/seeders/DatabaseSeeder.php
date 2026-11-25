<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $demo = User::factory()->create([
            'name' => 'Usuario Demo',
            'email' => 'test@example.com',
        ]);
        Contact::factory()->count(5)->for($demo)->create();

        $demo->tokens()->create([
            'name' => 'insomnia',
            'token' => hash('sha256', 'token-demo'),
            'abilities' => ['*'],
        ]);

        User::factory()
            ->count(4)
            ->create()
            ->each(fn (User $user) => Contact::factory()->count(3)->for($user)->create());
    }
}
