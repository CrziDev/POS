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
                ->label('Transfer Stocks')
                ->visible(fn()=>auth()->user()->hasRole('admin'))
                ->icon('heroicon-m-arrow-right-on-rectangle'), 
            
            Actions\Action::make('export-stocks')
                ->label('Export')
                ->color('info')
                ->icon('heroicon-m-arrow-down-tray'), 
            
        ];
    }

    public function getTabs(): array
    {
         if (auth_user()->hasRole(['admin','super-admin'])) {
            $branches = Branch::all();
        }else{
            $branches = Branch::whereIn('id',auth_user()->employee->branch()->pluck('branch_id'))->get();
        }
    
        return $branches->map(function ($branch) {
            return Tab::make($branch->name)
                ->label($branch->name)
                ->modifyQueryUsing(function ($query) use ($branch) {
                    return $query->where('branch_id', $branch->id);
                });
        })->toArray();
    }
}
