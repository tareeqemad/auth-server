<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Services\PasswordHistoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_matches_current_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('current-pass')]);
        $svc = app(PasswordHistoryService::class);

        $this->assertTrue($svc->matchesRecent($user, 'current-pass'));
        $this->assertFalse($svc->matchesRecent($user, 'totally-new'));
    }

    public function test_service_matches_recorded_history(): void
    {
        $user = User::factory()->create(['password' => Hash::make('current')]);
        $svc = app(PasswordHistoryService::class);

        $svc->record($user, Hash::make('old-1'));
        $svc->record($user, Hash::make('old-2'));

        $this->assertTrue($svc->matchesRecent($user, 'old-1'));
        $this->assertTrue($svc->matchesRecent($user, 'old-2'));
        $this->assertFalse($svc->matchesRecent($user, 'brand-new'));
    }

    public function test_profile_password_update_rejects_reuse_of_current(): void
    {
        $user = User::factory()->create([
            'email' => 'reuse@example.com',
            'password' => Hash::make('current-pass'),
        ]);

        $response = $this->actingAs($user)
            ->from('/profile/password')
            ->put('/profile/password', [
                'current_password' => 'current-pass',
                'password' => 'current-pass',
                'password_confirmation' => 'current-pass',
            ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_profile_password_update_accepts_new_password(): void
    {
        $user = User::factory()->create([
            'email' => 'newpass@example.com',
            'password' => Hash::make('old-pass'),
        ]);

        $response = $this->actingAs($user)
            ->from('/profile/password')
            ->put('/profile/password', [
                'current_password' => 'old-pass',
                'password' => 'brand-new-123',
                'password_confirmation' => 'brand-new-123',
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('brand-new-123', $user->fresh()->password));

        $this->assertTrue(
            app(PasswordHistoryService::class)->matchesRecent($user->fresh(), 'old-pass')
        );
    }
}
