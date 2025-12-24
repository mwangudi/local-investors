<?php

namespace Database\Factories;

use App\Models\Loan;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LoanFactory extends Factory
{
    protected $model = Loan::class;

    public function definition(): array
    {
        $amount = $this->faker->numberBetween(5000, 50000);

        $disbursed = $this->faker->dateTimeBetween('-6 months', 'now');
        $term = $this->faker->randomElement([2]);
        
        return [
            'member_id'        => Member::factory(),
            'amount'           => $amount,
            'interest_percent' => 10, 
            'term_months'      => $term,
            'disbursed_at'     => $disbursed,
            'due_at'           => (clone $disbursed)->modify("+{$term} months"),
            //'repaid_amount'    => $this->faker->randomFloat(2, 0, $amount / 2),
            'repaid'           => false,
            'status' => $this->faker->randomElement([
                'pending'
            ]),
        ];
    }
}