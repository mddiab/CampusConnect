<?php

namespace App\Models;

use Database\Factories\ServiceRequestMessageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'service_request_id',
    'user_id',
    'author_name',
    'author_role',
    'message',
])]
class ServiceRequestMessage extends Model
{
    /** @use HasFactory<ServiceRequestMessageFactory> */
    use HasFactory;

    protected $touches = ['serviceRequest'];

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function roleLabel(): string
    {
        return match ($this->author_role) {
            User::ROLE_STAFF => 'Staff',
            User::ROLE_ADMIN => 'Admin',
            default => 'Student',
        };
    }
}
