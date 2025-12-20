<?php

namespace Database\Factories;

use App\Models\TuitionDetails;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;


class TuitionDetailsFactory extends Factory
{
    protected $model = TuitionDetails::class;

    public function definition(): array
    {
        $teacher = User::where('role', 'teacher')->inRandomOrder()->first();
        $student = User::where('role', 'student')->inRandomOrder()->first();

        return [
            'teacher_id' => $teacher->id,
            'student_id' => $student->id,
            'tuition_type' => $this->faker->randomElement(['monthly_based', 'course']),
            'class_level' => $this->faker->randomElement(['Class 8', 'Class 9', 'SSC']),
            'subject_list' => ['Math', 'Physics'],
            'medium' => 'English Version',
            'institute_name' => $this->faker->company(),
            'address_line' => $this->faker->address(),
            'district' => 'Dhaka',
            'thana' => 'Mirpur',
            'study_purpose' => 'Exam preparation',

            // monthly
            'tuition_days_per_week' => 3,
            'hours_per_day' => 2,
            'days_name' => ['Sunday', 'Tuesday', 'Thursday'],
            'salary_per_month' => 6000,
            'starting_month' => now(),

            // course
            'total_classes_per_course' => 20,
            'hours_per_class' => 2,
            'salary_per_subject' => 3000,
            'total_course_completion_salary' => 6000,
            'duration' => '2 months',
        ];
    }
}
