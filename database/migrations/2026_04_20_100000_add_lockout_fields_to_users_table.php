<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedSmallInteger('failed_login_attempts')->default(0)->after('is_active');
            $table->timestamp('locked_until')->nullable()->index()->after('failed_login_attempts');
            $table->string('locked_reason', 255)->nullable()->after('locked_until');
            $table->uuid('locked_by_admin_id')->nullable()->index()->after('locked_reason');
            $table->timestamp('last_failed_login_at')->nullable()->after('locked_by_admin_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'failed_login_attempts',
                'locked_until',
                'locked_reason',
                'locked_by_admin_id',
                'last_failed_login_at',
            ]);
        });
    }
};
