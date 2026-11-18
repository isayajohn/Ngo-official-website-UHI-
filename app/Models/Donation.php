<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Donation extends Model
{
    public const STATUSES = ['PENDING', 'RECEIVED', 'CANCELLED'];
    public const FREQUENCIES = ['one_time' => 'One-time', 'monthly' => 'Monthly'];
    public const PURPOSES = ['where_needed' => 'Where needed most', 'maternal_health' => 'Maternal and child health', 'community_health' => 'Community health', 'other' => 'Other'];
    public const PAYMENT_METHODS = ['contact_me' => 'Please contact me', 'bank_transfer' => 'Bank transfer', 'mobile_money' => 'Mobile money'];

    protected $fillable = ['donor_name', 'email', 'phone', 'programme_id', 'amount', 'currency', 'frequency', 'purpose', 'payment_method', 'payment_reference', 'status', 'received_at', 'internal_notes'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'received_at' => 'datetime'];
    }

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }
}
