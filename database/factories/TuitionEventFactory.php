<?php

namespace Database\Factories;

use App\Models\TuitionEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;


class TuitionEventFactory extends Factory
{
    protected $model = TuitionEvent::class;

    public function definition(): array
    {
        $teacher = User::where('role', 'teacher')->inRandomOrder()->first();
        $student = User::where('role', 'student')->inRandomOrder()->first();

        return [
            'teacher_id' => $teacher->id,
            'student_id' => $student->id,
            'title' => 'Tuition Session',
            'description' => $this->faker->sentence(),
            'scheduled_at' => now()->addDays(rand(1, 30)),
            'status' => $this->faker->randomElement(['pending', 'accepted', 'rejected']),
        ];
    }
}
