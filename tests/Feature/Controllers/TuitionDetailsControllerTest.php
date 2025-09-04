<?php

namespace Tests\Feature\Controllers;

use App\Services\TuitionDetails\TuitionDetailsService;
use Illuminate\Auth\GenericUser;
use Mockery\MockInterface;
use Tests\TestCase;

class TuitionDetailsControllerTest extends TestCase
{
    private function asAny()
    {
        return new GenericUser(['id' => 9, 'role' => 'teacher', 'name' => 'X']);
    }

    /** @test */
    public function update_returns_success_and_payload()
    {
        $this->actingAs($this->asAny(), 'sanctum');

        $this->mock(TuitionDetailsService::class, function (MockInterface $m) {
            $m->shouldReceive('update')->once()->andReturn([
                'id' => 7,
                'class_level' => 'Grade 7'
            ]);
        });

        $res = $this->patchJson('/api/tuition-details/7', ['class_level' => 'Grade 7']);

        $res->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Tuition details updated successfully');
    }

    /** @test */
    public function show_returns_one()
    {
        $this->actingAs($this->asAny(), 'sanctum');

        $this->mock(TuitionDetailsService::class, function (MockInterface $m) {
            $m->shouldReceive('getById')->once()->andReturn([
                'id' => 7,
                'teacher_id' => 1,
                'student_id' => 2
            ]);
        });

        $res = $this->getJson('/api/tuition-details/7');

        $res->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.tuition_details.id', 7);
    }

    /** @test */
    public function get_by_teacher_and_student()
    {
        $this->actingAs($this->asAny(), 'sanctum');

        $this->mock(TuitionDetailsService::class, function (MockInterface $m) {
            $m->shouldReceive('getByTeacherAndStudent')->once()->andReturn([
                'id' => 10,
                'teacher_id' => 1,
                'student_id' => 2
            ]);
        });

        $res = $this->getJson('/api/tuition-details/teacher/1/student/2');

        $res->assertOk()->assertJsonPath('data.tuition_details.teacher_id', 1);
    }
}
