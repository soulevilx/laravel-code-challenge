<?php

namespace Database\Factories;

use App\Models\Loan;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoanFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Loan::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'terms' => $this->faker->randomElement([3, 6]),
            'amount' => $this->faker->numberBetween(1000, 10000),
            'outstanding_amount' => function($attributes) {
                return $attributes['amount'];
            },
            'currency_code' => $this->faker->randomElement(['SGD', 'VND']),
            'processed_at' => $this->faker->date(),
            'status' => Loan::STATUS_DUE
        ];
    }
}
