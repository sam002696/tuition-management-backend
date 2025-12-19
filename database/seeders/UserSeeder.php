<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run() : void
    {
        User::factory()
            ->count(1000)
            ->create();
    }
}
