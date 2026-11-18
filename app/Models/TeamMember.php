<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TeamMember extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['department_id', 'position_id', 'user_id', 'name', 'slug', 'email', 'phone', 'short_bio', 'bio', 'photo', 'photo_alt', 'employment_type', 'display_order', 'show_on_website', 'active', 'is_leader', 'leadership_role', 'leadership_group', 'leadership_parent_id'];
    protected function casts(): array { return ['show_on_website' => 'boolean', 'active' => 'boolean', 'is_leader' => 'boolean']; }

    protected static function booted(): void
    {
        static::creating(function (TeamMember $member) {
            $base = Str::slug($member->slug ?: $member->name) ?: 'team-member';
            $slug = $base; $suffix = 2;
            while (static::withTrashed()->where('slug', $slug)->exists()) $slug = $base.'-'.$suffix++;
            $member->slug = $slug;
        });

        static::saving(function (TeamMember $member): void {
            if (! $member->is_leader) {
                $member->leadership_parent_id = null;
                return;
            }

            if (! $member->leadership_parent_id) return;
            if ((int) $member->leadership_parent_id === (int) $member->getKey()) {
                throw ValidationException::withMessages(['leadership_parent_id' => 'A leader cannot report to themself.']);
            }

            $parent = static::query()->where('active', true)->where('show_on_website', true)->find($member->leadership_parent_id);
            if (! $parent || ! $parent->is_leader || $parent->leadership_group !== $member->leadership_group) {
                throw ValidationException::withMessages(['leadership_parent_id' => 'Choose an active leadership profile from the same section.']);
            }

            $seen = [];
            while ($parent) {
                if ((int) $parent->getKey() === (int) $member->getKey() || in_array($parent->getKey(), $seen, true)) {
                    throw ValidationException::withMessages(['leadership_parent_id' => 'This reporting relationship would create a hierarchy loop.']);
                }
                $seen[] = $parent->getKey();
                $parent = $parent->leadershipParent()->first();
            }
        });
    }

    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function position(): BelongsTo { return $this->belongsTo(Position::class); }
    public function user(): \Illuminate\Database\Eloquent\Relations\HasOne { return $this->hasOne(User::class); }
    public function legacyUser(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
    public function leadershipAppointments(): HasMany { return $this->hasMany(DepartmentLeadership::class); }
    public function leadershipParent(): BelongsTo { return $this->belongsTo(self::class, 'leadership_parent_id'); }
    public function leadershipChildren(): HasMany { return $this->hasMany(self::class, 'leadership_parent_id')->where('is_leader', true)->where('active', true)->where('show_on_website', true)->orderBy('display_order')->orderBy('name'); }
    public function performanceTargets(): HasMany { return $this->hasMany(PerformanceTarget::class); }
    public function performanceResults(): HasMany { return $this->hasMany(PerformanceResult::class); }
    public function ledDepartments() { return $this->belongsToMany(Department::class, 'department_leadership')->withPivot(['leadership_role', 'is_primary', 'appointment_status', 'active'])->withTimestamps(); }
    public function scopePublished($query) { return $query->where('active', true)->where('show_on_website', true)->orderBy('display_order')->orderBy('name'); }
}
