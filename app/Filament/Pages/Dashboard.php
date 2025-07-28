<?php

namespace App\Filament\Pages;

use App\Enums\RolesEnum;
use App\Filament\Widgets\SalesByBranchChartWidget;
use App\Filament\Widgets\SalesByProductCategoryWidget;
use App\Filament\Widgets\SalesChartWidget;
use App\Filament\Widgets\SaleStatOverviewWidget;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('')
                    ->schema([
                        Select::make('branch')
                            ->label('Select Branch')
                            ->placeholder('All Branch')
                            ->options([
                                1 => 'Branch 1',
                                2 => 'Branch 2',
                                3 => 'Branch 3',
                            ])
                            ->reactive(),
                        
                        Select::make('timePeriod')
                            ->label('Select Time Period')
                            ->options([
                                'today' => 'Today',
                                'this_week' => 'This Week',
                                'this_month' => 'This Month',
                                'custom' => 'Custom Date Range',
                            ])
                            ->default('today')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state === 'custom') {
                                    $set('startDate', null);
                                    $set('endDate', null);
                                } else {
                                    $set('startDate', null);
                                    $set('endDate', null);
                                }
                            }),

                        DatePicker::make('startDate')
                            ->label('Start Date')
                            ->disabled(fn ($get) => $get('timePeriod') !== 'custom'),

                        DatePicker::make('endDate')
                            ->label('End Date')
                            ->disabled(fn ($get) => $get('timePeriod') !== 'custom'),
                    ])
                    ->columns(4),
            ]);
    }

    public static function canAccess(): bool
    {   
        if(auth()->user()->hasRole([RolesEnum::CASHIER->value])){
            return false;
        }
        return true;
    }

    public function getWidgets(): array
    {
        return [
            SaleStatOverviewWidget::class,
            SalesChartWidget::class,
            SalesByBranchChartWidget::class,
            SalesByProductCategoryWidget::class,
        ];
    }
}
