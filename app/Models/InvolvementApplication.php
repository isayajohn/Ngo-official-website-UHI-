<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvolvementApplication extends Model
{
    public const TYPES = ['volunteer' => 'Volunteer', 'membership' => 'Membership', 'partnership' => 'Partner With Us'];
    public const STATUSES = ['NEW', 'REVIEWING', 'ACCEPTED', 'DECLINED', 'CLOSED', 'SPAM'];

    protected $fillable = ['type', 'name', 'email', 'phone', 'organization', 'interest_area', 'availability', 'message', 'status', 'assigned_to_user_id', 'internal_notes', 'submitted_at'];

    protected function casts(): array
    {
        return ['submitted_at' => 'datetime'];
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }
}
