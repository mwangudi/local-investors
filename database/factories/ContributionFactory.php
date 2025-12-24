<?php

namespace Database\Factories;

use App\Models\Contribution;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContributionFactory extends Factory
{
    protected $model = Contribution::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['monthly', 'welfare', 'penalty', 'special']);

        return [
            'member_id' => Member::factory(),
            'shares'  => $type === 'monthly'
                ? $this->faker->randomFloat(2, 500, 2000)
                : 0,

            'welfare' => $type === 'welfare'
                ? $this->faker->randomFloat(2, 100, 500)
                : 0,

            'penalty' => $type === 'penalty'
                ? $this->faker->randomFloat(2, 50, 300)
                : 0,

            'type'    => $type,
            'notes'   => $this->faker->sentence(),
            'paid_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
