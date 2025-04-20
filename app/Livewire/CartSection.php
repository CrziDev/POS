<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportEvents\BaseOn;

class CartSection extends Component
{

    public $cart = [];


    #[On('add-to-cart')] 
    public function updatePostList($item)
    {
        dd('test');
        collect($this->cart)->firstWhere('id',$item->id);
    }

    public function render()
    {
        return view('livewire.cart-section');
    }
}
