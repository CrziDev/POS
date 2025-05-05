<?php

namespace App\Livewire;

use App\Models\Supply;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Enums\MaxWidth;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportEvents\BaseOn;

use function Pest\Laravel\options;

class CartSection extends Component implements HasActions,HasForms
{

    use InteractsWithActions;
    use InteractsWithForms;

    public $cart = [];


    #[On('add-to-cart')] 
    public function updatePostList($itemId,$price)
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
                'retail_price' => $product->price,
                'price' => $price,
                'qty' => 1
            ];
        }

        $this->dispatch('close-modal',id: 'select-item');
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

    public function checkOut(): Action
    {
       return Action::make('checkOut')
                ->label('Items Checkedout')
                ->modalWidth(MaxWidth::SevenExtraLarge)
                ->form([

                    Split::make([
                        Section::make('Items')->make([
                            Repeater::make('items')
                                ->label('')
                                ->schema([
                                    TextInput::make('name')->required(),
                                    TextInput::make('retail_price')->required(),
                                    TextInput::make('qty')->required(),
                                    TextInput::make('price')->required(),
                                ])
                                ->afterStateHydrated(function($set,$get){
                                    $set('items',$this->cart);

                                })
                                ->reorderable(false)
                                ->deletable(false)
                                ->addable(false)
                                ->columns(4)
                            
                        ]),

                        Section::make('Payment')->schema([

                            Select::make('customer')
                                ->placeholder('Select Customer')
                                ->options([]),
    
                            Select::make('payment_method')
                                ->options([
                                    'gCash' => 'G-Cash',
                                    'cash'  => 'Cash',
                                ])
                                ->live(),
                            
                            Split::make([
                                TextInput::make('reference_number')
                                    ->visible(fn($get)=>$get('payment_method') == 'gCash'),
                                TextInput::make('amount'),
                            ])
                        ])
                    ]),
                ]);
    }

    public function render()
    {
        return view('livewire.cart-section');
    }
}
