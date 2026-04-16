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
        // FEATURE: Priority Levels (Urgent) - Add urgent flag
        if (!Schema::hasColumn('service_requests', 'is_urgent')) {
            Schema::table('service_requests', function (Blueprint $table) {
                $table->boolean('is_urgent')->default(false)->after('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            if (Schema::hasColumn('service_requests', 'is_urgent')) {
                $table->dropColumn('is_urgent');
            }
        });
    }
};
