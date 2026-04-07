<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// FEATURE: Staff Communication & Notes - Model for managing staff responses
#[Fillable(['service_request_id', 'user_id', 'content'])]
class Note extends Model
{
    /**
     * Get the service request that this note belongs to
     * Each note is associated with one service request
     */
    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    /**
     * Get the user (staff member) who wrote this note
     * Each note is written by one user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
