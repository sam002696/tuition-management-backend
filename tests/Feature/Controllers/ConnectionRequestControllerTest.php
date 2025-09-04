<?php

namespace Tests\Feature\Controllers;

use App\Services\ConnectionRequest\ConnectionRequestService;
use Illuminate\Auth\GenericUser;
use Mockery\MockInterface;
use Tests\TestCase;

class ConnectionRequestControllerTest extends TestCase
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
    public function send_request_as_teacher_returns_201()
    {
        $this->actingAs($this->asTeacher(), 'sanctum');

        $this->mock(ConnectionRequestService::class, function (MockInterface $m) {
            $m->shouldReceive('sendRequest')->once()->andReturn([
                'id' => 1,
                'teacher_id' => 11,
                'student_id' => 22,
                'status' => 'pending'
            ]);
        });

        $res = $this->postJson('/api/connection/send', [
            'custom_id' => 'S01700000000',
            'tuition_details_id' => 5,
        ]);

        $res->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Connection request sent successfully')
            ->assertJsonPath('data.connection.status', 'pending');
    }

    /** @test */
    public function respond_as_student_returns_success_message()
    {
        $this->actingAs($this->asStudent(), 'sanctum');

        $this->mock(ConnectionRequestService::class, function (MockInterface $m) {
            // controller uses ->status property in message, so return an object
            $m->shouldReceive('respondToRequest')->once()->andReturn((object)[
                'id' => 1,
                'status' => 'accepted'
            ]);
        });

        $res = $this->postJson('/api/connection/respond/1', ['status' => 'accepted']);

        $res->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Request accepted')
            ->assertJsonPath('data.connection.status', 'accepted');
    }

    /** @test */
    public function list_connections_with_filters()
    {
        $this->actingAs($this->asTeacher(), 'sanctum');

        $payload = [
            'requests' => [],
            'pagination' => [
                'current_page' => 1,
                'per_page' => 10,
                'total' => 0,
                'total_pages' => 0,
                'has_more_pages' => false
            ]
        ];

        $this->mock(ConnectionRequestService::class, function (MockInterface $m) use ($payload) {
            $m->shouldReceive('getFilteredConnections')
                ->once()->andReturn($payload);
        });

        $res = $this->getJson('/api/connections?status=pending&per_page=10&search=sa');

        $res->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Connection requests fetched successfully')
            ->assertJsonPath('data.pagination.per_page', 10);
    }

    /** @test */
    public function count_connections_returns_counts()
    {
        $this->actingAs($this->asStudent(), 'sanctum');

        $this->mock(ConnectionRequestService::class, function (MockInterface $m) {
            $m->shouldReceive('getConnectionCounts')->once()->andReturn([
                'active_accepted' => 3,
                'inactive_accepted' => 1,
                'pending' => 2
            ]);
        });

        $res = $this->getJson('/api/connections/count');

        $res->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.connection_count.active_accepted', 3);
    }

    /** @test */
    public function check_connection_status_returns_status_string()
    {
        $this->actingAs($this->asTeacher(), 'sanctum');

        $this->mock(ConnectionRequestService::class, function (MockInterface $m) {
            $m->shouldReceive('checkConnectionStatus')->once()->andReturn('pending');
        });

        $res = $this->postJson('/api/connection/check-connection-status', ['student_id' => 22]);

        $res->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.status', 'pending');
    }

    /** @test */
    public function disconnect_connection_returns_success()
    {
        $this->actingAs($this->asTeacher(), 'sanctum');

        $this->mock(ConnectionRequestService::class, function (MockInterface $m) {
            $m->shouldReceive('disconnectConnection')->once()->andReturn([
                'id' => 99,
                'is_active' => false,
                'status' => 'accepted'
            ]);
        });

        $res = $this->patchJson('/api/connections/99/disconnect');

        $res->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Connection disconnected successfully.');
    }
}
