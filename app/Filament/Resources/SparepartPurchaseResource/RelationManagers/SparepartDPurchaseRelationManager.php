<?php

namespace App\Filament\Resources\SparepartPurchaseResource\RelationManagers;

use App\Models\Satuan;
use App\Models\Sparepart;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SparepartDPurchaseRelationManager extends RelationManager
{
    protected static string $relationship = 'SparepartDPurchase';

    protected static ?string $title = 'Detail Sparepart';
    protected static ?string $pluralLabel = 'Detail Sparepart';
    protected static ?string $modelLabel = 'Detail Sparepart';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('sparepart_id')
                ->relationship('sparepart', 'name')
                ->preload()
                ->searchable()
                ->live() // Trigger update saat berubah
                ->afterStateUpdated(
                    function (Set $set, $state){
                        $set('satuan_id', null); // Reset satuan_id saat sparepart berubah
                    }
                ),

                Select::make('satuan_id')
                ->label('Satuan')
                ->options(
                    fn (Get $get) =>
                    Sparepart::find($get('sparepart_id'))?->sparepartSatuan()->pluck('satuan_name', 'satuan_id') ?? [],
                )
                ->live()
                ->searchable()
                ->disabled(fn (Get $get) => !$get('sparepart_id')),
                TextInput::make('jumlah_unit')
                ->required()
                ->gt(0),
                TextInput::make('harga_unit')
                ->currencyMask(',')
                ->label('Harga satuan')
                ->gt(0)
                ->prefix('Rp ')
                ->numeric()
                ->required()  
            ]);
    }


    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sparepart_name')
                ->searchable(),
                Tables\Columns\TextColumn::make('satuan_name'),
                Tables\Columns\TextColumn::make('jumlah_unit'),
                Tables\Columns\TextColumn::make('harga_unit')
                ->money('IDR', locale: 'id_ID'),
                Tables\Columns\TextColumn::make('harga_subtotal')
                ->summarize(
                    Sum::make()
                    ->money('IDR', locale: 'id_ID')
                    ->label('Total')
                    )
                ->money('IDR', locale: 'id_ID')
                
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->hidden(fn () => $this->getOwnerRecord()->is_approve === 'approved'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->hidden(fn () => $this->getOwnerRecord()->is_approve === 'approved'),
                Tables\Actions\DeleteAction::make()->hidden(fn () => $this->getOwnerRecord()->is_approve === 'approved'),
                
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }
}
