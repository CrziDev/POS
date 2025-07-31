<?php

namespace App\Filament\Pages;

use App\Models\File;
use App\Models\Folder;
use Filament\Pages\Page;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class FileManager extends Page
{
    use WithFileUploads;

    protected static ?string $navigationIcon = 'heroicon-o-folder';
    protected static string $view = 'filament.pages.file-manager';

    public ?int $currentFolderId = null;
    public $fileUpload;
    public $newFolderName;
    public bool $showFolderModal = false;
    public bool $showFileModal = false;

    protected $listeners = ['refresh' => '$refresh'];

    public function getFoldersProperty()
    {
        return Folder::where('parent_id', $this->currentFolderId)->get();
    }

    public function getFilesProperty()
    {
        return File::where('folder_id', $this->currentFolderId)->get();
    }

    public function openFolder($folderId)
    {
        $this->currentFolderId = $folderId;
    }

    public function goBack()
    {
        $parent = Folder::find($this->currentFolderId)?->parent;
        $this->currentFolderId = $parent?->id;
    }

    public function createFolder()
    {
        $this->validate([
            'newFolderName' => 'required|string|max:255',
        ]);

        Folder::create([
            'folder' => $this->newFolderName,
            'parent_id' => $this->currentFolderId,
        ]);

        $this->reset('newFolderName', 'showFolderModal');
        $this->dispatch('refresh');
    }

    public function uploadFile()
    {
        $this->validate([
            'fileUpload' => 'required|file|max:10240', // 10MB max
        ]);

        $path = $this->fileUpload->store('uploads', 'public');

        File::create([
            'name' => $this->fileUpload->getClientOriginalName(),
            'path' => $path,
            'folder_id' => $this->currentFolderId,
            'mime_type' => $this->fileUpload->getMimeType(),
            'size' => $this->fileUpload->getSize(),
        ]);

        $this->reset('fileUpload', 'showFileModal');
        $this->dispatch('refresh');
    }

    public function deleteFolder($folderId)
    {
        $folder = Folder::findOrFail($folderId);
        
        // Delete all files in the folder
        foreach ($folder->files as $file) {
            Storage::disk('public')->delete($file->path);
            $file->delete();
        }

        // Recursively delete subfolders
        foreach ($folder->folders as $subFolder) {
            $this->deleteFolder($subFolder->id);
        }

        $folder->delete();
        $this->dispatch('refresh');
    }

    public function deleteFile($fileId)
    {
        $file = File::findOrFail($fileId);
        Storage::disk('public')->delete($file->path);
        $file->delete();
        $this->dispatch('refresh');
    }
}