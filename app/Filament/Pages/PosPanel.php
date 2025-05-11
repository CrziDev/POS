<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use App\Models\Shop\Product;
use App\Models\Supply;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;


class PosPanel extends Page implements HasForms, HasTable
{

    use InteractsWithTable;
    use InteractsWithForms;
    
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.pos-panel';


    public function getTitle(): string | Htmlable
    {
        return '';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Supply::query())
            ->paginated(false)
            ->columns([
                Split::make([
                    TextColumn::make('name')
                        ->label('Item')
                        ->searchable()
                        ->formatStateUsing(strFormat())
                        ->default('-')
                        ->extraAttributes(['style'=>'text-[10px]']),
                ])
            ])
            ->contentGrid([
                'md' => 5,
                'xl' => 4,
            ])
            ->filters([
            ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
            ]);
    }

    public static function canAccess(): bool
    {
        return !auth()->user()->hasRole(['admin']);
    }

}
