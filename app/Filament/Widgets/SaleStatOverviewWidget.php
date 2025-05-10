<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Str;

class SaleStatOverviewWidget extends BaseWidget
{
    protected int | string | array $columnSpan = [
        'default' => 1,
        'md' => 2,
        'lg' => 2,
        'xl' => 2,
    ];

    protected function getStats(): array
    {
        // Simulated totals
        $totalSales = rand(10000, 100000);
        $totalTransactions = rand(100, 500);
        $totalDiscounts = rand(1000, 7000);
        $totalRefunds = rand(500, 5000);

        // 7-day trend data
        $trendData = collect(range(1, 7))->map(fn () => rand(100, 500))->toArray();

        return [
            Stat::make('Total Sales', numberToMoney($totalSales))
                ->description('Sales this period')
                ->descriptionIcon('heroicon-m-banknotes')
                ->chart($trendData)
                ->color('success')
                ->extraAttributes(['class' => 'font-semibold']),

            Stat::make('Total Transactions', number_format($totalTransactions))
                ->description('Successful orders')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->chart($trendData)
                ->color('info')
                ->extraAttributes(['class' => 'font-semibold']),

            Stat::make('Discount Given', numberToMoney($totalDiscounts))
                ->description('Promotions applied')
                ->descriptionIcon('heroicon-m-tag')
                ->chart($trendData)
                ->color('warning')
                ->extraAttributes(['class' => 'font-semibold']),

            Stat::make('Refund Processed', numberToMoney($totalRefunds))
                ->description('Returned transactions')
                ->descriptionIcon('heroicon-m-arrow-uturn-left')
                ->chart($trendData)
                ->color('danger')
                ->extraAttributes(['class' => 'font-semibold']),
        ];
    }
}
