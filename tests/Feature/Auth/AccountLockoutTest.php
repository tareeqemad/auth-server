<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Services\AccountLockoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountLockoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_reports_locked_when_locked_until_is_future(): void
    {
        $svc = app(AccountLockoutService::class);
        $user = User::factory()->create(['locked_until' => now()->addMinutes(10)]);
        $this->assertTrue($svc->isLocked($user));
    }

    public function test_service_reports_unlocked_when_locked_until_is_past(): void
    {
        $svc = app(AccountLockoutService::class);
        $user = User::factory()->create(['locked_until' => now()->subMinutes(5)]);
        $this->assertFalse($svc->isLocked($user->fresh()));
    }

    public function test_failed_attempts_auto_lock_after_threshold(): void
    {
        $user = User::factory()->create([
            'email' => 'tolock@example.com',
            'password' => Hash::make('correct'),
        ]);

        for ($i = 0; $i < AccountLockoutService::MAX_FAILED_ATTEMPTS; $i++) {
            $this->from('/login')->post('/login', [
                'email' => 'tolock@example.com',
                'password' => 'wrong',
            ]);
        }

        $user->refresh();
        $this->assertTrue(app(AccountLockoutService::class)->isLocked($user));
    }

    public function test_locked_user_cannot_login_with_correct_password(): void
    {
        $user = User::factory()->create([
            'email' => 'locked@example.com',
            'password' => Hash::make('real'),
            'failed_login_attempts' => 5,
            'locked_until' => now()->addMinutes(10),
        ]);

        $this->from('/login')
            ->post('/login', [
                'email' => 'locked@example.com',
                'password' => 'real',
            ])
            ->assertRedirect('/login');

        $this->assertGuest();
    }
}
