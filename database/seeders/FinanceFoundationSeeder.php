<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\FinancialYear;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class FinanceFoundationSeeder extends Seeder
{
    public function run(): void
    {
        $financePermissions = ['admin.access', 'finance.dashboard.view', 'finance.year.view', 'finance.year.manage', 'finance.account.view', 'finance.account.manage', 'finance.bank.view', 'finance.bank.manage', 'finance.cash.view', 'finance.cash.manage'];
        $role = Role::firstOrCreate(['name' => 'Finance Officer', 'guard_name' => 'web']);
        if ($role->wasRecentlyCreated) $role->syncPermissions($financePermissions);
        else $role->givePermissionTo(Permission::whereIn('name', $financePermissions)->where('guard_name', 'web')->get());

        $year = (int) today()->format('Y');
        FinancialYear::firstOrCreate(['name' => (string) $year], [
            'start_date' => today()->setDate($year, 1, 1),
            'end_date' => today()->setDate($year, 12, 31),
            'status' => 'OPEN',
        ]);

        $accounts = [
            ['code' => '1000', 'name' => 'Assets', 'type' => 'ASSET', 'normal_balance' => 'DEBIT', 'allow_transactions' => false, 'display_order' => 10],
            ['code' => '1100', 'name' => 'Bank Accounts', 'type' => 'ASSET', 'normal_balance' => 'DEBIT', 'parent' => '1000', 'display_order' => 20],
            ['code' => '1200', 'name' => 'Cash Accounts', 'type' => 'ASSET', 'normal_balance' => 'DEBIT', 'parent' => '1000', 'display_order' => 30],
            ['code' => '2000', 'name' => 'Liabilities', 'type' => 'LIABILITY', 'normal_balance' => 'CREDIT', 'allow_transactions' => false, 'display_order' => 40],
            ['code' => '3000', 'name' => 'Equity', 'type' => 'EQUITY', 'normal_balance' => 'CREDIT', 'allow_transactions' => false, 'display_order' => 50],
            ['code' => '4000', 'name' => 'Income', 'type' => 'INCOME', 'normal_balance' => 'CREDIT', 'allow_transactions' => false, 'display_order' => 60],
            ['code' => '5000', 'name' => 'Expenses', 'type' => 'EXPENSE', 'normal_balance' => 'DEBIT', 'allow_transactions' => false, 'display_order' => 70],
        ];
        foreach ($accounts as $definition) {
            $parentCode = $definition['parent'] ?? null;
            unset($definition['parent']);
            $parentId = $parentCode ? Account::where('code', $parentCode)->value('id') : null;
            Account::firstOrCreate(['code' => $definition['code']], [...$definition, 'parent_id' => $parentId, 'active' => true]);
        }
    }
}
