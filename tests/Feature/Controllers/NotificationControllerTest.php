<?php

namespace Tests\Feature\Controllers;

use App\Services\Notification\NotificationService;
use Illuminate\Auth\GenericUser;
use Illuminate\Http\JsonResponse;
use Mockery\MockInterface;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    private function asUser($id)
    {
        return new GenericUser(['id' => $id, 'role' => 'teacher', 'name' => 'N']);
    }

    /** @test */
    public function index_returns_notifications_for_self()
    {
        $this->actingAs($this->asUser(55), 'sanctum');

        $this->mock(NotificationService::class, function (MockInterface $m) {
            $m->shouldReceive('getNotificationsByUserId')
                ->once()
                ->andReturn(new JsonResponse([
                    'status' => 'success',
                    'message' => 'Notifications retrieved successfully.',
                    'data' => ['notifications' => [['id' => 'n1', 'read_at' => null]]],
                ], 200));
        });

        $res = $this->getJson('/api/users/55/notifications');

        $res->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(1, 'data.notifications');
    }
}
