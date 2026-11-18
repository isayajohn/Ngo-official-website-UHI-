<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use App\Models\Concerns\HasPublicSlug;
use App\Models\Concerns\HasTranslatedContent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

class Project extends Model
{
    use HasPublicSlug, HasTranslatedContent, SoftDeletes;

    protected $fillable = ['programme_id', 'region_id', 'district_id', 'code', 'project_manager_team_member_id', 'grant_id', 'title', 'slug', 'short_description', 'description', 'challenge', 'objectives', 'activities', 'target_groups', 'featured_image', 'image_alt', 'start_date', 'end_date', 'status', 'location', 'funding_partner', 'project_budget', 'people_reached', 'is_featured', 'is_published', 'published_at', 'meta_title', 'meta_description', 'og_image'];

    protected $attributes = ['status' => 'PLANNED', 'is_published' => false, 'is_featured' => false];

    protected function casts(): array
    {
        return ['title' => 'array', 'short_description' => 'array', 'description' => 'array', 'challenge' => 'array', 'objectives' => 'array', 'activities' => 'array', 'target_groups' => 'array', 'status' => ProjectStatus::class, 'start_date' => 'date', 'end_date' => 'date', 'published_at' => 'datetime', 'is_featured' => 'boolean', 'is_published' => 'boolean', 'project_budget' => 'decimal:2', 'people_reached' => 'integer'];
    }

    protected static function booted(): void
    {
        static::saving(function (Project $project) {
            if ($project->district_id && ! District::whereKey($project->district_id)->where('region_id', $project->region_id)->exists()) {
                throw ValidationException::withMessages(['data.district_id' => 'Choose a district belonging to the selected region.']);
            }
            if ($project->start_date && $project->end_date && $project->end_date->lt($project->start_date)) {
                throw ValidationException::withMessages(['data.end_date' => 'The end date must be on or after the start date.']);
            }
            if ($project->is_published && ! $project->published_at) {
                $project->published_at = now();
            }
        });
        static::updated(function (Project $project) {
            if ($project->wasChanged('programme_id')) {
                $project->impactStatistics()->withTrashed()->update(['programme_id' => $project->programme_id, 'verified_at' => null, 'verified_by' => null, 'is_active' => false]);
            }
        });
    }

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProjectImage::class)->orderBy('display_order')->orderBy('id');
    }

    public function impactStatistics(): HasMany
    {
        return $this->hasMany(ImpactStatistic::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ProjectActivity::class);
    }

    public function performanceIndicators(): HasMany { return $this->hasMany(PerformanceIndicator::class); }
    public function performanceTargets(): HasMany { return $this->hasMany(PerformanceTarget::class); }
    public function performanceResults(): HasMany { return $this->hasMany(PerformanceResult::class); }

    public function milestones(): HasMany
    {
        return $this->hasMany(ProjectMilestone::class);
    }

    public function risks(): HasMany
    {
        return $this->hasMany(ProjectRisk::class);
    }

    public function projectManager(): BelongsTo
    {
        return $this->belongsTo(TeamMember::class, 'project_manager_team_member_id');
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class)->using(DepartmentProject::class)->withPivot('is_lead')->withTimestamps();
    }

    public function approvalRequests(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(ApprovalRequest::class, 'approvable');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)->whereNotNull('published_at')->where('published_at', '<=', now())
            ->whereHas('programme', fn ($q) => $q->where('is_active', true))
            ->where(fn ($q) => $q->whereNull('region_id')->orWhereHas('region', fn ($r) => $r->published()))
            ->where(fn ($q) => $q->whereNull('district_id')->orWhereHas('district', fn ($d) => $d->published()));
    }

    public function locationLabel(): string
    {
        return $this->location ?: collect([$this->district?->name, $this->region?->name, $this->region?->country?->name])->filter()->join(', ');
    }
}
