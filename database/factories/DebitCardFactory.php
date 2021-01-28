<?php

namespace Database\Factories;

use App\Models\DebitCard;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DebitCardFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DebitCard::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'number' => $this->faker->creditCardNumber,
            'type' => $this->faker->creditCardType,
            'expiration_date' => $this->faker->dateTimeBetween('+1 month', '+3 year'),
            'disabled_at' => $this->faker->boolean ? $this->faker->dateTime : null,
            'user_id' => fn () => User::factory()->create(),
        ];
    }

    /**
     * Indicate that the debit card is active.
     *
     * @return Factory
     */
    public function active(): Factory
    {
        return $this->state(fn () => [
            'disabled_at' => null,
        ]);
    }

    /**
     * Indicate that the debit card is expired.
     *
     * @return Factory
     */
    public function expired(): Factory
    {
        return $this->state(fn () => [
            'disabled_at' => $this->faker->dateTime,
        ]);
    }
}
