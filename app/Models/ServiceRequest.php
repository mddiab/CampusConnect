<?php

namespace App\Models;

use Database\Factories\ServiceRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'title',
    'department',
    'category',
    'description',
    'status',
    'is_urgent',
    'attachment_path',
    'attachment_original_name',
    'first_completed_view_at',
    'archived_at',
])]
class ServiceRequest extends Model
{
    /** @use HasFactory<ServiceRequestFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';

    public function scopeNotArchived($query)
{
    return $query->where('archived', false);
}

public function scopeArchived($query)
{
    return $query->where('archived', true);
}

    public static function departments(): array
    {
        return [
            'Information Technology',
            'Maintenance',
            'Registrar',
            'Finance',
            'Library',
            'Student Affairs',
        ];
    }

    public static function categories(): array
    {
        return [
            'Technical Support',
            'Facility Maintenance',
            'Registration',
            'Payment',
            'Document Request',
            'General Inquiry',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * FEATURE: Staff Communication & Notes
     * Get all notes/responses from staff on this request
     * Notes are ordered by newest first
     */
    public function notes(): HasMany
    {
        return $this->hasMany(Note::class)->latest();
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_COMPLETED => 'Completed',
            default => 'Pending',
        };
    }

    public function isOpen(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_IN_PROGRESS], true);
    }

    /**
     * FEATURE: Request History & Archiving
     * Check if this request is archived
     */
    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    /**
     * FEATURE: Request History & Archiving
     * Check if this request should be archived (completed & viewed 24+ hours ago)
     */
    public function shouldBeArchived(): bool
    {
        // Only archive if request is completed
        if ($this->status !== self::STATUS_COMPLETED) {
            return false;
        }

        // Only archive if student has viewed it
        if ($this->first_completed_view_at === null) {
            return false;
        }

        // Archive if 24 hours have passed since viewing
        $hoursElapsed = now()->diffInHours($this->first_completed_view_at);
        return $hoursElapsed >= 24;
    }

    /**
     * FEATURE: Request History & Archiving
     * Get all non-archived requests for a user
     */
    public static function notArchived()
    {
        return static::whereNull('archived_at');
    }

    /**
     * FEATURE: Request History & Archiving
     * Get all archived requests for a user
     */
    public static function archived()
    {
        return static::whereNotNull('archived_at');
    }
}
