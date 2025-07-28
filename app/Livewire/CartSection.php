<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\SaleTransaction;
use App\Models\Stock;
use App\Models\Supply;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\RawJs;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;
use Livewire\Component;

class CartSection extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public $currentBranch = null;
    public $cart = [];
    public $totalAmount = 0;
    public $grandTotal = 0;
    public $editingPrice = null; // Track which item is being edited
    public $newPrice = null; // Store the new price input

    public function mount()
    {
        $this->currentBranch = auth()->user()->employee->branch()->first();
    }

    #[On('add-to-cart')] 
    public function updatePostList($itemId, $price, $quantity, $stock)
    {   
        $product = Supply::find($itemId);

        if (!$product) return;

        $exists = collect($this->cart)->firstWhere('id', $itemId);

        if ($exists) {
            foreach ($this->cart as &$cartItem) {
                if ($cartItem['id'] === $itemId) {
                    if ($cartItem['stock'] == $cartItem['qty']) {
                        Notification::make()
                            ->title('No More Available Stocks')
                            ->danger()
                            ->send();
                        break;
                    }
    
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
                'qty' => $quantity,
                'stock' => $stock,
            ];
        }

        $this->dispatch('close-modal', id: 'select-item');
    }

    public function increaseItem($itemId)
    {
        foreach ($this->cart as &$cartItem) {
            if ($cartItem['stock'] == $cartItem['qty']) {
                Notification::make()
                    ->title('No More Available Stocks')
                    ->danger()
                    ->send();
                break;
            }

            if ($cartItem['id'] === $itemId) {
                $cartItem['qty'] += 1;
                break;
            }
        }
    }

    public function decreaseItem($itemId)
    {
        foreach ($this->cart as &$cartItem) {
            if ($cartItem['id'] === $itemId) {
                if ($cartItem['qty'] == 0) {
                    break;
                }
                $cartItem['qty'] -= 1;
                break;
            }
        }
    }

    public function removeItem($itemId)
    {
        $this->cart = collect($this->cart)
            ->reject(fn ($item) => $item['id'] === $itemId)
            ->values()
            ->all();
        $this->editingPrice = null; // Reset editing state
    }

    public function editPrice($itemId)
    {
        $this->editingPrice = $itemId;
        $item = collect($this->cart)->firstWhere('id', $itemId);
        $this->newPrice = $item['price']; 
    }

    public function updatePrice($itemId)
    {
        if ($this->newPrice === null || $this->newPrice < 0) {
            Notification::make()
                ->title('Invalid Price')
                ->body('Please enter a valid price.')
                ->danger()
                ->send();
            return;
        }

        foreach ($this->cart as &$cartItem) {
            if ($cartItem['id'] === $itemId) {
                $cartItem['price'] = (float) $this->newPrice;
                break;
            }
        }

        $this->editingPrice = null; // Exit editing mode
        $this->newPrice = null; // Clear input
        Notification::make()
            ->title('Price Updated')
            ->success()
            ->send();
    }

    public function checkOut(): Action
    {
        return Action::make('checkOut')
            ->label('Purchase Information')
            ->modalWidth(MaxWidth::SevenExtraLarge)
            ->mountUsing(function($action) {
                if (count($this->cart) == 0) {
                    Notification::make()
                        ->title('Cart Is Empty. Please Select Items')
                        ->danger()
                        ->send();
                    $action->cancel();
                }
            })
            ->form([
                Split::make([
                    Section::make('Items')->schema([
                        Placeholder::make('items_list')
                            ->label('')
                            ->content(function () {
                                if (empty($this->cart)) {
                                    return 'No items in cart.';
                                }
                    
                                $grandTotal = 0;
                                $totalDiscount = 0;
                    
                                $rows = collect($this->cart)->map(function ($item) use (&$totalDiscount, &$grandTotal) {
                                    $qty = (float) $item['qty'];
                                    $retail = (float) $item['retail_price'];
                                    $price = (float) $item['price'];
                                    $total = $qty * $price;
                                    $discount = ($retail - $price) * $qty;
                                    $grandTotal += $total;
                                    $totalDiscount += $discount;
                    
                                    $retailDisplay = $retail !== $price
                                        ? "<div class='line-through text-xs text-red-400'>₱" . number_format($retail, 2) . "</div>
                                           <div>₱" . number_format($price, 2) . "</div>"
                                        : "₱" . number_format($retail, 2);
                    
                                    return "<tr>
                                        <td class='border px-2 py-1'>{$item['name']}</td>
                                        <td class='border px-2 py-1 text-right'>{$retailDisplay}</td>
                                        <td class='border px-2 py-1 text-center'>{$qty}</td>
                                        <td class='border px-2 py-1 text-right'>₱" . number_format($total, 2) . "</td>
                                        <td class='border px-2 py-1 text-right text-rose-500'>₱" . number_format($discount, 2) . "</td>
                                    </tr>";
                                })->implode('');
                                
                                return new HtmlString('
                                    <div class="overflow-auto max-h-96">
                                        <table class="w-full text-sm border-collapse bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                                            <thead>
                                                <tr class="bg-gray-100 dark:bg-gray-700">
                                                    <th class="border px-2 py-1 text-left">Name</th>
                                                    <th class="border px-2 py-1 text-right">Price</th>
                                                    <th class="border px-2 py-1 text-center">Qty</th>
                                                    <th class="border px-2 py-1 text-right">Total</th>
                                                    <th class="border px-2 py-1 text-right">Discount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                ' . $rows . '
                                            </tbody>
                                            <tfoot>
                                                <tr class="bg-gray-200 dark:bg-gray-600 font-semibold">
                                                    <td colspan="4" class="border px-2 py-1 text-right">Total Discount</td>
                                                    <td class="border px-2 py-1 text-right text-rose-500">₱' . number_format($totalDiscount, 2) . '</td>
                                                </tr>
                                                <tr class="bg-gray-200 dark:bg-gray-600 font-semibold">
                                                    <td colspan="4" class="border px-2 py-1 text-right">Grand Total</td>
                                                    <td class="border px-2 py-1 text-right text-blue-600">₱' . number_format($grandTotal, 2) . '</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                ');
                            })
                    ]),
                ]),
            ])
            ->action(function($data) {
                $this->checkForStockAvailability($data);
            })
            ->modalSubmitActionLabel('Proceed');
    }

    protected function checkForStockAvailability($data)
    {
        $insufficient = [];
    
        foreach ($this->cart as $cartItem) {
            $stock = Stock::where('supply_id', $cartItem['id'])
                ->where('branch_id', $this->currentBranch->branch_id)
                ->first();
    
            if (!$stock || $stock->quantity < $cartItem['qty']) {
                $insufficient[] = $cartItem['name'];
            }
        }
    
        if (!empty($insufficient)) {
            $count = count($insufficient);
            $itemsList = $count === 1
                ? $insufficient[0]
                : implode(', ', array_slice($insufficient, 0, -1)) . ' and ' . end($insufficient);
        
            $title = 'Payment Was Cancelled';
            $body = $count === 1
                ? "Insufficient stock for: $itemsList."
                : "Insufficient stock for the following items: $itemsList.";
        
            Notification::make()
                ->title($title)
                ->body($body)
                ->danger()
                ->send();
        
            return;
        }
    
        $this->createTransaction(); 
    }

    protected function createTransaction()
    {
        $totalDiscount = 0;
        $grandTotal = 0;
    
        foreach ($this->cart as $item) {
            $stock = Stock::where('supply_id', $item['id'])
                ->where('branch_id', $this->currentBranch->branch_id)
                ->first();
    
            if ($stock) {
                $stock->quantity -= $item['qty'];
                $stock->save();
            }

            $qty = (float) $item['qty'];
            $retail = (float) $item['retail_price'];
            $price = (float) $item['price'];
            $total = $qty * $price;
            $discount = ($retail - $price) * $qty;

            $grandTotal += $total;
            $totalDiscount += $discount;
        }
    
        $transaction = SaleTransaction::create([
            'branch_id'          => $this->currentBranch->branch_id,
            'processed_by'       => auth()->user()->employee->id,
            'transaction_date'   => now()->toDateString(),
            'discount_value'     => $totalDiscount,
            'total_amount'       => $grandTotal,
            'status'             => 'pending',
        ]);
    
        foreach ($this->cart as $cartItem) {
            $transaction->items()->create([
                'supply_id'     => $cartItem['id'],
                'original_price' => $cartItem['retail_price'],
                'price_amount'   => $cartItem['price'],
                'quantity'      => $cartItem['qty'],
            ]);
        }
    
        $this->cart = [];
        $this->editingPrice = null; // Reset editing state
        $this->newPrice = null; // Clear input
    
        Notification::make()
            ->title('Checkout Complete')
            ->body('A new sale transaction has been successfully recorded. Please proceed to payment.')
            ->success()
            ->send();

        $this->dispatch('refreshTable');
    }

    public function render()
    {
        return view('livewire.cart-section');
    }
}