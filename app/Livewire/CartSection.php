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
use Livewire\Features\SupportEvents\BaseOn;

use function Pest\Laravel\options;

class CartSection extends Component implements HasActions,HasForms
{

    use InteractsWithActions;
    use InteractsWithForms;

    public $cart = [];
    public $totalAmount = 0;


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
            ->label('Purchase Information')
            ->modalWidth(MaxWidth::SevenExtraLarge)
            ->form([
                Split::make([
                    Section::make('Items')->schema([
                        Placeholder::make('items_list')
                            ->label('')
                            ->content(function () {
                                if (empty($this->cart)) {
                                    return 'No items in cart.';
                                }
                            
                                $this->totalAmount = 0;
                                $rows = collect($this->cart)->map(function ($item) {
                                    $this->totalAmount += (float)$item['qty'] * (float)$item['price'];
                                    return "<tr>
                                        <td class='border px-2 py-1'>{$item['name']}</td>
                                        <td class='border px-2 py-1 text-right'>₱" . number_format($item['price'], 2) . "</td>
                                        <td class='border px-2 py-1 text-center'>{$item['qty']}</td>
                                        <td class='border px-2 py-1 text-right'>₱" . number_format((float)$item['qty'] * (float)$item['price'], 2) . "</td>
                                    </tr>";
                                })->implode('');
                                

                                return new HtmlString('
                                    <div class="overflow-auto max-h-96">
                                        <table class="w-full text-sm border-collapse bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                                            <thead>
                                                <tr class="bg-gray-100 dark:bg-gray-700">
                                                    <th class="border px-2 py-1 text-left">Name</th>
                                                    <th class="border px-2 py-1 text-right">Retail Price</th>
                                                    <th class="border px-2 py-1 text-center">Qty</th>
                                                    <th class="border px-2 py-1 text-right">Total Price</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                '.$rows.'
                                            </tbody>
                                            <tfoot>
                                                <tr class="bg-gray-200 dark:bg-gray-600 font-semibold">
                                                    <td colspan="3" class="border px-2 py-1 text-right">Grand Total</td>
                                                    <td class="border px-2 py-1 text-right">₱' . number_format($this->totalAmount, 2) . '</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                ');
                            })
                            
                    ]),

                    Section::make('Payment')->schema([
                        Select::make('customer')
                            ->placeholder('Select Customer')
                            ->createOptionForm([
                                Section::make('New Customer')->schema([
                                    TextInput::make('name')
                                        ->label('Customer Name')
                                        ->required(),
                                    TextInput::make('contact_number')
                                        ->label('Contact Number'),
                                    TextInput::make('address')
                                        ->label('Address'),
                                ]),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                $customer = Customer::create($data);
                                return $customer->getKey();
                            })
                            ->required()
                            ->searchable()
                            ->allowHtml()
                            ->options(Customer::getOptionsArray()),

                        Select::make('payment_method')
                            ->options([
                                'g-cash' => 'G-Cash',
                                'cash'  => 'Cash',
                            ])
                            ->default('g-cash')
                            ->live(),

                        Split::make([
                            TextInput::make('reference_number')
                                ->visible(fn ($get) => $get('payment_method') === 'g-cash')
                                ->label('Reference No.')
                                ->required(),
                            TextInput::make('amount')
                                ->label('Amount')
                                ->minValue($this->totalAmount)
                                ->mask(RawJs::make('$money($input)'))
                                ->stripCharacters(',')
                                ->numeric()
                                ->minValue(1)
                                ->inputMode('decimal')
                                ->required(),
                        ]),
                    ]),
                ]),
            ])
            ->action(function($data){
                $this->checkForStockAvailability($data);
            });
    }


    protected function checkForStockAvailability($data)
    {
        $branch = auth()->user()->branch;
    
        $insufficient = [];
    
        foreach ($this->cart as $cartItem) {
            $stock = Stock::where('supply_id', $cartItem['id'])
                ->where('branch_id', $branch->id)
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
    
        $this->submitPayment($branch,$data); 
    }
    

    protected function submitPayment($branch, $data)
    {
        $totalAmount = 0;
    
        foreach ($this->cart as $cartItem) {
            $stock = Stock::where('supply_id', $cartItem['id'])
                ->where('branch_id', $branch->id)
                ->first();
    
            if ($stock) {
                $stock->quantity -= $cartItem['qty'];
                $stock->save();
            }
    
            $totalAmount += $cartItem['qty'] * $cartItem['price'];
        }
    
        $transaction = SaleTransaction::create([
            'customer_id'        => $data['customer'],
            'processed_by'       => auth()->user()->id,
            'payment_method'     => $data['payment_method'],
            'payment_reference'  => $data['reference_number'],
            'date_paid'          => now()->toDateString(),
            'discount_value'     => 0,
            'total_amount'       => $totalAmount,
            'status'             => 'Paid',
        ]);
    
        foreach ($this->cart as $cartItem) {
            $transaction->items()->create([
                'supply_id'     => $cartItem['id'],
                'origanl_price' => $cartItem['retail_price'],
                'price_amout'   => $cartItem['price'],
                'quantity'      => $cartItem['qty'],
            ]);
        }
    
        $this->cart = [];
    
        Notification::make()
            ->title('Transaction successful!')
            ->success()
            ->send();
    }
    

    public function render()
    {
        return view('livewire.cart-section');
    }
}
