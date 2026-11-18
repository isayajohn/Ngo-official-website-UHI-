<?php

namespace App\Models;

use App\Enums\UserInvitationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class UserInvitation extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'team_member_id',
        'invited_by_user_id',
        'role_id',
        'primary_department_id',
        'token_hash',
        'status',
        'expires_at',
        'accepted_at',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => UserInvitationStatus::class,
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    public function teamMember(): BelongsTo
    {
        return $this->belongsTo(TeamMember::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(\Spatie\Permission\Models\Role::class);
    }

    public function primaryDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'primary_department_id');
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'user_invitation_departments');
    }

    public function isPendingAndValid(): bool
    {
        return $this->status === UserInvitationStatus::Pending && $this->expires_at->isFuture();
    }

    public function effectiveStatus(): UserInvitationStatus
    {
        if ($this->status === UserInvitationStatus::Pending && $this->expires_at->isPast()) {
            return UserInvitationStatus::Expired;
        }

        return $this->status;
    }

    public function getNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }
}
