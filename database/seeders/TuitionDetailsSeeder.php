<?php

namespace Database\Seeders;

use App\Models\TuitionDetails;
use Illuminate\Database\Seeder;

class TuitionDetailsSeeder extends Seeder
{
    public function run(): void
    {
        TuitionDetails::factory()->count(50)->create();
    }
}
