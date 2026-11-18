<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

class Account extends Model
{
    use SoftDeletes;

    protected $fillable = ['parent_id', 'code', 'name', 'type', 'normal_balance', 'description', 'allow_transactions', 'active', 'display_order'];
    protected function casts(): array { return ['allow_transactions' => 'boolean', 'active' => 'boolean']; }
    public function parent(): BelongsTo { return $this->belongsTo(self::class, 'parent_id'); }
    public function children(): HasMany { return $this->hasMany(self::class, 'parent_id')->orderBy('display_order')->orderBy('code'); }
    public function bankAccount(): HasOne { return $this->hasOne(BankAccount::class); }
    public function cashAccount(): HasOne { return $this->hasOne(CashAccount::class); }

    protected static function booted(): void
    {
        static::saving(function (Account $account): void {
            $normalBalance = ['ASSET' => 'DEBIT', 'EXPENSE' => 'DEBIT', 'LIABILITY' => 'CREDIT', 'EQUITY' => 'CREDIT', 'INCOME' => 'CREDIT'];
            if (! isset($normalBalance[$account->type]) || ! in_array($account->normal_balance, ['DEBIT', 'CREDIT'], true)) throw ValidationException::withMessages(['type' => 'Select a valid account type and normal balance.']);
            if ($normalBalance[$account->type] !== $account->normal_balance) throw ValidationException::withMessages(['normal_balance' => 'The normal balance must match the account type.']);
            if ($account->parent_id && (int) $account->parent_id === (int) $account->id) throw ValidationException::withMessages(['parent_id' => 'An account cannot be its own parent.']);
            if ($account->parent_id && self::withTrashed()->whereKey($account->parent_id)->exists()) {
                $parent = self::withTrashed()->find($account->parent_id);
                if ($parent && ($parent->type !== $account->type || $parent->allow_transactions)) throw ValidationException::withMessages(['parent_id' => 'Choose a non-posting parent account of the same type.']);
            }
            if ($account->parent_id && $account->exists) {
                $parent = self::withTrashed()->find($account->parent_id);
                $seen = [$account->id];
                while ($parent) {
                    if (in_array($parent->id, $seen, true)) throw ValidationException::withMessages(['parent_id' => 'This parent would create an account hierarchy loop.']);
                    $seen[] = $parent->id;
                    $parent = $parent->parent_id ? self::withTrashed()->find($parent->parent_id) : null;
                }
            }
            if ($account->exists && $account->children()->exists() && $account->allow_transactions) throw ValidationException::withMessages(['allow_transactions' => 'A parent account with child accounts cannot accept direct postings.']);
        });
        static::created(fn (Account $account) => \App\Services\FinanceAudit::record($account, 'created', ['code' => $account->code, 'name' => $account->name, 'type' => $account->type]));
        static::updated(function (Account $account): void { if ($account->wasChanged()) \App\Services\FinanceAudit::record($account, 'updated', ['changed' => array_keys($account->getChanges())]); });
    }
}
