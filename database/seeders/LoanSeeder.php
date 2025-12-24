<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Member;
use App\Models\Loan;

class LoanSeeder extends Seeder
{
    public function run(): void
    {
        Member::all()->each(function ($member) {
            Loan::factory()
                ->count(rand(1, 5))
                ->create([
                    'member_id' => $member->id
                ]);
        });
    }
}
