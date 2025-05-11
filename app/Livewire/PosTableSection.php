<?php

namespace App\Livewire;

use App\Models\Stock;
use App\Models\Supply;
use App\Models\SupplyCategory;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Livewire\Component;
use Filament\Forms;
use Filament\Notifications\Notification;
use Livewire\Attributes\On;

class PosTableSection extends Component implements HasActions,HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public $selectedCategory = 'all';
    public $search = '';
    public $categoryList = [];
    public $selectedSupply;
    public $scanning = false;

    ## Selecting Item
    public $setPrice = 0;
    public $setQuantity = 1;


    public function mount()
    {
        $this->categoryList['all'] = 'All';
        $this->categoryList += SupplyCategory::pluck('name', 'id')->toArray();
    }

    #[On('refreshTable')]
    public function refreshContent()
    {
        $this->dispatch('$refresh');
    }

    public function updateCategory($category)
    {
        $this->selectedCategory = $category;
    }

    public function selectItem($id)
    {
        $currentBranch = auth()->user()->employee->branch;


        $this->selectedSupply = Stock::with('supply')->find($id);
        
        $this->setPrice =  $this->selectedSupply->supply->price;
                            
        if($this->selectedSupply->quantity == 0){
            Notification::make()
                ->title('No Available Stock For this Product')
                ->danger()
                ->send();
            return;
        };

        $this->dispatch('open-modal', id: 'select-item');
    }

    public function addToCart()
    {
        $maxPrice = $this->selectedSupply?->supply->price ?? 0;
        $maxQty = $this->selectedSupply?->quantity ?? 0;
    
        $this->validate([
            'setPrice' => ['required', 'numeric', 'min:0.01', "max:$maxPrice"],
            'setQuantity' => ['required', 'integer', 'min:1', "max:$maxQty"],
        ]);

        $this->dispatch(
            'add-to-cart', 
            itemId:$this->selectedSupply?->supply->id,
            price:$this->setPrice,
            quantity:$this->setQuantity,
            stock:$maxQty 
        );
    }

    public function render()
    {
        $currentBranch = auth()->user()->employee->branch;
        
        $stocks = Stock::query()
            ->where('branch_id', $currentBranch->branch_id)
            ->when($this->selectedCategory !== 'all', function ($query) {
                $query->whereHas('supply', function ($q) {
                    $q->where('category_id', $this->selectedCategory);
                });
            })
            ->when($this->search !== '', function ($query) {
                $query->whereHas('supply', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                    ->orwhere('sku', 'like', '%' . $this->search . '%');
                });
            });
            
        return view('livewire.pos-table-section', [
            'stocks' => $stocks->get(),
        ]);
    }

    public function handleBarcode($code)
    {
        $this->scanning = true;

        try {
            $supply = Supply::where('sku', $code)->first();

            if (!$supply) {
                Notification::make()
                    ->title("No item found for code: $code")
                    ->danger()
                    ->send();
                return;
            }

            $this->selectItem($supply->id);
        } finally {
            $this->scanning = false;
        }
    }

}
