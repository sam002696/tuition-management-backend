<?php

namespace Database\Seeders;

use App\Models\TuitionEvent;
use Illuminate\Database\Seeder;

class TuitionEventSeeder extends Seeder
{
    public function run(): void
    {
        TuitionEvent::factory()->count(500)->create();
    }
}
