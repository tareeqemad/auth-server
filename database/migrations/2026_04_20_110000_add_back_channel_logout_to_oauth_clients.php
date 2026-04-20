<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('oauth_clients', function (Blueprint $table) {
            $table->string('back_channel_logout_uri', 500)->nullable()->after('description');
            $table->boolean('back_channel_logout_session_required')->default(true)->after('back_channel_logout_uri');
        });
    }

    public function down(): void
    {
        Schema::table('oauth_clients', function (Blueprint $table) {
            $table->dropColumn([
                'back_channel_logout_uri',
                'back_channel_logout_session_required',
            ]);
        });
    }
};
