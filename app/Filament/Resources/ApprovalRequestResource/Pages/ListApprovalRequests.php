<?php
namespace App\Filament\Resources\ApprovalRequestResource\Pages;
use App\Filament\Resources\ApprovalRequestResource;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListApprovalRequests extends ListRecords
{
    protected static string $resource = ApprovalRequestResource::class;

    public function getTabs(): array
    {
        return [
            'pending' => Tab::make('Pending')->query(fn (Builder $query) => $query->whereIn('status', ['SUBMITTED', 'UNDER_REVIEW'])),
            'approved' => Tab::make('Approved')->query(fn (Builder $query) => $query->where('status', 'APPROVED')),
            'returned' => Tab::make('Returned')->query(fn (Builder $query) => $query->where('status', 'RETURNED')),
            'rejected' => Tab::make('Rejected')->query(fn (Builder $query) => $query->where('status', 'REJECTED')),
            'completed' => Tab::make('Completed')->query(fn (Builder $query) => $query->whereIn('status', ['APPROVED', 'REJECTED', 'CANCELLED'])),
        ];
    }
}
