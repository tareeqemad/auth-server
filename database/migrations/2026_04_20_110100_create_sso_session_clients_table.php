<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sso_session_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('sso_session_id')->index();
            $table->foreignUuid('client_id')->index();
            $table->foreignUuid('user_id')->index();
            $table->string('sid', 64)->unique()->comment('Session identifier sent as sid claim in id_token');
            $table->timestamp('authenticated_at')->useCurrent();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('logout_sent_at')->nullable();
            $table->string('logout_status', 32)->nullable()->comment('success | failed | skipped');
            $table->text('logout_error')->nullable();
            $table->timestamps();

            $table->unique(['sso_session_id', 'client_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sso_session_clients');
    }
};
