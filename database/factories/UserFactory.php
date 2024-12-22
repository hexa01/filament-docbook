<?php

namespace Database\Factories;

use App\Models\Specialization;
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
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'role' => 'patient',
            'address' => $this->faker->address(),
            'phone' => $this->faker->phoneNumber(),
            'gender' => $this->faker->randomElement(['male', 'female', 'other']),
            'dob' => $this->faker->date(),
            'password' => Hash::make('12345678'), // All users have the same password
            'remember_token' => Str::random(10),
        ];
    }
    /**
     * Indicate that the model's role is 'superadmin'.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function superadmin()
    {
        return $this->state([
            'role' => 'superadmin',
        ]);
    }

    /**
     * Indicate that the model's role is 'admin'.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function admin()
    {
        return $this->state([
            'role' => 'admin',
        ]);
    }


    /**
     * Indicate that the model's role is 'doctor'.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function doctor()
    {
        return $this->state([
            'role' => 'doctor',
        ]);
    }


    /**
     * Indicate that the model's role is 'patient'.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function patient()
    {
        return $this->state([
            'role' => 'patient',
        ]);
    }



    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
