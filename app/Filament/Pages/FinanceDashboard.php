<?php

namespace App\Filament\Pages;

use App\Models\Account;
use App\Models\BankAccount;
use App\Models\CashAccount;
use App\Models\FinancialYear;
use Filament\Pages\Page;

class FinanceDashboard extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-pie';
    protected static string|\UnitEnum|null $navigationGroup = 'Finance';
    protected static ?string $navigationLabel = 'Finance Dashboard';
    protected static ?string $title = 'Finance Foundation';
    protected string $view = 'filament.pages.finance-dashboard';

    protected function getViewData(): array
    {
        $year = FinancialYear::whereKey(request()->query('year'))->first()
            ?? FinancialYear::where('status', 'OPEN')->whereDate('start_date', '<=', today())->whereDate('end_date', '>=', today())->first();
        $accountsCount = Account::where('active', true)->count();
        $bankAccounts = BankAccount::with('account')->where('active', true)->orderBy('currency')->orderBy('bank_name')->get();
        $cashAccounts = CashAccount::with(['account', 'department'])->where('active', true)->orderBy('currency')->orderBy('name')->get();

        return [
            'year' => $year,
            'years' => FinancialYear::orderByDesc('start_date')->get(),
            'accountsCount' => $accountsCount,
            'bankAccounts' => $bankAccounts,
            'cashAccounts' => $cashAccounts,
            'bankOpeningByCurrency' => BankAccount::where('active', true)->selectRaw('currency, SUM(opening_balance) as total')->groupBy('currency')->orderBy('currency')->get(),
            'cashOpeningByCurrency' => CashAccount::where('active', true)->selectRaw('currency, SUM(opening_balance) as total')->groupBy('currency')->orderBy('currency')->get(),
            'financeStats' => [
                ['label' => 'Financial year', 'value' => $year?->name ?? 'Not set', 'description' => $year ? $year->start_date->format('M j, Y').' – '.$year->end_date->format('M j, Y').' · '.$year->status : 'Create a financial year to begin', 'icon' => 'heroicon-o-calendar-days', 'tone' => $year?->status === 'OPEN' ? 'mint' : 'amber'],
                ['label' => 'Active chart accounts', 'value' => number_format($accountsCount), 'description' => 'Accounts available for finance setup', 'icon' => 'heroicon-o-rectangle-stack', 'tone' => 'blue'],
                ['label' => 'Active bank accounts', 'value' => number_format($bankAccounts->count()), 'description' => 'Opening balances listed by currency below', 'icon' => 'heroicon-o-building-library', 'tone' => 'violet'],
                ['label' => 'Active cash accounts', 'value' => number_format($cashAccounts->count()), 'description' => 'Petty cash and cash holdings configured', 'icon' => 'heroicon-o-banknotes', 'tone' => 'amber'],
            ],
        ];
    }

    public static function canAccess(): bool { return auth()->user()?->can('finance.dashboard.view') || auth()->user()?->can('organization.full_access'); }
}
