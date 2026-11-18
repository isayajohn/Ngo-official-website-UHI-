<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

class Position extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['department_id', 'title', 'code', 'description', 'level', 'reports_to_position_id', 'display_order', 'active'];
    protected function casts(): array { return ['active' => 'boolean']; }

    protected static function booted(): void
    {
        static::saving(function (Position $position) {
            if ($position->reports_to_position_id && $position->reports_to_position_id === $position->id) {
                throw ValidationException::withMessages(['data.reports_to_position_id' => 'A position cannot report to itself.']);
            }
        });
    }

    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function reportsTo(): BelongsTo { return $this->belongsTo(self::class, 'reports_to_position_id'); }
    public function subordinates(): HasMany { return $this->hasMany(self::class, 'reports_to_position_id'); }
    public function teamMembers(): HasMany { return $this->hasMany(TeamMember::class); }
}
