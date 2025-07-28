
<div class="flex gap-2 w-full h-full"> 

<div 
    x-data="{
        barcode: '',
        shouldFocus: true,

        init() {
            const input = document.getElementById('barcode-input');

            setInterval(() => {
                const active = document.activeElement;
                const isInputFocused = ['INPUT', 'TEXTAREA'].includes(active.tagName) && active.id !== 'barcode-input';
                
                this.shouldFocus = !isInputFocused;

                if (this.shouldFocus) {
                    input.focus();
                }
            }, 200);
        },

        handleEnter(e) {
            if (this.barcode.length > 2) {
                @this.call('handleBarcode', this.barcode);
                this.barcode = '';
                e.target.value = '';
            }
        }
    }"
    x-init="init()"
>
    <input 
        id="barcode-input"
        type="text" 
        class="absolute opacity-0 pointer-events-none"
        @input="barcode = $event.target.value" 
        @keydown.space.prevent="handleEnter($event)" 
        autocomplete="off"
    >
</div>


    <!-- Table Section -->
    <div class="hideScroll pb-5 h-full w-[100%] overflow-auto fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        
        <div class="sticky px-5 pt-5 top-0 z-10 bg-white dark:bg-gray-900 pb-3   mb-4">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-300">
                    Branch: {{ $this->currentBranch->branch->name ?? 'N/A' }}
                </span>

                <div class="w-full max-w-sm ml-auto">
                    <input 
                       wire:model.live="search"
                        type="text" 
                        wire:model.debounce.300ms="search" 
                        placeholder="Search SKU..." 
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm text-gray-900 dark:text-gray-100 shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    />
                </div>
            </div>
        </div>
    
        <div 
            class="px-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($stocks as $key => $stock)
                @php
                    $quantity = $stock->quantity;
                    $reorderLevel = $stock->reorder_level;
                    $borderClass = 'border-blue-500';
                    $textClass = 'text-gray-400 dark:text-gray-500';

                    if ($quantity == 0) {
                        $borderClass = 'border-red-500';
                        $textClass = 'text-red-500';
                    } elseif ($quantity <= $reorderLevel) {
                        $borderClass = 'border-orange-400';
                        $textClass = 'text-orange-400';

                    }
                @endphp

                <div 
                    wire:click="addToCart({{ $stock->id }})"
                    class="relative h-[100px] bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-white/10 
                        rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 border-b-4 {{ $borderClass }} 
                        flex flex-col items-center justify-center text-center px-2">
                    
                    <p class="text-xs font-semibold text-gray-800 dark:text-gray-200">{{$stock->supply->name}}</p>
                    <p class="mb-3 text-[10px] text-gray-500 dark:text-gray-400 mt-1">{{$stock->supply->retail_price}}</p>

                    <span class="absolute bottom-2 left-3 text-[10px] {{ $textClass }}">
                        Stock: {{$quantity}}
                    </span>

                    <span class="absolute bottom-2 right-3 text-[10px] text-gray-400 dark:text-gray-500">{{$stock->supply->sku}}</span>
                </div>
            @endforeach

        </div>
    </div>

     <!-- POS Modal for Item Selection and Price Entry -->
     <x-filament::modal id="select-item" width="4xl">
        <div 
            @keydown.space.prevent="$wire.addToCart()"
            class="space-y-6  focus:outline-none"
        >
            <!-- Item Header Info -->
            <div class="bg-white dark:bg-gray-900 p-4 rounded-xl shadow ring-1 ring-gray-950/5 dark:ring-white/10 space-y-1">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    <span class="font-medium text-gray-800 dark:text-gray-100">Supply:</span>
                    {{ $selectedSupply?->supply->name }}
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    <span class="font-medium text-gray-800 dark:text-gray-100">Branch:</span>
                    {{ $selectedSupply?->branch->name }}
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    <span class="font-medium text-gray-800 dark:text-gray-100">Stock:</span>
                    {{ $selectedSupply?->quantity }}
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    <span class="font-medium text-gray-800 dark:text-gray-100">Retail Price:</span>
                    ₱{{ number_format($selectedSupply?->supply->price, 2) }}
                </p>
            </div>

            <!-- Input Fields -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Custom Price -->
                <div>
                    <label for="price" class="block text-sm text-gray-500 dark:text-gray-400 mb-1">
                        Set Price (₱)
                    </label>
                    <input
                        wire:model="setPrice"
                        type="number"
                        step="0.01"
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        placeholder="e.g. 99.00"
                    />

                    @error('setPrice')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="quantity" class="block text-sm text-gray-500 dark:text-gray-400 mb-1">
                        Quantity
                    </label>
                    <input
                        wire:model="setQuantity"
                        type="number"
                        min="1"
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        placeholder="e.g. 1"
                    />           
                    @error('setQuantity')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror

                </div>
            </div>

            <div class="flex justify-end pt-2 space-x-3">
                <x-filament::button
                    type="button"
                    color="gray"
                    tag="button"
                    size="sm"
                    wire:click="$dispatch('close-modal', { id: 'select-item' })"
                >
                    Cancel
                </x-filament::button>

                <x-filament::button
                    type="button"
                    color="primary"
                    wire:click="addToCart()"
                    tag="button"
                    size="sm"
                >
                    Add to Cart
                </x-filament::button>
            </div>
        </div>
    </x-filament::modal>
</div>

