<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\User;
use App\Models\Tenant;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $tenantId = auth()->user()->tenant_id;

        return [
            Stat::make('Total Users', User::where('tenant_id', $tenantId)->count())
                ->description('Users in your organization')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make('Active Tenant', Tenant::find($tenantId)?->name ?? 'N/A')
                ->description('Current organization')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('primary'),

            Stat::make('System Status', 'Operational')
                ->description('All systems running')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
