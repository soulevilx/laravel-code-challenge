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
            'user_id' => User::factory(),
            'amount' => 5000,
            'terms' => rand(1,3),
            'outstanding_amount' => 5000,
            'currency_code' => 'VND',
            'processed_at' => date('Y-m-d H:i:s'),
            'status' => 'due',
            'created_at' => date('Y-m-d H:i:s')
        ];
    }
}
