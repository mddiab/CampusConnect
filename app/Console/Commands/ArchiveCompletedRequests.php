<?php

namespace App\Console\Commands;

use App\Models\ServiceRequest;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * FEATURE: Request History & Archiving
 * Automatically archive completed requests that have been viewed for 24+ hours
 * Run this command periodically (e.g., every hour) via scheduler
 */
#[Signature('app:archive-completed-requests')]
#[Description('Automatically archive completed requests that were viewed 24+ hours ago')]
class ArchiveCompletedRequests extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Find all completed, non-archived requests that should be archived
        $requestsToArchive = ServiceRequest::where('status', ServiceRequest::STATUS_COMPLETED)
            ->whereNull('archived_at') // Not already archived
            ->whereNotNull('first_completed_view_at') // Must have been viewed
            ->where('first_completed_view_at', '<=', now()->subHours(24)) // 24+ hours ago
            ->get();

        // Archive each request
        foreach ($requestsToArchive as $request) {
            $request->update(['archived_at' => now()]);
        }

        // Provide feedback to console
        $count = $requestsToArchive->count();
        $this->info("✅ Successfully archived {$count} request(s).");

        return 0;
    }
}
