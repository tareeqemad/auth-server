<?php

namespace Tests\Feature;

use App\Models\User;
use App\Rules\PalestinianNationalId;
use App\Services\OIDC\ClaimsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class NationalIdTest extends TestCase
{
    use RefreshDatabase;

    public function test_validator_accepts_valid_id_from_spec(): void
    {
        $this->assertTrue(PalestinianNationalId::isValid('400104865'));
    }

    public function test_validator_rejects_bad_checksum(): void
    {
        $this->assertFalse(PalestinianNationalId::isValid('400104866'));
        $this->assertFalse(PalestinianNationalId::isValid('123456789'));
    }

    public function test_validator_rejects_wrong_length_or_non_digits(): void
    {
        $this->assertFalse(PalestinianNationalId::isValid('12345'));
        $this->assertFalse(PalestinianNationalId::isValid('1234567890'));
        $this->assertFalse(PalestinianNationalId::isValid(''));
        $this->assertFalse(PalestinianNationalId::isValid('abc123456'));
        $this->assertFalse(PalestinianNationalId::isValid('400-10-865'));
    }

    public function test_mask_hides_middle_digits(): void
    {
        $this->assertSame('400****865', PalestinianNationalId::mask('400104865'));
        $this->assertSame('—', PalestinianNationalId::mask(null));
        $this->assertSame('—', PalestinianNationalId::mask('123'));
    }

    public function test_user_masked_national_id_returns_masked_string(): void
    {
        $user = User::factory()->create(['national_id' => '400104865']);
        $this->assertSame('400****865', $user->maskedNationalId());

        $noNid = User::factory()->create(['national_id' => null]);
        $this->assertSame('—', $noNid->maskedNationalId());
    }

    public function test_claims_service_emits_national_id_only_when_scope_present(): void
    {
        $user = User::factory()->create(['national_id' => '400104865']);
        $svc = app(ClaimsService::class);

        $withoutScope = $svc->getClaimsForUser($user, ['openid', 'profile', 'email']);
        $this->assertArrayNotHasKey('national_id', $withoutScope);

        $withScope = $svc->getClaimsForUser($user, ['openid', 'national_id']);
        $this->assertSame('400104865', $withScope['national_id']);
    }

    public function test_claims_service_omits_national_id_when_user_has_none(): void
    {
        $user = User::factory()->create(['national_id' => null]);
        $svc = app(ClaimsService::class);

        $claims = $svc->getClaimsForUser($user, ['openid', 'national_id']);
        $this->assertArrayNotHasKey('national_id', $claims);
    }

    public function test_profile_update_sets_national_id_when_empty(): void
    {
        $user = User::factory()->create([
            'national_id' => null,
            'password' => Hash::make('pass1234'),
        ]);

        $response = $this->actingAs($user)
            ->from('/profile')
            ->put('/profile', [
                'full_name' => $user->full_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'national_id' => '400104865',
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame('400104865', $user->fresh()->national_id);
    }

    public function test_profile_update_rejects_invalid_national_id(): void
    {
        $user = User::factory()->create([
            'national_id' => null,
            'password' => Hash::make('pass1234'),
        ]);

        $response = $this->actingAs($user)
            ->from('/profile')
            ->put('/profile', [
                'full_name' => $user->full_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'national_id' => '400104866',
            ]);

        $response->assertSessionHasErrors('national_id');
        $this->assertNull($user->fresh()->national_id);
    }

    public function test_profile_update_ignores_national_id_if_already_set(): void
    {
        $user = User::factory()->create([
            'national_id' => '400104865',
            'password' => Hash::make('pass1234'),
        ]);

        $this->actingAs($user)
            ->from('/profile')
            ->put('/profile', [
                'full_name' => $user->full_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'national_id' => '123456789',
            ]);

        $this->assertSame('400104865', $user->fresh()->national_id);
    }

    public function test_duplicate_national_id_is_rejected(): void
    {
        User::factory()->create(['national_id' => '400104865']);

        $user = User::factory()->create([
            'national_id' => null,
            'password' => Hash::make('pass1234'),
        ]);

        $response = $this->actingAs($user)
            ->from('/profile')
            ->put('/profile', [
                'full_name' => $user->full_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'national_id' => '400104865',
            ]);

        $response->assertSessionHasErrors('national_id');
    }
}
