<?php

namespace Database\Factories;


use App\Models\ConnectionRequest;
use App\Models\TuitionDetails;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConnectionRequestFactory extends Factory
{
    protected $model = ConnectionRequest::class;

    public function definition(): array
    {
        $tuition = TuitionDetails::inRandomOrder()->first();

        return [
            'tuition_details_id' => $tuition->id,
            'teacher_id' => $tuition->teacher_id,
            'student_id' => $tuition->student_id,
            'status' => $this->faker->randomElement(['pending', 'accepted', 'rejected']),
            'is_active' => true,
        ];
    }
}
