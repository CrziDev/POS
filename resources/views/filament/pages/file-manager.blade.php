<x-filament::page>
    <div class="text-gray-800 divide-y divide-gray-200 rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:divide-white/10 dark:bg-gray-900 dark:ring-white/10">
        
      <!-- Header with Actions -->
        <div class="px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    @if($currentFolderId)
                        <button wire:click="goBack" class="text-gray-600 hover:text-gray-800">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                    @endif
                    <h2 class="text-lg font-semibold">Client Document Manager</h2>
                </div>
                <div class="flex gap-2">
                    <button @click="$dispatch('open-modal', { id: 'create-folder' })" class="bg-gray-800 text-white px-3 py-1.5 text-sm rounded hover:bg-gray-700">+ Add Folder</button>
                    <button wire:click="$toggle('showFileModal')" class="bg-gray-700 text-white px-3 py-1.5 text-sm rounded hover:bg-gray-600">+ Upload File</button>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="relative divide-y divide-gray-200 overflow-x-auto dark:divide-white/10 px-4 py-4">
            <!-- Folders Section -->
            <div class="mb-6">
                <div class="flex flex-wrap gap-4 pb-2" x-data="{ openDropdown: null }">
                    @foreach($this->folders as $folder)
                        <div class="relative bg-white rounded-lg shadow-sm p-4 w-48 min-w-[12rem] hover:shadow transition">
                    <div class="absolute top-2 right-2 z-10">
                        <button @click="openDropdown = openDropdown === {{ $folder->id }} ? null : {{ $folder->id }}" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12h.01M12 12h.01M18 12h.01" />
                            </svg>
                        </button>
                        <div x-show="openDropdown === {{ $folder->id }}" @click.away="openDropdown = null" class="absolute right-0 mt-2 w-32 bg-white border border-gray-200 rounded shadow text-sm z-10">
                            <button wire:click="openFolder({{ $folder->id }})" class="w-full px-3 py-2 text-left hover:bg-gray-50">Open</button>
                            <button wire:click="deleteFolder({{ $folder->id }})" class="w-full px-3 py-2 text-left text-red-500 hover:bg-gray-50">Delete</button>
                        </div>
                    </div>
                    <div wire:click="openFolder({{ $folder->id }})" class="flex items-center gap-2 mb-2 cursor-pointer">
                        <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7.5A1.5 1.5 0 014.5 6h5.086a1.5 1.5 0 011.06.44l1.414 1.414a1.5 1.5 0 001.06.44H19.5A1.5 1.5 0 0121 9.5v8a1.5 1.5 0 01-1.5 1.5H4.5A1.5 1.5 0 013 17.5v-10z" />
                        </svg>
                        <div class="font-medium truncate text-sm text-gray-800">{{ $folder->folder }}</div>
                    </div>
                    <div class="text-xs text-gray-500">{{ $folder->folders()->count() }} folders, {{ $folder->files()->count() }} assets</div>
                </div>
                    @endforeach
                </div>
            </div>

            <!-- Files Section -->
            <div class="mb-6 py-5">
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    @foreach($this->files as $file)
                        <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-100 relative">
                            <div class="absolute top-2 right-2 z-10">
                                <button x-data="{ open: false }" @click="open = !open" class="text-gray-400 hover:text-gray-600">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12h.01M12 12h.01M18 12h.01" />
                                    </svg>
                                </button>
                                <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-32 bg-white border border-gray-200 rounded shadow text-sm z-10">
                                    <a href="{{ Storage::url($file->path) }}" target="_blank" class="block w-full px-3 py-2 text-left hover:bg-gray-50">Preview</a>
                                    <a href="{{ Storage::url($file->path) }}" download class="block w-full px-3 py-2 text-left hover:bg-gray-50">Download</a>
                                    <button wire:click="deleteFile({{ $file->id }})" class="w-full px-3 py-2 text-left text-red-500 hover:bg-gray-50">Delete</button>
                                </div>
                            </div>
                            <img src="{{ Storage::url($file->path) }}" alt="{{ $file->name }}" class="w-full h-32 object-cover">
                            <div class="p-3">
                                <div class="text-sm font-medium text-gray-800 truncate">{{ $file->name }}</div>
                                <div class="text-xs text-gray-500">Category: {{ $file->category ?? 'None' }}</div>
                                <div class="text-xs text-gray-400">Uploaded: {{ $file->created_at->format('M d, Y') }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Folder Modal -->
    <x-filament::modal id="create-folder" wire:model="showFolderModal">
        <x-slot name="header">
            <h2 class="text-lg font-semibold">Create New Folder</h2>
        </x-slot>
        <form wire:submit.prevent="createFolder">
            <div class="space-y-4">
                <x-filament::input.wrapper>
                    <x-filament::input type="text" wire:model="newFolderName" placeholder="Folder Name" />
                </x-filament::input.wrapper>
            </div>
            <div class="mt-6 flex justify-end gap-2">
                <x-filament::button type="button" wire:click="$toggle('showFolderModal')" color="gray">Cancel</x-filament::button>
                <x-filament::button type="submit" color="primary">Create</x-filament::button>
            </div>
        </form>
    </x-filament::modal>

    <!-- File Upload Modal -->
    <x-filament::modal id="upload-file" wire:model="showFileModal">
        <x-slot name="header">
            <h2 class="text-lg font-semibold">Upload File</h2>
        </x-slot>
        <form wire:submit.prevent="uploadFile">
            <div class="space-y-4">
                <x-filament::input.wrapper>
                    <x-filament::input type="file" wire:model="fileUpload" />
                </x-filament::input.wrapper>
            </div>
            <div class="mt-6 flex justify-end gap-2">
                <x-filament::button type="button" wire:click="$toggle('showFileModal')" color="gray">Cancel</x-filament::button>
                <x-filament::button type="submit" color="primary">Upload</x-filament::button>
            </div>
        </form>
    </x-filament::modal>
</x-filament::page>