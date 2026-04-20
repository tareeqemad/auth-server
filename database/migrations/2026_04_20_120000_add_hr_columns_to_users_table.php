<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('national_id', 20)->nullable()->unique()->after('phone');
            $table->unsignedInteger('employee_number')->nullable()->unique()->after('national_id');
            $table->string('source', 20)->default('external')->index()->after('employee_number');
            $table->boolean('needs_id_linking')->default(false)->after('source');
            $table->string('job_title', 255)->nullable()->after('needs_id_linking');
            $table->string('department', 255)->nullable()->after('job_title');
            $table->string('directorate', 255)->nullable()->after('department');
            $table->string('governorate', 64)->nullable()->after('directorate');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'national_id',
                'employee_number',
                'source',
                'needs_id_linking',
                'job_title',
                'department',
                'directorate',
                'governorate',
            ]);
        });
    }
};
