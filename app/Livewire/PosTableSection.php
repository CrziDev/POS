<?php

namespace App\Livewire;

use App\Models\Supply;
use App\Models\SupplyCategory;
use Livewire\Component;

class PosTableSection extends Component
{

    public $selectedCategory = 'all';
    public $categoryList = [];

    public function mount()
    {
        $this->categoryList['all'] = 'All';
        $this->categoryList += SupplyCategory::pluck('name', 'id')->toArray();
    }

    public function updateCategory($category)
    {
        $this->selectedCategory = $category;
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
