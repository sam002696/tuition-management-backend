<?php

namespace Tests\Feature\Controllers;

use App\Services\TuitionEvent\TuitionEventService;
use Illuminate\Auth\GenericUser;
use Mockery\MockInterface;
use Tests\TestCase;

class TuitionEventControllerTest extends TestCase
{
    private function asTeacher()
    {
        return new GenericUser(['id' => 11, 'name' => 'T', 'role' => 'teacher']);
    }
    private function asStudent()
    {
        return new GenericUser(['id' => 22, 'name' => 'S', 'role' => 'student']);
    }

    /** @test */
    public function create_event_as_teacher_returns_201()
    {
        $this->actingAs($this->asTeacher(), 'sanctum');

        $this->mock(TuitionEventService::class, function (MockInterface $m) {
            $m->shouldReceive('create')->once()->andReturn([
                'id' => 1,
                'teacher_id' => 11,
                'student_id' => 22,
                'title' => 'Math',
                'status' => 'pending'
            ]);
        });

        $res = $this->postJson('/api/tuition-events', [
            'student_id' => 22,
            'title' => 'Math',
            'scheduled_at' => '2025-08-27 10:00:00'
        ]);

        $res->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.event.title', 'Math');
    }

    /** @test */
    public function respond_to_event_as_student()
    {
        $this->actingAs($this->asStudent(), 'sanctum');

        $this->mock(TuitionEventService::class, function (MockInterface $m) {
            $m->shouldReceive('respond')->once()->andReturn((object)[
                'id' => 1,
                'status' => 'accepted',
            ]);
        });


        $res = $this->postJson('/api/tuition-events/respond/1', ['status' => 'accepted']);

        $res->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Event accepted');
    }

    /** @test */
    public function my_events_lists_only_current_user_side()
    {
        $this->actingAs($this->asTeacher(), 'sanctum');

        $this->mock(TuitionEventService::class, function (MockInterface $m) {
            $m->shouldReceive('getMyEvents')->once()->andReturn([
                ['id' => 1, 'status' => 'accepted', 'title' => 'Session 1']
            ]);
        });

        $res = $this->getJson('/api/tuition-events/my');

        $res->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(1, 'data.events');
    }

    /** @test */
    public function my_pending_events_lists_pending()
    {
        $this->actingAs($this->asStudent(), 'sanctum');

        $this->mock(TuitionEventService::class, function (MockInterface $m) {
            $m->shouldReceive('getPendingEvents')->once()->andReturn([
                ['id' => 2, 'status' => 'pending', 'title' => 'Wait']
            ]);
        });

        $res = $this->getJson('/api/tuition-events/pending');

        $res->assertOk()->assertJsonPath('data.events.0.status', 'pending');
    }

    /** @test */
    public function get_events_with_student_as_teacher()
    {
        $this->actingAs($this->asTeacher(), 'sanctum');

        $this->mock(TuitionEventService::class, function (MockInterface $m) {
            $m->shouldReceive('getEventsForStudentTeacher')->once()->andReturn([
                ['id' => 3, 'student_id' => 22, 'title' => 'Focus']
            ]);
        });

        $res = $this->getJson('/api/tuition-events/student?student_id=22');

        $res->assertOk()->assertJsonPath('data.events.0.student_id', 22);
    }

    /** @test */
    public function get_events_with_teacher_as_student()
    {
        $this->actingAs($this->asStudent(), 'sanctum');

        $this->mock(TuitionEventService::class, function (MockInterface $m) {
            $m->shouldReceive('getEventsForTeacherStudent')->once()->andReturn([
                ['id' => 4, 'teacher_id' => 11, 'title' => 'Check']
            ]);
        });

        $res = $this->getJson('/api/tuition-events/teacher?teacher_id=11');

        $res->assertOk()->assertJsonPath('data.events.0.teacher_id', 11);
    }
}
