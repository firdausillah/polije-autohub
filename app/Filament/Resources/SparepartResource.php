<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SparepartResource\Pages;
use App\Filament\Resources\SparepartResource\RelationManagers;
use App\Helpers\CodeGenerator;
use App\Models\Sparepart;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SparepartResource extends Resource
{
    protected static ?string $model = Sparepart::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    // protected static ?string $navigationGroup = 'Sparepart';

    protected static ?string $navigationGroup = 'Master';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                ->required(),
                TextInput::make('kode'),
                // ->default(fn () => CodeGenerator::generateSimpleCode('SP', 'spareparts', 'kode'))
                // ->readOnly(),
                Grid::make(3)
                ->schema([
                    Select::make('sparepart_m_category_id')
                    ->label('Kategori Sparepart')
                    ->relationship('sparepartMCategory', 'name'),
                    Select::make('is_original')
                    ->label('Original part')
                    ->required()
                    ->options([
                        1 => 'Iya',
                        0 => 'Tidak'
                    ]),
                    Select::make('is_pajak')
                    ->label('Pajak 12%')
                    ->required()
                    ->options([
                        1 => 'Iya',
                        0 => 'Tidak'
                    ]),

                ]),
                Grid::make(3)
                ->schema([
                    Select::make('is_liquid')
                        ->label('Part Liquid')
                        ->required()
                        ->options([
                            1 => 'Iya',
                            0 => 'Tidak'
                    ]),
                    TextInput::make('part_number'),
                    TextInput::make('margin')
                    ->numeric()
                    ->suffix('%')
                    ->required(),
                ]),
                Textarea::make('keterangan'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                ->searchable(),
                TextColumn::make('kode')
                ->searchable(),
                TextColumn::make('sparepartMCategory.name')
                ->label('Kategori')
                ->searchable(),
                IconColumn::make('is_original')
                ->label('Original')
                ->boolean()
                ->trueColor('success')
                ->falseColor('danger'),
                IconColumn::make('is_pajak')
                ->label('Pajak 12%')
                ->boolean()
                ->trueColor('success')
                ->falseColor('danger'),
                IconColumn::make('is_liquid')
                ->label('Liquid')
                ->boolean()
                ->trueColor('success')
                ->falseColor('warning'),
                TextColumn::make('margin')
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                SelectFilter::make('sparepart_m_category_id')
                ->searchable()
                ->preload()
                ->relationship('sparepartMCategory', 'name')
            ])
            ->actions([
                Tables\Actions\EditAction::make()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SatuansRelationManager::class,
            RelationManagers\ModalsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSpareparts::route('/'),
            'create' => Pages\CreateSparepart::route('/create'),
            'edit' => Pages\EditSparepart::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
