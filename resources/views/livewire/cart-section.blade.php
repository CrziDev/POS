<div x-data="{
        cart: [
            { name: 'Item 1', price: 199.99, quantity: 1 },
            { name: 'Item 2', price: 349.50, quantity: 1 },
            { name: 'Item 3', price: 120.00, quantity: 1 }
        ]
    }" 
    class="p-5 h-full overflow-auto fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 space-y-4 flex flex-col">

    <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-200 border-b pb-2">🛒 Your Cart</h2>

    <!-- Empty Cart Message -->
    <template x-if="cart.length === 0">
        <p class="text-xs text-gray-400 dark:text-gray-500 text-center">Your cart is empty.</p>
    </template>

    <!-- Cart Items List -->
    <div class="space-y-2 flex-1 overflow-y-auto">
        <template x-for="(item, index) in cart" :key="index">
            <div class="flex items-center justify-between text-xs border-b border-gray-200 dark:border-gray-700 py-2 px-3 bg-gray-50 dark:bg-white/5">
                
                <!-- Item Name -->
                <div class="text-gray-800 dark:text-gray-200 truncate w-1/2" x-text="item.name"></div>

                <!-- Quantity Control -->
                <div class="flex items-center space-x-1 mx-4">
                    <button 
                        class="text-[9px] bg-gray-300 dark:bg-gray-700 hover:bg-gray-400 dark:hover:bg-gray-600 rounded px-2"
                        @click="item.quantity = item.quantity > 1 ? item.quantity - 1 : 1"
                    >
                        &lt;
                    </button>
                    <span class="text-xs text-gray-600 dark:text-gray-400 min-w-[1.5rem] text-center" x-text="item.quantity"></span>
                    <button 
                        class="text-[9px] bg-gray-300 dark:bg-gray-700 hover:bg-gray-400 dark:hover:bg-gray-600 rounded px-2"
                        @click="item.quantity++"
                    >
                        &gt;
                    </button>
                </div>

                <!-- Item Price -->
                <div class="text-gray-600 dark:text-gray-400 text-right w-[80px]">
                    ₱<span x-text="(item.price * item.quantity).toFixed(2)"></span>
                </div>
            </div>
        </template>
    </div>

    <!-- Footer (Sticky Checkout Section) -->
    <div class="mt-4 pt-2 border-t border-gray-200 dark:border-gray-700 text-xs text-right space-y-2">
        
        <!-- Net Total -->
        <div class="flex justify-between">
            <span class="text-gray-700 dark:text-gray-300">Net Total:</span>
            <span class="font-semibold text-blue-500">
                ₱<span x-text="cart.reduce((sum, item) => sum + (item.price * item.quantity), 0).toFixed(2)"></span>
            </span>
        </div>

        <!-- Discount -->
        <div class="flex justify-between">
            <span class="text-gray-700 dark:text-gray-300">Discount:</span>
            <span class="font-semibold text-blue-500">
                ₱<span x-text="(cart.reduce((sum, item) => sum + (item.price * item.quantity), 0) * 0.1).toFixed(2)"></span>
            </span>
        </div>

        <hr class="border-gray-200 dark:border-gray-700 my-2" />

        <!-- Grand Total -->
        <div class="flex justify-between text-sm font-semibold text-gray-800 dark:text-gray-200">
            <span>Grand Total:</span>
            <span class="text-blue-600">
                ₱<span x-text="(cart.reduce((sum, item) => sum + (item.price * item.quantity), 0) * 0.9).toFixed(2)"></span>
            </span>
        </div>

        <!-- Checkout Button -->
        <button 
            class="w-full py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold mt-2"
            :disabled="cart.length === 0"
        >
            Checkout
        </button>
    </div>
</div>
