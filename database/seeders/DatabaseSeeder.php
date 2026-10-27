<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()
            ->count(5)
            ->create()
            ->each(function (User $user) {
                Contact::factory()
                    ->count(fake()->numberBetween(3, 8))
                    ->for($user)
                    ->create();
            });
    }
}