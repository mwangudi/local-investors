<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Member;
use App\Models\Contribution;

class ContributionSeeder extends Seeder
{
    public function run(): void
    {
        Member::all()->each(function ($member) {
            Contribution::factory()
                ->count(rand(8, 20))
                ->create([
                    'member_id' => $member->id,
                ]);
        });
    }
}