<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class CreateAdminUser extends Command
{
    protected $signature = 'sso:create-admin
                            {--email= : بريد المدير}
                            {--name= : الاسم الكامل}
                            {--role=super_admin : الدور (super_admin|user_manager|client_manager|viewer)}
                            {--password= : كلمة المرور (ستُولَّد تلقائيًا إن لم تُحدَّد)}';

    protected $description = 'إنشاء مستخدم بدور مدير لوحة التحكم.';

    public function handle(): int
    {
        $email = $this->option('email') ?: $this->ask('البريد الإلكتروني للمدير');
        $name = $this->option('name') ?: $this->ask('الاسم الكامل');
        $role = $this->option('role');
        $password = $this->option('password');

        $validator = Validator::make(
            ['email' => $email, 'name' => $name, 'role' => $role],
            [
                'email' => ['required', 'email'],
                'name' => ['required', 'string', 'min:2'],
                'role' => ['required', 'in:'.implode(',', User::ADMIN_ROLES)],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->components->error($error);
            }

            return self::FAILURE;
        }

        if (! Role::where('name', $role)->where('guard_name', 'web')->exists()) {
            $this->components->error("الدور '{$role}' غير موجود. شغّل: php artisan db:seed --class=RolesSeeder");

            return self::FAILURE;
        }

        $generatedPassword = null;
        if (! $password) {
            $generatedPassword = Str::password(14, letters: true, numbers: true, symbols: false);
            $password = $generatedPassword;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'full_name' => $name,
                'password' => Hash::make($password),
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );

        $user->syncRoles([$role]);

        $this->newLine();
        $this->components->info('تم إنشاء المدير بنجاح.');
        $this->newLine();
        $this->line("  <fg=gray>البريد:</>   <fg=cyan>{$email}</>");
        $this->line("  <fg=gray>الاسم:</>    <fg=cyan>{$name}</>");
        $this->line("  <fg=gray>الدور:</>    <fg=cyan>{$role}</>");
        $this->line("  <fg=gray>UUID:</>     <fg=cyan>{$user->id}</>");

        if ($generatedPassword) {
            $this->newLine();
            $this->components->warn('كلمة المرور المولّدة (لن تظهر مرة أخرى):');
            $this->line("  <fg=yellow;options=bold>{$generatedPassword}</>");
        }

        $this->newLine();
        $this->line('لوحة التحكم: <fg=cyan>http://localhost:8000/admin</>');

        return self::SUCCESS;
    }
}
