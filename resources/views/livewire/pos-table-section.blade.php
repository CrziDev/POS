
<div class="flex gap-2 w-full h-full"> 

    <!-- Category Selection as Pills -->
    <div  class="p-5 h-full w-[10%] overflow-auto fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 sm:w-60">
        <div class="space-y-2">
                @foreach($categoryList as $key => $category)
                    <button
                        type="button"
                        wire:click="updateCategory('{{$key}}')"
                        class="w-full text-left px-3 py-1.5 rounded-lg text-xs border transition 
                                text-gray-700 dark:text-gray-200 
                                border-gray-300 dark:border-gray-700 
                                hover:bg-blue-100 dark:hover:bg-white/10 
                                focus:outline-none 
                                {{$selectedCategory == $key ? 'bg-blue-500 text-white dark:text-white border-blue-500 ': '' }}" 
                            >
                            {{$category}}
                    </button>
                @endforeach
            </div>
    </div>

    <!-- Table Section -->
    <div class="hideScroll p-5 h-full w-[90%] overflow-auto fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <div 
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($supplies as $key => $supply)
                <div 
                    wire:click="selectItem({{ $supply->id }})"
                    class="relative h-[100px] bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-white/10 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 border-b-4 border-blue-500 flex flex-col items-center justify-center text-center px-2">
                    <p class="text-xs font-semibold text-gray-800 dark:text-gray-200">{{$supply->name}}</p>
                    <p class="mb-3 text-[10px] text-gray-500 dark:text-gray-400 mt-1">₱999.00</p>
                    <span class="absolute bottom-2 left-3 text-[10px] text-gray-400 dark:text-gray-500">Stock: 12</span>
                    <span class="absolute bottom-2 right-3 text-[10px] text-gray-400 dark:text-gray-500">{{$supply->sku}}</span>
                </div>
            @endforeach
        </div>
    </div>

     <!-- POS Modal for Item Selection and Price Entry -->
     <x-filament::modal id="select-item" width="3xl">
        <div 
            @keydown.space.prevent="$wire.addToCart()"
            class="space-y-6 p-4 sm:p-6 focus:outline-none"
        >

            <!-- Product Details -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="p-4 sm:p-6 rounded-xl bg-white dark:bg-gray-900 shadow ring-1 ring-gray-950/5 dark:ring-white/10">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-1">
                        {{ $selectedSupply?->name }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                        Stock: <span class="font-medium">{{ $selectedSupply?->stock }}</span>
                    </p>
                    <p class="text-xl font-bold text-blue-600 dark:text-blue-400">
                        ₱{{ number_format($selectedSupply?->price, 2) }}
                    </p>
                </div>

                <!-- Price Input -->
                <div class="p-4 sm:p-6 rounded-xl bg-white dark:bg-gray-900 shadow ring-1 ring-gray-950/5 dark:ring-white/10">
                    <label for="price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Set Custom Price
                    </label>
                    <input
                        wire:model="setPrice"
                        type="number"
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        placeholder="Enter custom price"
                    />
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end pt-2 space-x-3">
                <x-filament::button
                    type="button"
                    color="gray"
                    tag="button"
                    size="sm"
                    wire:click="$dispatch('close-modal',{id:'select-item'})"
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
