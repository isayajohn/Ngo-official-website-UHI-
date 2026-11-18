<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class CashAccount extends Model
{
    protected $fillable = ['account_id', 'name', 'department_id', 'currency', 'opening_balance', 'active', 'custodian_team_member_id'];
    protected function casts(): array { return ['opening_balance' => 'decimal:2', 'active' => 'boolean']; }
    public function account(): BelongsTo { return $this->belongsTo(Account::class); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function custodian(): BelongsTo { return $this->belongsTo(TeamMember::class, 'custodian_team_member_id'); }

    protected static function booted(): void
    {
        static::saving(function (CashAccount $cash): void {
            $account = Account::find($cash->account_id);
            if ($account && ($account->type !== 'ASSET' || ! $account->allow_transactions)) throw ValidationException::withMessages(['account_id' => 'Select an active transaction-enabled asset account.']);
            if ($cash->custodian_team_member_id && $cash->department_id && ! TeamMember::whereKey($cash->custodian_team_member_id)->where('department_id', $cash->department_id)->exists()) throw ValidationException::withMessages(['custodian_team_member_id' => 'The custodian must belong to the selected department.']);
            $cash->currency = strtoupper($cash->currency);
        });
        static::created(fn (CashAccount $cash) => \App\Services\FinanceAudit::record($cash, 'created', ['name' => $cash->name, 'department_id' => $cash->department_id, 'currency' => $cash->currency]));
        static::updated(function (CashAccount $cash): void { if ($cash->wasChanged()) \App\Services\FinanceAudit::record($cash, 'updated', ['changed' => array_keys($cash->getChanges())]); });
    }
}
