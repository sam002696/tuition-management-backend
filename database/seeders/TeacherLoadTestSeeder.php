<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\TuitionDetails;
use App\Models\ConnectionRequest;
use App\Models\TuitionEvent;
use Illuminate\Database\Seeder;

class TeacherLoadTestSeeder extends Seeder
{
    public function run(): void
    {
        $teacherId = 2;

        $teacher = User::where('id', $teacherId)
            ->where('role', 'teacher')
            ->firstOrFail();

        // pick many students
        $students = User::where('role', 'student')
            ->inRandomOrder()
            ->limit(100)
            ->get();

        foreach ($students as $student) {

            // Tuition details
            $tuition = TuitionDetails::firstOrCreate(
                [
                    'teacher_id' => $teacher->id,
                    'student_id' => $student->id,
                ],
                [
                    'tuition_type' => 'monthly_based',
                    'class_level' => 'Class 9',
                    'subject_list' => ['Math', 'Physics'],
                    'medium' => 'English Version',
                    'institute_name' => 'Load Test Institute',
                    'address_line' => 'Mirpur, Dhaka',
                    'district' => 'Dhaka',
                    'thana' => 'Mirpur',
                    'study_purpose' => 'Load testing',

                    'tuition_days_per_week' => 3,
                    'hours_per_day' => 2,
                    'days_name' => ['Sunday', 'Tuesday', 'Thursday'],
                    'salary_per_month' => 7000,
                    'starting_month' => now(),
                ]
            );

            //  Connection request (pending)
            ConnectionRequest::firstOrCreate(
                [
                    'teacher_id' => $teacher->id,
                    'student_id' => $student->id,
                ],
                [
                    'tuition_details_id' => $tuition->id,
                    'status' => 'pending',
                    'is_active' => true,
                ]
            );

            //  Tuition events (MULTIPLE per student)
            for ($i = 0; $i < 5; $i++) {
                TuitionEvent::create([
                    'teacher_id' => $teacher->id,
                    'student_id' => $student->id,
                    'title' => 'Tuition Session',
                    'description' => 'Load test session',
                    'scheduled_at' => now()->addDays(rand(1, 60)),
                    'status' => 'pending',
                ]);
            }
        }
    }
}
