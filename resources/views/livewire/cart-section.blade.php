<div
    class="p-5 h-full overflow-auto fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 space-y-4 flex flex-col">

    <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-200 border-b pb-2">🛒 Your Cart</h2>

    <!-- Empty Cart Message -->
    @if(count($cart) === 0)
        <p class="text-xs text-gray-400 dark:text-gray-500 text-center">Your cart is empty.</p>
    @endif

    @if(count($cart) > 0)
        <!-- Column Labels -->
        <div class="flex items-center justify-between text-[11px] font-semibold px-3 text-gray-500 dark:text-gray-400 uppercase">
            <span class="w-1/2 truncate">Item</span>
            <span class="w-[120px] text-center">Qty</span>
            <span class="w-[80px] text-right">Price</span>
        </div>
    @endif

    <!-- Cart Items List (Scrollable) -->
    <div class="space-y-2 flex-1 overflow-y-auto max-h-[300px] pr-1">
        @foreach($cart as $key => $item)
            <div class="flex items-center justify-between text-xs border-b border-gray-200 dark:border-gray-700 py-2 px-3 bg-gray-50 dark:bg-white/5 rounded">
                
                <!-- Item Name -->
                <div class="text-gray-800 dark:text-gray-200 truncate w-1/2">{{ $item['name'] }}</div>

                <!-- Quantity Control -->
                <div class="flex items-center space-x-1 mx-4 w-[120px] justify-center">
                    <button 
                        class="text-[9px] bg-gray-300 dark:bg-gray-700 hover:bg-gray-400 dark:hover:bg-gray-600 rounded px-2"
                        wire:click="decreaseItem({{ $item['id'] }})"
                    >
                        &lt;
                    </button>
                    <span class="text-xs text-gray-600 dark:text-gray-400 min-w-[1.5rem] text-center">{{ $item['qty'] }}</span>
                    <button 
                        class="text-[9px] bg-gray-300 dark:bg-gray-700 hover:bg-gray-400 dark:hover:bg-gray-600 rounded px-2"
                        wire:click="increaseItem({{ $item['id'] }})"
                    >
                        &gt;
                    </button>
                </div>

                <!-- Item Price -->
                <div class="text-gray-600 dark:text-gray-400 text-right w-[80px]">
                    ₱{{ number_format($item['price'] * $item['qty'], 2) }}
                </div>
            </div>
        @endforeach
    </div>

    <div class="pt-2 border-t border-gray-200 dark:border-gray-700 text-xs text-right space-y-2">
        
        <div class="flex justify-between">
            <span class="text-gray-700 dark:text-gray-300">Net Total:</span>
            <span class="font-semibold text-blue-500">₱</span>
        </div>

        <div class="flex justify-between">
            <span class="text-gray-700 dark:text-gray-300">Discount:</span>
            <span class="font-semibold text-blue-500">₱</span>
        </div>

        <hr class="border-gray-200 dark:border-gray-700 my-2" />

        <div class="flex justify-between text-sm font-semibold text-gray-800 dark:text-gray-200">
            <span>Grand Total:</span>
            <span class="text-blue-600">₱</span>
        </div>

        <button 
            wire:click="mountAction('checkOut')"
            class="w-full py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold"
        >
            Checkout
        </button>
    </div>

    <x-filament-actions::modals />
</div>
