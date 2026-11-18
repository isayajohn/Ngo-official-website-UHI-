<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class BankAccount extends Model
{
    protected $fillable = ['account_id', 'bank_name', 'account_name', 'masked_account_number', 'currency', 'account_type', 'opening_balance', 'active', 'notes'];
    protected function casts(): array { return ['opening_balance' => 'decimal:2', 'active' => 'boolean']; }
    public function account(): BelongsTo { return $this->belongsTo(Account::class); }

    protected static function booted(): void
    {
        static::saving(function (BankAccount $bank): void {
            $account = Account::find($bank->account_id);
            if ($account && ($account->type !== 'ASSET' || ! $account->allow_transactions)) throw ValidationException::withMessages(['account_id' => 'Select an active transaction-enabled asset account.']);
            $bank->currency = strtoupper($bank->currency);
            $bank->masked_account_number = filled($bank->masked_account_number) ? trim($bank->masked_account_number) : null;
        });
        static::created(fn (BankAccount $bank) => \App\Services\FinanceAudit::record($bank, 'created', ['bank_name' => $bank->bank_name, 'currency' => $bank->currency]));
        static::updated(function (BankAccount $bank): void { if ($bank->wasChanged()) \App\Services\FinanceAudit::record($bank, 'updated', ['changed' => array_keys($bank->getChanges())]); });
    }
}
