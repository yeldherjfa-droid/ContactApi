<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contact>
 */
class ContactFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->name(),
            'address' => fake()->address(),
            'phone_number' => fake()->numerify('3#########'),
            'email' => fake()->safeEmail(),
            'avatar' => 'https://i.pravatar.cc/150?u='.fake()->uuid(),
        ];
    }
}
