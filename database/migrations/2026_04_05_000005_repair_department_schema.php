<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('departments', 'name')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->string('name')->nullable()->after('id');
            });
        }

        if (! Schema::hasColumn('service_categories', 'name')) {
            Schema::table('service_categories', function (Blueprint $table) {
                $table->string('name')->nullable()->after('id');
            });
        }

        if (! Schema::hasColumn('service_categories', 'department_id')) {
            Schema::table('service_categories', function (Blueprint $table) {
                $table->foreignId('department_id')
                    ->nullable()
                    ->after('name')
                    ->constrained('departments')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('users', 'department_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('department_id')
                    ->nullable()
                    ->after('role')
                    ->constrained('departments')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('service_requests', 'department_id')) {
            Schema::table('service_requests', function (Blueprint $table) {
                $table->foreignId('department_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('departments')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a repair migration for previously incomplete schema definitions.
    }
};
