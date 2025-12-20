<?php

namespace Database\Seeders;

use App\Models\ConnectionRequest;
use App\Models\TuitionDetails;
use Illuminate\Database\Seeder;

class ConnectionRequestSeeder extends Seeder
{
    public function run(): void
    {
        TuitionDetails::all()->each(function ($tuition) {
            ConnectionRequest::firstOrCreate(
                [
                    'teacher_id' => $tuition->teacher_id,
                    'student_id' => $tuition->student_id,
                ],
                [
                    'tuition_details_id' => $tuition->id,
                    'status' => 'pending',
                    'is_active' => true,
                ]
            );
        });
    }
}
