<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class SalesByBranchChartWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Total Sales by Branch';
    protected static ?string $minHeight = '380px';

    protected int | string | array $columnSpan = [
        'default' => 'full',
        'md' => 'half',
    ];

    protected function getData(): array
    {
        $branches = [
            1 => 'Branch 1',
            2 => 'Branch 2',
            3 => 'Branch 3',
            4 => 'Branch 4',
            5 => 'Branch 5',
        ];

        $salesData = [
            1 => rand(10000, 50000),  // Branch 1
            2 => rand(15000, 45000),  // Branch 2
            3 => rand(20000, 60000),  // Branch 3
            4 => rand(25000, 70000),  // Branch 4
            5 => rand(30000, 80000),  // Branch 5
        ];

        $labels = array_values($branches); 
        $values = array_values($salesData); 

        return [
            'datasets' => [
                [
                    'label' => 'Total Sales', 
                    'data' => $values,
                    'backgroundColor' => [
                        '#4caf50', // Branch 1 color (green
                        '#2196f3', // Branch 2 color (blue)
                        '#ff9800', // Branch 3 color (orange)
                        '#f44336', // Branch 4 color (red)
                        '#9c27b0', // Branch 5 color (purple)
                    ],
                    'hoverBackgroundColor' => '#e2e8f0',
                    'borderColor' => '#ffffff', // White border around bars
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getDefaultOptions(): array
    {
        return [
            'responsive' => true,
            'plugins' => [
                'legend' => [
                    'position' => 'top',
                    'labels' => [
                        'font' => [
                            'family' => 'Roboto, sans-serif',
                            'weight' => 'bold',
                            'size' => 14,
                        ],
                    ],
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => function ($tooltipItem) {
                            return '₱' . number_format($tooltipItem->raw);
                        },
                    ],
                ],
                'title' => [
                    'display' => true,
                    'text' => 'Total Sales by Branch', // Chart title
                    'font' => [
                        'size' => 18,
                        'weight' => 'bold',
                    ],
                ],
            ],
            'scales' => [
                'x' => [
                    'ticks' => [
                        'autoSkip' => false, // Avoid skipping tick labels
                        'maxRotation' => 45, // Rotate x-axis labels to avoid overlap
                        'minRotation' => 45,
                    ],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => function ($value) {
                            return '₱' . number_format($value);
                        },
                    ],
                ],
            ],
        ];
    }
}
