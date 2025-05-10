<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SalesChartWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = '📊 Monthly Sales Overview';
    protected static ?string $description = 'Tracks monthly sales totals for the selected year';
    protected static ?string $maxHeight = '400px';

    protected int | string | array $columnSpan = [
        'default' => 2,
        'md' => 2,
        'lg' => 2,
        'xl' => 2,
    ];

    protected function getData(): array
    {
        // Get start date from filters or default to current year
        $start = $this->filters['startDate'] ?? now()->startOfYear();
        $year = Carbon::parse($start)->year;

        // Optional: Replace this block with actual DB query if needed
        // Simulate monthly totals with dummy data
        $monthlySales = collect(range(1, 12))->mapWithKeys(function ($month) use ($year) {
            $monthName = Carbon::create($year, $month)->format('F');
            return [$monthName => fake()->numberBetween(10000, 150000)];
        });

        return [
            'datasets' => [
                [
                    'label' => "Sales for {$year}",
                    'data' => $monthlySales->values(),
                    'backgroundColor' => [
                        '#4f46e5', '#6366f1', '#818cf8', '#a5b4fc',
                        '#c7d2fe', '#dbeafe', '#93c5fd', '#60a5fa',
                        '#3b82f6', '#2563eb', '#1d4ed8', '#1e40af',
                    ],
                    'borderRadius' => 6,
                ],
            ],
            'labels' => $monthlySales->keys(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    public static function getDefaultOptions(): array
    {
        return [
            'options' => [
                'scales' => [
                    'y' => [
                        'beginAtZero' => true,
                        'ticks' => [
                            'callback' => function ($value) {
                                return '₱' . number_format($value);
                            },
                        ],
                    ],
                ],
                'plugins' => [
                    'legend' => [
                        'display' => true,
                        'position' => 'top',
                    ],
                    'tooltip' => [
                        'callbacks' => [
                            'label' => function ($tooltipItem) {
                                return '₱' . number_format($tooltipItem->raw);
                            },
                        ],
                    ],
                ],
            ],
        ];
    }
}
