<?php

namespace App\Livewire;

use App\Models\Supply;
use App\Models\SupplyCategory;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Livewire\Component;
use Filament\Forms;

class PosTableSection extends Component implements HasActions,HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public $selectedCategory = 'all';
    public $categoryList = [];
    public $selectedSupply;
    public $setPrice = 0;
    

    public function mount()
    {
        $this->categoryList['all'] = 'All';
        $this->categoryList += SupplyCategory::pluck('name', 'id')->toArray();
    }

    public function updateCategory($category)
    {
        $this->selectedCategory = $category;
    }

    public function selectItem($id)
    {
        $this->selectedSupply = Supply::find($id);
        $this->setPrice =  $this->selectedSupply->price;
        $this->dispatch('open-modal', id: 'select-item');
    }

    public function addToCart()
    {
        $this->dispatch(
            'add-to-cart', 
            itemId:$this->selectedSupply?->id,
            price:$this->setPrice 
        );
    }

    public function render()
    {
        $supplies = Supply::query();

        if($this->selectedCategory != 'all'){
            $supplies = $supplies->where('category_id',$this->selectedCategory);
        }

        return view('livewire.pos-table-section',['supplies' => $supplies->get()]);
    }
}
