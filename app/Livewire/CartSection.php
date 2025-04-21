<?php

namespace App\Livewire;

use App\Models\Supply;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportEvents\BaseOn;

class CartSection extends Component
{

    public $cart = [];


    #[On('add-to-cart')] 
    public function updatePostList($itemId)
    {   
        $product = Supply::find($itemId);

        if (!$product) return;

        $exists = collect($this->cart)->firstWhere('id',$itemId);

        if ($exists) {
            foreach ($this->cart as &$cartItem){
                if ($cartItem['id'] === $itemId) {
                    $cartItem['qty'] += 1;
                    break;
                }
            }
        } else {
            $this->cart[] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'qty' => 1
            ];
        }
    }

    public function increaseItem($itemId){
        foreach ($this->cart as &$cartItem){
            if ($cartItem['id'] === $itemId) {
                $cartItem['qty'] += 1;
                break;
            }
        }
    }

    public function decreaseItem($itemId){
        foreach ($this->cart as &$cartItem){
            if ($cartItem['id'] === $itemId) {
                if( $cartItem['qty'] == 0){
                    break;
                }
                $cartItem['qty'] -= 1;
                break;
            }
        }
    }

    public function render()
    {
        return view('livewire.cart-section');
    }
}
