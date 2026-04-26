<?php

namespace Tests\Feature\Auth;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_loads(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_valid_credentials_authenticate(): void
    {
        $user = User::factory()->create([
            'email' => 'alice@example.com',
            'password' => Hash::make('correct-pass'),
        ]);

        $this->post('/login', [
            'email' => 'alice@example.com',
            'password' => 'correct-pass',
        ])->assertRedirect();

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'event_type' => AuditLog::EVENT_LOGIN_SUCCESS,
        ]);
    }

    public function test_invalid_password_fails(): void
    {
        User::factory()->create([
            'email' => 'bob@example.com',
            'password' => Hash::make('correct'),
        ]);

        $this->from('/login')->post('/login', [
            'email' => 'bob@example.com',
            'password' => 'wrong',
        ])->assertRedirect('/login');

        $this->assertGuest();
        $this->assertDatabaseHas('audit_logs', [
            'email' => 'bob@example.com',
            'event_type' => AuditLog::EVENT_LOGIN_FAILED,
        ]);
    }

    public function test_inactive_account_cannot_login(): void
    {
        User::factory()->create([
            'email' => 'inactive@example.com',
            'password' => Hash::make('pass1234'),
            'is_active' => false,
        ]);

        $this->from('/login')
            ->post('/login', [
                'email' => 'inactive@example.com',
                'password' => 'pass1234',
            ])
            ->assertRedirect('/login');

        $this->assertGuest();
    }

    public function test_nonexistent_email_fails_silently(): void
    {
        $this->from('/login')
            ->post('/login', [
                'email' => 'ghost@example.com',
                'password' => 'anything',
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
