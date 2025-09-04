<?php

namespace Tests\Feature\Controllers;

use App\Services\Auth\AuthService;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function register_returns_201_and_success_shape()
    {
        $fakeUser = ['id' => 1, 'name' => 'Alice', 'email' => 'a@example.com', 'role' => 'teacher'];

        $this->mock(AuthService::class, function (MockInterface $m) use ($fakeUser) {
            $m->shouldReceive('registerUser')->once()->andReturn($fakeUser);
        });

        $res = $this->postJson('/api/register', [
            'name' => 'Alice',
            'email' => 'a@example.com',
            'password' => 'secret',
            'role' => 'teacher'
        ]);

        $res->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'User registered successfully')
            ->assertJsonPath('data.user.email', 'a@example.com');
    }

    /** @test */
    public function login_success()
    {
        $fake = [
            'user'  => ['id' => 1, 'name' => 'Alice', 'email' => 'a@example.com', 'role' => 'teacher'],
            'token' => Str::random(40),
        ];

        $this->mock(AuthService::class, function (MockInterface $m) use ($fake) {
            $m->shouldReceive('loginUser')->once()->andReturn($fake);
        });

        $res = $this->postJson('/api/login', ['email' => 'a@example.com', 'password' => 'secret']);

        $res->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Login successful')
            ->assertJsonPath('data.user.email', 'a@example.com')
            ->assertJsonStructure(['data' => ['token']]);
    }

    /** @test */
    public function login_invalid_credentials_returns_401()
    {
        $this->mock(AuthService::class, function (MockInterface $m) {
            $m->shouldReceive('loginUser')->once()->andReturn(null);
        });

        $res = $this->postJson('/api/login', ['email' => 'x@x.com', 'password' => 'wrong']);

        $res->assertStatus(401)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Invalid email or password');
    }
}
