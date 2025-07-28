<div
    class="p-5 h-full overflow-auto fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 space-y-4 flex flex-col">

    <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-200 border-b pb-2">🛒 Your Cart</h2>

    <!-- Empty Cart Message -->
    @if(count($cart) === 0)
        <p class="text-xs text-gray-400 dark:text-gray-500 text-center">Your cart is empty.</p>
    @endif

    @if(count($cart) > 0)
        <!-- Column Labels -->
        <div class="flex items-center justify-between text-[11px] font-semibold px-3 text-gray-500 dark:text-gray-400 uppercase pr-4">
            <span class="w-[40%] truncate">Item</span>
            <span class="w-[120px] text-center">Qty</span>
            <span class="w-[90px] text-center">Price</span>
            <span class="w-[90px] text-right">Discount</span>
        </div>
    @endif

    <!-- Cart Items List (Scrollable) -->
    <div class="space-y-2 flex-1 overflow-y-auto max-h-[400px] pr-4 ">
        @php
            $netTotal = 0;
            $totalDiscount = 0;
        @endphp

        @foreach($cart as $key => $item)
            @php
                $itemTotal = $item['price'] * $item['qty'];
                $itemDiscount = ($item['retail_price'] - $item['price']) * $item['qty'];
                $netTotal += $itemTotal;
                $totalDiscount += $itemDiscount;
            @endphp

            <div class="relative flex items-center justify-between text-xs border-b border-gray-200 dark:border-gray-700 py-2 px-3 bg-gray-50 dark:bg-white/5 rounded">
                <button 
                    wire:click="removeItem({{ $item['id'] }})"
                    class="absolute top-2 right-[-15px] text-red-500 hover:text-red-700 text-xs"
                    title="Remove item"
                >
                    ✕
                </button>
                <div class="text-gray-800 dark:text-gray-200 truncate w-[40%]">{{ $item['name'] }}</div>

                <div class="flex items-center space-x-1 mx-4 w-[120px] justify-center">
                    <button 
                        class="text-[8px] bg-gray-300 dark:bg-gray-700 hover:bg-gray-400 dark:hover:bg-gray-600 rounded px-2"
                        wire:click="decreaseItem({{ $item['id'] }})"
                    >
                        &lt;
                    </button>
                    <span class="text-xs text-gray-600 dark:text-gray-400 min-w-[1.2rem] text-center">{{ $item['qty'] }}</span>
                    <button 
                        class="text-[8px] bg-gray-300 dark:bg-gray-700 hover:bg-gray-400 dark:hover:bg-gray-600 rounded px-2"
                        wire:click="increaseItem({{ $item['id'] }})"
                    >
                        &gt;
                    </button>
                </div>

                <div class="text-gray-600 dark:text-gray-400 text-center w-[100px] leading-tight flex items-center justify-center space-x-1">
                    <div class="{{ $item['retail_price'] !== $item['price'] ? 'line-through text-red-400 text-[11px]' : '' }}">
                        ₱{{ number_format($item['retail_price'], 2) }}
                    </div>
                    @if($item['retail_price'] !== $item['price'])
                        <div class="text-[12px] font-semibold text-gray-800 dark:text-gray-200">
                            ₱{{ number_format($item['price'], 2) }}
                        </div>
                    @endif
                    <button 
                        wire:click="editPrice({{ $item['id'] }})"
                        class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                        title="Edit price"
                    >
                        ✎
                    </button>
                    @if($editingPrice === $item['id'])
                        <input 
                            type="number" 
                            wire:model="newPrice" 
                            wire:keydown.enter="updatePrice({{ $item['id'] }})"
                            class="w-16 text-xs border rounded px-1 py-0.5 text-gray-800 dark:text-gray-200 dark:bg-gray-800"
                            placeholder="{{ $item['price'] }}"
                        />
                    @endif
                </div>

                <div class="text-gray-600 dark:text-gray-400 text-right w-[90px]">
                    ₱{{ number_format($itemDiscount, 2) }}
                </div>
            </div>
        @endforeach
    </div>

    <div class="pt-2 border-t border-gray-200 dark:border-gray-700 text-xs text-right space-y-3">
        
        <div class="flex justify-between">
            <span class="text-gray-700 dark:text-gray-300">Net Total:</span>
            <span class="font-semibold text-blue-500">₱{{ number_format($netTotal, 2) }}</span>
        </div>

        <div class="flex justify-between">
            <span class="text-gray-700 dark:text-gray-300">Discount:</span>
            <span class="font-semibold text-blue-500">₱{{ number_format($totalDiscount, 2) }}</span>
        </div>

        <hr class="border-gray-200 dark:border-gray-700 my-2" />

        <div class="flex mb-2 justify-between text-sm font-semibold text-gray-800 dark:text-gray-200">
            <span>Grand Total:</span>
            <span class="text-blue-600">₱{{ number_format($netTotal, 2) }}</span>
        </div>

        <div class="pt-5">
            <button 
                wire:click="mountAction('checkOut')"
                class="w-full py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold"
            >
                Checkout
            </button>
        </div>
    </div>

    <x-filament-actions::modals />
</div>