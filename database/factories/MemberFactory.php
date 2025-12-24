<?php

namespace Database\Factories;

use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

class MemberFactory extends Factory
{
    protected $model = Member::class;

    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name'  => $this->faker->lastName(),
            'email'      => $this->faker->unique()->safeEmail(),
            'phone'      => '07' . $this->faker->numberBetween(10000000, 99999999),
            'join_date'  => $this->faker->dateTimeBetween('-2 years', 'now'),
        ];
    }
}
