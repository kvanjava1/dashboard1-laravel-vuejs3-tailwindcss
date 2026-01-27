<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'status' => fake()->randomElement(['active', 'inactive', 'suspended']),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'profile_image' => fake()->optional(0.7)->imageUrl(400, 400, 'people'),
            'username' => fake()->unique()->userName(),
            'bio' => fake()->optional()->paragraph(),
            'date_of_birth' => fake()->optional()->date(),
            'location' => fake()->optional()->city(),
            'timezone' => fake()->timezone(),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
