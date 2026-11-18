<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectImage extends Model
{
    protected $fillable = ['image', 'alt_text', 'caption', 'display_order'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
