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
        // FEATURE: Staff Communication & Notes - Create notes table for staff to respond to student requests
        Schema::create('notes', function (Blueprint $table) {
            // Primary key
            $table->id();

            // Foreign keys
            // Link to the service request this note belongs to
            $table->foreignId('service_request_id')->constrained()->cascadeOnDelete();
            
            // Link to the user (staff member) who wrote this note
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // The actual note content written by staff
            $table->text('content');

            // Timestamps: created_at (when note was written) and updated_at (if edited)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
