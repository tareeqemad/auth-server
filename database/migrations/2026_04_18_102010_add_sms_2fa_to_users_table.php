<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'sms_2fa_enabled')) {
                $table->boolean('sms_2fa_enabled')->default(false)->after('mfa_secret');
            }

            if (! Schema::hasColumn('users', 'sms_2fa_enabled_at')) {
                $table->timestamp('sms_2fa_enabled_at')->nullable()->after('sms_2fa_enabled');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('users', 'sms_2fa_enabled')) {
                $columns[] = 'sms_2fa_enabled';
            }
            if (Schema::hasColumn('users', 'sms_2fa_enabled_at')) {
                $columns[] = 'sms_2fa_enabled_at';
            }
            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};
