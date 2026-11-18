<?php

namespace App\Filament\Widgets;

use App\Models\ImpactStatistic;
use App\Models\Department;
use App\Models\Position;
use App\Models\Programme;
use App\Models\Project;
use App\Models\TeamMember;
use App\Models\User;
use App\Models\Donation;
use App\Filament\Resources\DonationResource;
use Filament\Widgets\Widget;

class FoundationOverview extends Widget
{
    protected string $view = 'filament.widgets.foundation-overview';

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = null;

    public function getViewData(): array
    {
        $stats = [];
        if (auth()->user()->can('programme.view')) {
            $stats[] = ['label' => 'Published programmes', 'value' => Programme::where('is_active', true)->count(), 'description' => 'Currently visible on the website', 'icon' => 'heroicon-o-document-text', 'tone' => 'blue'];
            $stats[] = ['label' => 'Hidden programmes', 'value' => Programme::where('is_active', false)->count(), 'description' => 'Awaiting publication', 'icon' => 'heroicon-o-eye', 'tone' => 'violet'];
        }
        if (auth()->user()->can('user.manage')) {
            $stats[] = ['label' => 'Active CMS users', 'value' => User::where('is_active', true)->count(), 'description' => 'Active accounts, subject to permissions', 'icon' => 'heroicon-o-users', 'tone' => 'mint'];
        }

        if (auth()->user()->can('project.view')) {
            $stats[] = ['label' => 'Active projects', 'value' => Project::where('status', \App\Enums\ProjectStatus::Active)->count(), 'description' => 'Across current project records', 'icon' => 'heroicon-o-briefcase', 'tone' => 'amber'];
        }
        if (auth()->user()->can('impact.view')) {
            $reach = ImpactStatistic::published()->where('metric', 'people_reached')->whereNull('programme_id')->whereNull('project_id')->latest('as_of_date')->orderByDesc('id')->first();
            if ($reach) {
                $stats[] = ['label' => 'People reached', 'value' => $reach->displayNumber().$reach->suffix, 'description' => 'Organization-wide · '.$reach->as_of_date->format('d M Y'), 'icon' => 'heroicon-o-chart-bar', 'tone' => 'blue'];
            }
        }
        if (auth()->user()->can('department.view')) {
            $stats[] = ['label' => 'Departments', 'value' => Department::where('active', true)->count(), 'description' => 'Active internal departments', 'icon' => 'heroicon-o-building-office-2', 'tone' => 'blue'];
            $stats[] = ['label' => 'Team members', 'value' => TeamMember::where('active', true)->count(), 'description' => 'People records, login optional', 'icon' => 'heroicon-o-user-group', 'tone' => 'violet'];
            $stats[] = ['label' => 'Positions', 'value' => Position::where('active', true)->count(), 'description' => 'Active organizational positions', 'icon' => 'heroicon-o-identification', 'tone' => 'mint'];
        }

        if (auth()->user()->can('donation.view')) {
            $pending = Donation::where('status', 'PENDING')->count();
            $received = Donation::where('status', 'RECEIVED')->count();
            $totals = Donation::where('status', 'RECEIVED')->selectRaw('currency, SUM(amount) as total')->groupBy('currency')->get()
                ->map(fn ($row) => $row->currency.' '.number_format((float) $row->total, 2))->join(' · ');
            $url = DonationResource::getUrl('index');

            $stats[] = ['label' => 'Pending donations', 'value' => $pending, 'description' => 'Awaiting payment confirmation · Manage donations', 'icon' => 'heroicon-o-clock', 'tone' => 'amber', 'url' => $url, 'group' => 'donations'];
            $stats[] = ['label' => 'Received donations', 'value' => $received, 'description' => $totals ?: 'No confirmed payments yet', 'icon' => 'heroicon-o-heart', 'tone' => 'mint', 'url' => $url, 'group' => 'donations'];
        }

        return ['stats' => $stats];
    }
}
