<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class Department extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['parent_id', 'head_staff_id', 'code', 'name', 'slug', 'short_description', 'description', 'email', 'phone', 'office_location', 'icon', 'featured_image', 'display_order', 'show_on_website', 'accepts_public_messages', 'active'];

    protected function casts(): array
    {
        return ['show_on_website' => 'boolean', 'accepts_public_messages' => 'boolean', 'active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::creating(function (Department $department) {
            $base = Str::slug($department->slug ?: $department->name) ?: 'department';
            $slug = $base;
            $suffix = 2;
            while (static::withTrashed()->where('slug', $slug)->exists()) {
                $slug = $base.'-'.$suffix++;
            }
            $department->slug = $slug;
            $department->code = strtoupper($department->code);
        });
        static::saving(function (Department $department) {
            if ($department->isDirty('code')) {
                $department->code = strtoupper($department->code);
            }
            if ($department->parent_id && $department->parent_id === $department->id) {
                throw ValidationException::withMessages(['data.parent_id' => 'A department cannot be its own parent.']);
            }
            if ($department->parent_id && $department->exists) {
                $parent = static::withTrashed()->find($department->parent_id);
                $seen = [$department->id];
                while ($parent) {
                    if (in_array($parent->id, $seen, true)) {
                        throw ValidationException::withMessages(['data.parent_id' => 'This parent would create a department hierarchy loop.']);
                    }
                    $seen[] = $parent->id;
                    $parent = $parent->parent_id ? static::withTrashed()->find($parent->parent_id) : null;
                }
            }
            if ($department->head_staff_id && ! TeamMember::query()->whereKey($department->head_staff_id)->where('active', true)->where('department_id', $department->id)->exists()) {
                throw ValidationException::withMessages(['data.head_staff_id' => 'The department head must be an active team member of this department.']);
            }
        });
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('active', true)->where('show_on_website', true)->orderBy('display_order')->orderBy('name');
    }

    public function parent(): BelongsTo { return $this->belongsTo(self::class, 'parent_id'); }
    public function children(): HasMany { return $this->hasMany(self::class, 'parent_id')->orderBy('display_order')->orderBy('name'); }
    public function positions(): HasMany { return $this->hasMany(Position::class); }
    public function teamMembers(): HasMany { return $this->hasMany(TeamMember::class); }
    public function head(): BelongsTo { return $this->belongsTo(TeamMember::class, 'head_staff_id'); }
    public function leadership(): HasMany { return $this->hasMany(DepartmentLeadership::class); }
    public function activeLeadership(): HasMany { return $this->leadership()->current(); }
    public function deputyHeads(): HasMany { return $this->activeLeadership()->whereIn('leadership_role', ['DEPUTY_HEAD', 'ACTING_HEAD']); }
    public function managers(): HasMany { return $this->activeLeadership()->where('leadership_role', 'MANAGER'); }
    public function tasks(): HasMany { return $this->hasMany(DepartmentTask::class); }
    public function performanceIndicators(): HasMany { return $this->hasMany(PerformanceIndicator::class); }
    public function performanceTargets(): HasMany { return $this->hasMany(PerformanceTarget::class); }
    public function performanceResults(): HasMany { return $this->hasMany(PerformanceResult::class); }
    public function approvalRequests(): HasMany { return $this->hasMany(ApprovalRequest::class); }
    public function activity(): HasMany { return $this->hasMany(DepartmentActivityLog::class)->latest(); }
    public function users(): BelongsToMany { return $this->belongsToMany(User::class, 'user_departments')->withPivot(['is_primary', 'department_role', 'active'])->withTimestamps(); }
    public function programmes(): BelongsToMany { return $this->belongsToMany(Programme::class)->withTimestamps(); }
    public function projects(): BelongsToMany { return $this->belongsToMany(Project::class)->using(DepartmentProject::class)->withPivot('is_lead')->withTimestamps(); }
}
