<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('oauth_clients', function (Blueprint $table) {
            if (! Schema::hasColumn('oauth_clients', 'slug')) {
                $table->string('slug', 64)->nullable()->unique()->after('name');
            }
            if (! Schema::hasColumn('oauth_clients', 'display_name_ar')) {
                $table->string('display_name_ar')->nullable()->after('slug');
            }
            if (! Schema::hasColumn('oauth_clients', 'display_name_en')) {
                $table->string('display_name_en')->nullable()->after('display_name_ar');
            }
            if (! Schema::hasColumn('oauth_clients', 'description')) {
                $table->text('description')->nullable()->after('display_name_en');
            }
            if (! Schema::hasColumn('oauth_clients', 'color')) {
                $table->string('color', 16)->nullable()->after('description');
            }
            if (! Schema::hasColumn('oauth_clients', 'launch_url')) {
                $table->string('launch_url', 500)->nullable()->after('color');
            }
            if (! Schema::hasColumn('oauth_clients', 'logo_url')) {
                $table->string('logo_url', 500)->nullable()->after('launch_url');
            }
            if (! Schema::hasColumn('oauth_clients', 'is_first_party')) {
                $table->boolean('is_first_party')->default(false)->after('logo_url');
            }
            if (! Schema::hasColumn('oauth_clients', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('oauth_clients', function (Blueprint $table) {
            foreach (['slug', 'display_name_ar', 'display_name_en', 'description', 'color', 'launch_url', 'logo_url', 'is_first_party', 'deleted_at'] as $col) {
                if (Schema::hasColumn('oauth_clients', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
