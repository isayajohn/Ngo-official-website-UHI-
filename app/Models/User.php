<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\UghiResetPasswordNotification;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name', 'first_name', 'middle_name', 'last_name', 'username', 'email', 'phone', 'profile_photo', 'team_member_id',
        'password', 'is_active', 'status', 'must_change_password', 'locale', 'timezone', 'notification_preferences',
        'email_verified_at', 'phone_verified_at', 'last_login_at', 'last_login_ip', 'failed_login_attempts', 'locked_until', 'password_changed_at',
    ];

    public const STATUSES = ['ACTIVE', 'INACTIVE', 'SUSPENDED', 'LOCKED', 'PENDING'];

    protected $attributes = ['is_active' => true, 'must_change_password' => true];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime', 'phone_verified_at' => 'datetime', 'last_login_at' => 'datetime', 'locked_until' => 'datetime',
            'password_changed_at' => 'datetime', 'password' => 'hashed', 'is_active' => 'boolean', 'must_change_password' => 'boolean',
            'notification_preferences' => 'array', 'failed_login_attempts' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            $user->password_changed_at ??= now();
            if ($user->isDirty('status')) $user->is_active = $user->status === 'ACTIVE';
            else $user->status = $user->is_active ? 'ACTIVE' : 'INACTIVE';
        });

        static::saving(function (User $user): void {
            if ($user->isDirty('status')) $user->is_active = $user->status === 'ACTIVE';
            elseif ($user->isDirty('is_active')) $user->status = $user->is_active ? 'ACTIVE' : 'INACTIVE';
            if ($user->isDirty('password')) $user->password_changed_at = now();
            if ($user->first_name || $user->last_name) {
                $user->name = trim(implode(' ', array_filter([$user->first_name, $user->middle_name, $user->last_name])));
            }
        });
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($this->status === 'LOCKED' && $this->locked_until?->isPast()) {
            $this->forceFill(['status' => 'ACTIVE', 'is_active' => true, 'locked_until' => null, 'failed_login_attempts' => 0])->save();
        }

        return $this->status === 'ACTIVE' && $this->is_active && ! $this->locked_until?->isFuture() && $this->can('admin.access');
    }

    public function teamMember(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return $this->belongsTo(TeamMember::class); }
    public function departments(): BelongsToMany { return $this->belongsToMany(Department::class, 'user_departments')->withPivot(['is_primary', 'department_role', 'active'])->withTimestamps(); }
    public function primaryDepartment(): BelongsToMany { return $this->departments()->wherePivot('is_primary', true)->wherePivot('active', true); }
    public function assignedDepartmentTasks(): HasMany { return $this->hasMany(DepartmentTask::class, 'assigned_to_user_id'); }
    public function createdDepartmentTasks(): HasMany { return $this->hasMany(DepartmentTask::class, 'assigned_by_user_id'); }
    public function tasks(): HasMany { return $this->assignedDepartmentTasks(); }
    public function approvalRequests(): HasMany { return $this->hasMany(ApprovalRequest::class, 'requested_by_user_id'); }
    public function auditActivities(): HasMany { return $this->hasMany(DepartmentActivityLog::class, 'user_id')->latest(); }

    public function hasActiveDepartmentAccess(): bool
    {
        return $this->departments()->wherePivot('active', true)->exists();
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new UghiResetPasswordNotification($token));
    }
}
