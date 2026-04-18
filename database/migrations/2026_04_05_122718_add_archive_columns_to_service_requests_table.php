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
        // FEATURE: Request History & Archiving - Track viewing and automatic archiving
        Schema::table('service_requests', function (Blueprint $table) {
            // Track when student first viewed a completed request
            // Used to calculate 24-hour archive timer
            $table->timestamp('first_completed_view_at')->nullable()->after('updated_at');

            // Timestamp when request was automatically archived
            // Null = not archived, has timestamp = archived
            $table->timestamp('archived_at')->nullable()->after('first_completed_view_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropColumn(['first_completed_view_at', 'archived_at']);
        });
    }
};
