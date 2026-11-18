<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\BankAccount;
use App\Models\CashAccount;
use App\Models\Department;
use App\Models\FinancialYear;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class FinanceFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_finance_foundation_seeds_current_financial_year_and_starter_account_hierarchy(): void
    {
        $year = FinancialYear::where('name', today()->format('Y'))->firstOrFail();
        $this->assertSame('OPEN', $year->status);
        $this->assertSame(today()->startOfYear()->toDateString(), $year->start_date->toDateString());
        $asset = Account::where('code', '1000')->firstOrFail();
        $this->assertFalse($asset->allow_transactions);
        $this->assertSame($asset->id, Account::where('code', '1100')->value('parent_id'));
        $this->assertDatabaseHas('accounts', ['code' => '5000', 'type' => 'EXPENSE', 'normal_balance' => 'DEBIT']);
    }

    public function test_accounts_enforce_normal_balance_and_parent_hierarchy(): void
    {
        $parent = Account::create(['code' => 'TEST-ASSET-GROUP', 'name' => 'Test asset group', 'type' => 'ASSET', 'normal_balance' => 'DEBIT', 'allow_transactions' => false]);
        $child = Account::create(['parent_id' => $parent->id, 'code' => 'TEST-ASSET-LEAF', 'name' => 'Test bank ledger', 'type' => 'ASSET', 'normal_balance' => 'DEBIT', 'allow_transactions' => true]);
        $this->assertSame($parent->id, $child->parent->id);

        try {
            Account::create(['code' => 'TEST-WRONG-BALANCE', 'name' => 'Invalid balance', 'type' => 'INCOME', 'normal_balance' => 'DEBIT']);
            $this->fail('A mismatched normal balance was accepted.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('normal_balance', $exception->errors());
        }

        $this->expectException(ValidationException::class);
        $child->update(['parent_id' => $child->id]);
    }

    public function test_bank_and_cash_accounts_require_transaction_enabled_asset_accounts(): void
    {
        $income = Account::where('code', '4000')->firstOrFail();
        $this->expectException(ValidationException::class);
        BankAccount::create(['account_id' => $income->id, 'bank_name' => 'Example Bank', 'account_name' => 'UGHI account', 'currency' => 'TZS', 'opening_balance' => '0.00']);
    }

    public function test_masked_bank_reference_is_kept_and_currencies_are_normalized(): void
    {
        $account = Account::where('code', '1100')->firstOrFail();
        $bank = BankAccount::create(['account_id' => $account->id, 'bank_name' => 'Test Bank', 'account_name' => 'UGHI Operating', 'masked_account_number' => '•••• 1234', 'currency' => 'tzs', 'opening_balance' => '12345.67']);
        $this->assertSame('TZS', $bank->currency);
        $this->assertSame('12345.67', $bank->opening_balance);
        $this->assertDatabaseHas('bank_accounts', ['id' => $bank->id, 'masked_account_number' => '•••• 1234']);
    }

    public function test_cash_account_scope_is_enforced_for_read_access(): void
    {
        $department = Department::where('code', 'PROG')->firstOrFail();
        $cashAccount = Account::where('code', '1200')->firstOrFail();
        $cash = CashAccount::create(['account_id' => $cashAccount->id, 'name' => 'Programme petty cash', 'department_id' => $department->id, 'currency' => 'TZS', 'opening_balance' => '0.00']);
        $user = User::factory()->create();
        $user->givePermissionTo('finance.cash.view');
        $this->assertFalse($user->can('view', $cash));
        $user->departments()->attach($department, ['active' => true]);
        $this->assertTrue($user->can('view', $cash));
    }

    public function test_finance_pages_require_explicit_finance_permission(): void
    {
        $user = User::factory()->create(['must_change_password' => false]);
        $user->givePermissionTo('admin.access');
        $this->actingAs($user);
        $this->get('/admin/finance-dashboard')->assertForbidden();
        $user->givePermissionTo('finance.dashboard.view');
        $this->get('/admin/finance-dashboard')->assertOk()->assertSee('Finance Foundation');
    }
}
