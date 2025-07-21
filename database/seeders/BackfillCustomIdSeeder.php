<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BackfillCustomIdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::whereNull('custom_id')->get();

        foreach ($users as $user) {
            if ($user->phone && in_array($user->role, ['teacher', 'student'])) {
                $prefix = $user->role === 'student' ? 'S' : 'T';
                $user->custom_id = $prefix . $user->phone;
                $user->save();
            }
        }
    }
}
