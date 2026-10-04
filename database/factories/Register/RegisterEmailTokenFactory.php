<?php

namespace Database\Factories\Register;

use App\Models\Register\RegisterEmailToken;
use Illuminate\Database\Eloquent\Factories\Factory;
use Random\RandomException;

/**
 * @extends Factory<RegisterEmailToken>
 */
class RegisterEmailTokenFactory extends Factory
{
    protected $model = RegisterEmailToken::class;

    /**
     * @throws RandomException
     */
    public function definition(): array
    {
        return [
            'email' => $this->faker->unique()->safeEmail(),
            'token' => bin2hex(random_bytes(32)),
            'created_at' => now(),
        ];
    }

    /**
     * Mark token as expired.
     */
    public function expired(): static
    {
        $ttl = (int) config('register.email_token_ttl', 86400);

        return $this->state(fn () => [
            'created_at' => now()->subSeconds($ttl + 1),
        ]);
    }
}
