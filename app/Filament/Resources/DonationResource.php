<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DonationResource\Pages;
use App\Models\Donation;
use App\Models\Programme;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DonationResource extends Resource
{
    protected static ?string $model = Donation::class;
    protected static string|\UnitEnum|null $navigationGroup = 'Organization';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-heart';
    protected static ?string $navigationLabel = 'Donations';
    protected static ?string $recordTitleAttribute = 'donor_name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Donation details')->schema([
                TextInput::make('donor_name')->required()->maxLength(160),
                TextInput::make('email')->email()->required()->maxLength(255),
                TextInput::make('phone')->maxLength(50),
                Select::make('programme_id')->label('Programme')->options(fn (?Donation $record): array => Programme::query()
                    ->where('is_active', true)->when($record?->programme_id, fn ($query, $id) => $query->orWhereKey($id))
                    ->orderBy('display_order')->orderBy('id')->get()->mapWithKeys(fn (Programme $programme) => [$programme->id => $programme->translated('title')])->all())
                    ->searchable()->nullable()->placeholder('Where needed most'),
                TextInput::make('amount')->numeric()->minValue(1)->required(),
                Select::make('currency')->options(['TZS' => 'TZS', 'USD' => 'USD'])->required(),
                Select::make('frequency')->options(Donation::FREQUENCIES)->required(),
                Select::make('purpose')->options(Donation::PURPOSES)->required(),
                Select::make('payment_method')->options(Donation::PAYMENT_METHODS)->required(),
            ])->columns(2)->columnSpanFull(),
            Section::make('Payment tracking')->description('Mark a donation as received only after confirming the payment.')->schema([
                Select::make('status')->options(array_combine(Donation::STATUSES, Donation::STATUSES))->required(),
                TextInput::make('payment_reference')->maxLength(255),
                DateTimePicker::make('received_at')->label('Payment received at'),
                Textarea::make('internal_notes')->rows(4)->columnSpanFull(),
            ])->columns(2)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('created_at')->label('Pledged')->dateTime()->sortable(),
            TextColumn::make('donor_name')->searchable()->sortable(),
            TextColumn::make('email')->searchable(),
            TextColumn::make('amount')->formatStateUsing(fn ($state, Donation $record) => $record->currency.' '.number_format((float) $state, 2))->sortable(),
            TextColumn::make('programme.title.en')->label('Programme')->placeholder('Where needed most')->searchable(),
            TextColumn::make('purpose')->formatStateUsing(fn ($state) => Donation::PURPOSES[$state] ?? $state),
            TextColumn::make('frequency')->formatStateUsing(fn ($state) => Donation::FREQUENCIES[$state] ?? $state),
            TextColumn::make('status')->badge()->color(fn ($state) => match ($state) { 'RECEIVED' => 'success', 'CANCELLED' => 'gray', default => 'warning' })->sortable(),
        ])->defaultSort('created_at', 'desc')->filters([
            SelectFilter::make('status')->options(array_combine(Donation::STATUSES, Donation::STATUSES)),
            SelectFilter::make('programme_id')->label('Programme')->options(fn () => Programme::orderBy('display_order')->orderBy('id')->get()->mapWithKeys(fn (Programme $programme) => [$programme->id => $programme->translated('title')])->all()),
            SelectFilter::make('purpose')->options(Donation::PURPOSES),
            SelectFilter::make('frequency')->options(Donation::FREQUENCIES),
        ])->recordActions([
            \Filament\Actions\EditAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDonations::route('/'),
            'create' => Pages\CreateDonation::route('/create'),
            'edit' => Pages\EditDonation::route('/{record}/edit'),
        ];
    }
}
