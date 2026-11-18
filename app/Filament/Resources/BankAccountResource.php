<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BankAccountResource\Pages;
use App\Models\Account;
use App\Models\BankAccount;
use App\Support\CmsCreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BankAccountResource extends Resource
{
    protected static ?string $model = BankAccount::class;
    protected static string|\UnitEnum|null $navigationGroup = 'Finance';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $navigationLabel = 'Bank Accounts';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([Section::make('Bank account')->description('Only store an already-masked account reference. Never enter an online banking password, PIN, OTP, or full security credentials.')->schema([
            TextInput::make('bank_name')->required()->maxLength(160), TextInput::make('account_name')->required()->maxLength(160),
            TextInput::make('masked_account_number')->label('Masked account reference')->placeholder('••••1234')->maxLength(80),
            Select::make('currency')->options(['TZS' => 'TZS', 'USD' => 'USD', 'EUR' => 'EUR', 'GBP' => 'GBP'])->default('TZS')->required(),
            TextInput::make('account_type')->maxLength(40), TextInput::make('opening_balance')->numeric()->minValue(0)->default('0.00')->required(),
            Select::make('account_id')->label('Chart of accounts')->options(fn () => Account::query()->where('type', 'ASSET')->where('allow_transactions', true)->where('active', true)->orderBy('code')->get()->mapWithKeys(fn ($a) => [$a->id => $a->code.' · '.$a->name]))->searchable()->required()->unique(ignoreRecord: true),
            Textarea::make('notes')->rows(3)->columnSpanFull(), Toggle::make('active')->default(true),
        ])->columns(2)]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('bank_name')->searchable()->sortable(), TextColumn::make('account_name')->searchable(), TextColumn::make('masked_account_number')->label('Account reference')->placeholder('Not recorded'), TextColumn::make('currency')->badge(), TextColumn::make('account.code')->label('Ledger account'), TextColumn::make('opening_balance')->money(fn ($record) => $record->currency), IconColumn::make('active')->boolean()])
            ->filters([TernaryFilter::make('active')])->recordActions([EditAction::make()])->headerActions([CmsCreateAction::make(static::class)]);
    }

    public static function getEloquentQuery(): Builder { return parent::getEloquentQuery()->with('account'); }
    public static function getPages(): array { return ['index' => Pages\ListBankAccounts::route('/'), 'create' => Pages\CreateBankAccount::route('/create'), 'edit' => Pages\EditBankAccount::route('/{record}/edit')]; }
}
