<?php

namespace App\Filament\Resources\StockResource\Pages;

use App\Filament\Resources\StockResource;
use App\Models\Branch;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListStocks extends ListRecords
{
    protected static string $resource = StockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('transfer-item')
                ->label('Transfer Stocks'),
            Actions\Action::make('export-stocks')
                ->label('Export')
                ->color('info'),
        ];
    }

    public function getTabs(): array
    {
        $branches = Branch::all();
    
        return $branches->map(function ($branch) {
            return Tab::make($branch->name)
                ->label($branch->name)
                ->modifyQueryUsing(function ($query) use ($branch) {
                    return $query->where('branch_id', $branch->id);
                });
        })->toArray();
    }
}
