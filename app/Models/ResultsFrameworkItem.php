<?php

namespace App\Models;

use App\Enums\ResultsFrameworkLevel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;
use App\Services\DepartmentActivity;

class ResultsFrameworkItem extends Model
{
    use SoftDeletes;

    protected $fillable = ['programme_id', 'project_id', 'parent_id', 'level', 'code', 'title', 'description', 'start_date', 'end_date', 'display_order', 'active'];

    protected function casts(): array
    {
        return ['level' => ResultsFrameworkLevel::class, 'start_date' => 'date', 'end_date' => 'date', 'active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saving(function (self $item): void {
            $level = $item->level instanceof ResultsFrameworkLevel ? $item->level->value : $item->level;
            $parent = $item->parent;

            if ($item->end_date && $item->start_date && $item->end_date->lt($item->start_date)) {
                throw ValidationException::withMessages(['end_date' => 'The end date must be on or after the start date.']);
            }

            if ($item->parent_id && (! $parent || $parent->is($item))) {
                throw ValidationException::withMessages(['parent_id' => 'Choose a valid parent item.']);
            }

            if ($parent && $item->exists && $item->descendants()->whereKey($parent->getKey())->exists()) {
                throw ValidationException::withMessages(['parent_id' => 'A results framework item cannot be nested beneath one of its descendants.']);
            }

            if ($parent && $item->programme_id && $parent->programme_id && (int) $parent->programme_id !== (int) $item->programme_id) {
                throw ValidationException::withMessages(['parent_id' => 'Parent and child must belong to the same programme.']);
            }

            if ($parent && $item->project_id && $parent->project_id && (int) $parent->project_id !== (int) $item->project_id) {
                throw ValidationException::withMessages(['parent_id' => 'Parent and child must belong to the same project.']);
            }

            if ($item->project_id && $item->programme_id && ! Project::query()->whereKey($item->project_id)->where('programme_id', $item->programme_id)->exists()) {
                throw ValidationException::withMessages(['project_id' => 'Choose a project that belongs to the selected programme.']);
            }

            if ($parent && $level === ResultsFrameworkLevel::Impact->value) {
                throw ValidationException::withMessages(['parent_id' => 'An Impact must be a top-level results framework item.']);
            }
        });

        static::created(fn (self $item) => DepartmentActivity::record($item->project?->departments()->wherePivot('is_lead', true)->value('departments.id'), auth()->id(), 'mel.framework_item_created', $item));
        static::updated(fn (self $item) => $item->wasChanged() ? DepartmentActivity::record($item->project?->departments()->wherePivot('is_lead', true)->value('departments.id'), auth()->id(), 'mel.framework_item_changed', $item, ['changed' => array_keys($item->getChanges())]) : null);
    }

    public function programme(): BelongsTo { return $this->belongsTo(Programme::class); }
    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function parent(): BelongsTo { return $this->belongsTo(self::class, 'parent_id'); }
    public function children(): HasMany { return $this->hasMany(self::class, 'parent_id')->orderBy('display_order')->orderBy('id'); }
    public function descendants(): Builder
    {
        $ids = collect();
        $frontier = [$this->getKey()];
        while ($frontier !== []) {
            $children = static::query()->whereIn('parent_id', $frontier)->pluck('id')->all();
            $frontier = array_values(array_diff($children, $ids->all()));
            $ids = $ids->merge($frontier);
        }
        return static::query()->whereKey($ids->all());
    }
    public function indicators(): HasMany { return $this->hasMany(PerformanceIndicator::class, 'results_framework_item_id'); }
}
