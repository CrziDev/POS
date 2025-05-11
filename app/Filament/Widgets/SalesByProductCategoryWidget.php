<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class SalesByProductCategoryWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Sales by Product Category';
    protected static ?string $maxHeight = '380px';


    protected int | string | array $columnSpan = [
        'default' => 'full',
        'md' => 'half',
    ];

    protected function getData(): array
    {
        // Dummy data for categories, total sales, units sold, and gross revenue
        $categories = [
            ['category_name' => 'Electronics', 'total_sales' => rand(50000, 100000), 'units_sold' => rand(200, 500), 'gross_revenue' => rand(20000, 60000)],
            ['category_name' => 'Furniture', 'total_sales' => rand(30000, 60000), 'units_sold' => rand(150, 400), 'gross_revenue' => rand(15000, 50000)],
            ['category_name' => 'Clothing', 'total_sales' => rand(20000, 50000), 'units_sold' => rand(300, 600), 'gross_revenue' => rand(10000, 40000)],
            ['category_name' => 'Toys', 'total_sales' => rand(15000, 40000), 'units_sold' => rand(100, 300), 'gross_revenue' => rand(5000, 30000)],
            ['category_name' => 'Groceries', 'total_sales' => rand(50000, 150000), 'units_sold' => rand(500, 1000), 'gross_revenue' => rand(30000, 90000)],
        ];

        // Prepare data for the chart
        $labels = array_map(fn($category) => $category['category_name'], $categories);
        $totalSales = array_map(fn($category) => $category['total_sales'], $categories);

        return [
            'datasets' => [
                [
                    'data' => $totalSales,
                    'backgroundColor' => ['#3b82f6', '#10b981', '#f97316', '#9333ea', '#eab308'], // Different colors for each category
                    'hoverBackgroundColor' => ['#2563eb', '#16a34a', '#d97706', '#7e22ce', '#ca8a04'], // Lighter shade on hover
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie'; // Specify that this is a pie chart
    }

    protected function getDefaultOptions(): array
    {
        return [
            'plugins' => [
                'tooltip' => [
                    'enabled' => true,
                    'callbacks' => [
                        'label' => 'function(tooltipItem) {
                            return tooltipItem.label + ": " + tooltipItem.raw;
                        }',
                    ],
                ],
                'legend' => [
                    'position' => 'top', // Display the legend at the top of the chart
                    'labels' => [
                        'font' => [
                            'size' => 14,
                            'weight' => 'bold',
                        ],
                        'color' => '#333', // Text color for legend items
                    ],
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
        ];
    }
}
