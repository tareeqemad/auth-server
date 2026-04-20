<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class HrEmployeesSeeder extends Seeder
{
    public function run(): void
    {
        $csv = __DIR__.'/data/hr_employees.csv';

        if (! is_file($csv)) {
            $this->command->error("CSV not found: {$csv}");

            return;
        }

        $fh = fopen($csv, 'r');
        $headers = fgetcsv($fh);

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $conflicts = [];
        $needsIdLinking = 0;
        $noMobile = 0;

        while (($row = fgetcsv($fh)) !== false) {
            $data = array_combine($headers, $row);

            $empNumber = trim($data['employee_number']);
            $nationalId = trim($data['national_id']);
            $email = trim($data['email']);
            $fullName = trim($data['full_name']);
            $phone = trim($data['phone']);

            if (! $email && ! $nationalId) {
                $skipped++;

                continue;
            }

            // Conflict detection: email or national_id already exists under different employee
            $existing = null;
            if ($nationalId) {
                $existing = User::withTrashed()->where('national_id', $nationalId)->first();
            }
            if (! $existing && $email) {
                $existing = User::withTrashed()->where('email', $email)->first();
            }

            $attrs = [
                'full_name' => $fullName,
                'email' => $email ?: null,
                'phone' => $phone ?: null,
                'national_id' => $nationalId ?: null,
                'employee_number' => $empNumber ?: null,
                'source' => User::SOURCE_HR_MASTER,
                'needs_id_linking' => ! $nationalId,
                'job_title' => $data['job_title'] ?: null,
                'governorate' => $data['governorate'] ?: null,
                'is_active' => true,
                'email_verified_at' => now(),
            ];

            if ($existing) {
                // Skip updating protected admin accounts
                if ($existing->hasAnyRole(User::ADMIN_ROLES)) {
                    $conflicts[] = "emp {$empNumber}: {$email} reserved (admin)";
                    $skipped++;

                    continue;
                }

                $existing->update($attrs);
                $updated++;
            } else {
                $attrs['id'] = (string) Str::uuid7();
                $attrs['password'] = Hash::make(Str::random(32));

                User::create($attrs);
                $created++;
            }

            if (! $nationalId) {
                $needsIdLinking++;
            }
            if (! $phone) {
                $noMobile++;
            }
        }

        fclose($fh);

        $this->command->info("✅ HR Employees imported");
        $this->command->line("   • Created:          {$created}");
        $this->command->line("   • Updated:          {$updated}");
        $this->command->line("   • Skipped:          {$skipped}");
        $this->command->line("   • Needs ID linking: {$needsIdLinking}");
        $this->command->line("   • No mobile (no 2FA):{$noMobile}");

        if ($conflicts) {
            $this->command->warn('Conflicts skipped:');
            foreach ($conflicts as $c) {
                $this->command->line("   - {$c}");
            }
        }

        $total = User::where('source', User::SOURCE_HR_MASTER)->count();
        $this->command->info("Total HR-sourced users in DB: {$total}");
    }
}
