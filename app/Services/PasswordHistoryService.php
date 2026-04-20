<?php

namespace App\Services;

use App\Models\PasswordHistory;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PasswordHistoryService
{
    public const HISTORY_SIZE = 5;

    public function matchesRecent(User $user, string $plainPassword): bool
    {
        if (Hash::check($plainPassword, $user->password)) {
            return true;
        }

        $histories = PasswordHistory::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(self::HISTORY_SIZE)
            ->get();

        foreach ($histories as $entry) {
            if (Hash::check($plainPassword, $entry->password_hash)) {
                return true;
            }
        }

        return false;
    }

    public function record(User $user, ?string $previousHash = null): void
    {
        $hash = $previousHash ?: $user->password;

        if (! $hash) {
            return;
        }

        PasswordHistory::create([
            'user_id' => $user->id,
            'password_hash' => $hash,
            'created_at' => now(),
        ]);

        $this->pruneOld($user);
    }

    private function pruneOld(User $user): void
    {
        $ids = PasswordHistory::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->skip(self::HISTORY_SIZE)
            ->take(PHP_INT_MAX)
            ->pluck('id');

        if ($ids->isNotEmpty()) {
            PasswordHistory::whereIn('id', $ids)->delete();
        }
    }
}
