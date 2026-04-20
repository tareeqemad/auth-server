<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\User;
use App\Models\UserSystemLink;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TestUsersSeeder extends Seeder
{
    private const DEFAULT_PASSWORD = 'password123';

    private const USERS = [
        [
            'email' => 'admin@example.com',
            'full_name' => 'مدير النظام',
            'phone' => '+962790000001',
            'is_active' => true,
            'last_login_days_ago' => 0,
            'systems' => ['system_a', 'system_b', 'system_c', 'system_d', 'system_e'],
        ],
        [
            'email' => 'ahmed@example.com',
            'full_name' => 'أحمد محمد',
            'phone' => '+962790000002',
            'is_active' => true,
            'last_login_days_ago' => 1,
            'systems' => ['system_a', 'system_b'],
        ],
        [
            'email' => 'sara@example.com',
            'full_name' => 'سارة علي',
            'phone' => '+962790000003',
            'is_active' => true,
            'last_login_days_ago' => 3,
            'systems' => ['system_c', 'system_d'],
        ],
        [
            'email' => 'fatma@example.com',
            'full_name' => 'فاطمة حسن',
            'phone' => null,
            'is_active' => true,
            'last_login_days_ago' => null,
            'systems' => [],
        ],
        [
            'email' => 'khaled@example.com',
            'full_name' => 'خالد المعطّل',
            'phone' => '+962790000005',
            'is_active' => false,
            'last_login_days_ago' => 60,
            'systems' => ['system_a'],
        ],
        [
            'email' => 'ibrahim@example.com',
            'full_name' => 'إبراهيم العطية',
            'phone' => '+962790000006',
            'is_active' => true,
            'last_login_days_ago' => 7,
            'systems' => ['system_e'],
        ],
    ];

    public function run(): void
    {
        $password = Hash::make(self::DEFAULT_PASSWORD);
        $summary = [];

        foreach (self::USERS as $definition) {
            $user = $this->upsertUser($definition, $password);
            $this->syncSystemLinks($user, $definition['systems']);
            $this->generateAuditLogs($user, $definition);

            $summary[] = [
                'email' => $user->email,
                'name' => $user->full_name,
                'active' => $user->is_active ? 'نعم' : 'لا',
                'systems' => count($definition['systems']),
                'id' => $user->id,
            ];
        }

        $this->renderSummary($summary);
    }

    private function upsertUser(array $def, string $hashedPassword): User
    {
        $lastLogin = $def['last_login_days_ago'] !== null
            ? CarbonImmutable::now()->subDays($def['last_login_days_ago'])
            : null;

        return User::updateOrCreate(
            ['email' => $def['email']],
            [
                'full_name' => $def['full_name'],
                'password' => $hashedPassword,
                'phone' => $def['phone'],
                'is_active' => $def['is_active'],
                'email_verified_at' => $def['is_active'] ? now() : null,
                'last_login_at' => $lastLogin,
                'last_login_ip' => $lastLogin ? '192.168.1.'.random_int(10, 200) : null,
            ],
        );
    }

    private function syncSystemLinks(User $user, array $systems): void
    {
        foreach ($systems as $systemName) {
            UserSystemLink::updateOrCreate(
                ['user_id' => $user->id, 'system_name' => $systemName],
                [
                    'external_user_id' => (string) random_int(1000, 99999),
                    'linked_at' => CarbonImmutable::now()->subMonths(random_int(3, 24)),
                    'last_accessed_at' => CarbonImmutable::now()->subDays(random_int(0, 30)),
                    'metadata' => ['source' => 'test_seeder'],
                ],
            );
        }
    }

    private function generateAuditLogs(User $user, array $def): void
    {
        AuditLog::where('user_id', $user->id)
            ->where('metadata->source', 'test_seeder')
            ->delete();

        $successCount = $def['is_active'] ? random_int(3, 12) : random_int(0, 2);

        for ($i = 0; $i < $successCount; $i++) {
            AuditLog::create([
                'user_id' => $user->id,
                'event_type' => AuditLog::EVENT_LOGIN_SUCCESS,
                'email' => $user->email,
                'ip_address' => '192.168.1.'.random_int(10, 200),
                'user_agent' => $this->fakeUserAgent(),
                'metadata' => ['source' => 'test_seeder'],
                'created_at' => CarbonImmutable::now()->subDays(random_int(0, 30))->subHours(random_int(0, 23)),
            ]);
        }

        $failedCount = random_int(0, 3);
        for ($i = 0; $i < $failedCount; $i++) {
            AuditLog::create([
                'user_id' => $user->id,
                'event_type' => AuditLog::EVENT_LOGIN_FAILED,
                'email' => $user->email,
                'ip_address' => '192.168.1.'.random_int(10, 200),
                'user_agent' => $this->fakeUserAgent(),
                'metadata' => ['source' => 'test_seeder', 'reason' => 'invalid_credentials'],
                'created_at' => CarbonImmutable::now()->subDays(random_int(0, 30)),
            ]);
        }
    }

    private function fakeUserAgent(): string
    {
        $agents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Safari/605.1.15',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0) Mobile/15E148',
            'Mozilla/5.0 (X11; Linux x86_64) Firefox/121.0',
        ];

        return $agents[array_rand($agents)];
    }

    private function renderSummary(array $summary): void
    {
        $this->command->newLine();
        $this->command->info('✅ تم إنشاء '.count($summary).' مستخدم تجريبي');
        $this->command->newLine();
        $this->command->table(
            ['البريد الإلكتروني', 'الاسم', 'نشط؟', 'الأنظمة المرتبطة', 'UUID'],
            array_map(
                fn ($row) => [$row['email'], $row['name'], $row['active'], $row['systems'], Str::limit($row['id'], 13)],
                $summary,
            ),
        );
        $this->command->newLine();
        $this->command->warn('كلمة المرور لجميع المستخدمين: '.self::DEFAULT_PASSWORD);
        $this->command->line('جرّب الدخول عبر: <fg=cyan>http://localhost:8000/login</>');
    }
}
