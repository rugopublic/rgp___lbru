<?php

namespace Database\Factories\Auth;

use App\Models\Auth\PasswordResetToken;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PasswordResetToken>
 */
class PasswordResetTokenFactory extends Factory
{
    protected $model = PasswordResetToken::class;

    /**
     * Returns the factory's default model definition.
     * @return array
     */
    public function definition(): array
    {
        return [
            'email' => User::factory()->create()->email,
            'token' => Str::random(64),
            'created_at' => now(),
        ];
    }

    /**
     * Expired token state.
     */
    public function expired(): static
    {
        return $this->state(fn () => [
            'created_at' => now()->subDays(2),
        ]);
    }
}
