
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
            wire:click="$dispatch('add-to-cart')"
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($supplies as $key => $supply)
                <div class="relative h-[100px] bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-white/10 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 border-b-4 border-blue-500 flex flex-col items-center justify-center text-center px-2">
                    <p class="text-xs font-semibold text-gray-800 dark:text-gray-200">{{$supply->name}}</p>
                    <p class="mb-3 text-[10px] text-gray-500 dark:text-gray-400 mt-1">₱999.00</p>
                    <span class="absolute bottom-2 left-3 text-[10px] text-gray-400 dark:text-gray-500">Stock: 12</span>
                    <span class="absolute bottom-2 right-3 text-[10px] text-gray-400 dark:text-gray-500">{{$supply->sku}}</span>
                </div>
            @endforeach
        </div>
    </div>

</div>
