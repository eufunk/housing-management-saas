<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Owner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Owner>
 */
class OwnerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'user_id' => null,
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
        ];
    }
}
