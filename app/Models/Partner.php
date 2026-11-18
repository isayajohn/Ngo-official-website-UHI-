<?php

namespace App\Models;

use App\Models\Concerns\HasPublicSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Partner extends Model
{
    use HasFactory, HasPublicSlug, SoftDeletes;

    protected $fillable = ['name', 'slug', 'partner_type', 'short_description', 'description', 'website', 'email', 'logo', 'logo_alt', 'featured_image', 'image_alt', 'is_featured', 'show_on_website', 'active', 'display_order', 'meta_title', 'meta_description'];
    protected function casts(): array { return ['is_featured' => 'boolean', 'show_on_website' => 'boolean', 'active' => 'boolean']; }
    public function scopePublished(Builder $query): Builder { return $query->where('active', true)->where('show_on_website', true)->orderBy('display_order')->orderBy('name'); }
}
