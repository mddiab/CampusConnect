<?php

namespace App\Models;

use Database\Factories\ServiceRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'title',
    'department',
    'category',
    'description',
    'status',
    'attachment_path',
    'attachment_original_name',
])]
class ServiceRequest extends Model
{
    /** @use HasFactory<ServiceRequestFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';

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
}
