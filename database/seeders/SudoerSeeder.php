<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SudoerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::updateOrCreate([
            'id' => 1
        ], [
            'name' => 'Super User',
            'email' => 'sudo@localhost.com',
            'email_verified_at' => now(),
            'is_active' => true,
            'password' => Hash::make('sudo101'),
        ])->assignRole(Role::findByName('admin'));
    }
}
