<?php

namespace App\Models;

use Database\Factories\ServiceRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'department_id',
    'service_category_id',
    'title',
    'description',
    'status',
    'is_urgent',
    'staff_notes',
    'resolved_at',
    'archived_at',
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

    protected static function booted(): void
    {
        static::saving(function (self $serviceRequest): void {
            if ($serviceRequest->service_category_id) {
                $departmentId = $serviceRequest->serviceCategory?->department_id
                    ?? ServiceCategory::query()
                        ->whereKey($serviceRequest->service_category_id)
                        ->value('department_id');

                $serviceRequest->department_id = $departmentId;
            }

            if ($serviceRequest->status === self::STATUS_COMPLETED) {
                $serviceRequest->resolved_at ??= now();
            } else {
                $serviceRequest->resolved_at = null;
            }
        });
    }

    /**
     * @return array<int, string>
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_IN_PROGRESS,
            self::STATUS_COMPLETED,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function serviceCategory(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ServiceRequestMessage::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
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

    public function scopeForDepartment(Builder $query, ?int $departmentId): Builder
    {
        return $query->when(
            $departmentId,
            fn (Builder $builder) => $builder->where('department_id', $departmentId),
            fn (Builder $builder) => $builder->whereRaw('1 = 0'),
        );
    }

    public function scopeNotArchived(Builder $query): Builder
    {
        return $query->whereNull('archived_at');
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->whereNotNull('archived_at');
    }

    public function departmentName(): string
    {
        return $this->department?->name ?? 'Unassigned';
    }

    public function categoryName(): string
    {
        return $this->serviceCategory?->name ?? 'Unassigned';
    }

    public function canBeManagedBy(User $user): bool
    {
        return $user->role === User::ROLE_STAFF
            && $user->department_id !== null
            && $this->department_id === $user->department_id;
    }

    public function canBeViewedBy(User $user): bool
    {
        return match ($user->role) {
            User::ROLE_ADMIN => true,
            User::ROLE_STUDENT => $this->user_id === $user->id,
            default => $this->canBeManagedBy($user),
        };
    }

    public function addMessageFrom(User $user, string $message): ServiceRequestMessage
    {
        return $this->messages()->create([
            'user_id' => $user->id,
            'author_name' => $user->name,
            'author_role' => $user->role,
            'message' => $message,
        ]);
    }

    /**
     * FEATURE: Request History & Archiving - Check if request is archived
     */
    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    /**
     * FEATURE: Edit/Update Requests - Check if request can be edited
     * Only pending requests can be edited by their creator
     */
    public function canBeEditedBy(User $user): bool
    {
        return $this->user_id === $user->id && $this->status === self::STATUS_PENDING;
    }
}
