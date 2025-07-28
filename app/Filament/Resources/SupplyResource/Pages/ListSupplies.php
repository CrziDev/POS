<?php

namespace App\Filament\Resources\SupplyResource\Pages;

use App\Filament\Resources\SupplyResource;
use App\Models\Supply;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;
use Milon\Barcode\DNS1D;
use Milon\Barcode\Facades\DNS1DFacade;

class ListSupplies extends ListRecords
{
    protected static string $resource = SupplyResource::class;

    
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generate-barcode')
                ->color('info')
                ->modalWidth(MaxWidth::SixExtraLarge)
                ->modalContent(function () {
                    $barcodes = Supply::generateBarcode();
                    return view('SupplyResource.GenerateBarCode', compact('barcodes'));
                })
                ->modalSubmitActionLabel('Generate')
                ->action(fn () => $this->dispatch('post-created'))
                ->icon('heroicon-o-tag') 
                ->label('Generate Barcode'), 
    
            Actions\Action::make('export-stocks')
                ->label('Export Stocks')
                ->color('info')
                ->icon('heroicon-o-arrow-down') 
                ->label('Export Stocks'), 
    
            Actions\CreateAction::make()
                ->modalSubmitActionLabel('Save')
                ->extraModalFooterActions(fn (Actions\CreateAction $action): array => [
                    $action->makeModalSubmitAction('createAnother', arguments: ['another' => true])
                        ->label('Save & add another'),
                ])
                ->label('New Supply')
                ->icon('heroicon-m-plus') 
        ];
    }
    
}
