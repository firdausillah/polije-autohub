<?php

namespace App\Filament\Resources\SparepartResource\RelationManagers;

use App\Models\Satuan;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SatuansRelationManager extends RelationManager
{
    protected static string $relationship = 'sparepartSatuan';
    protected static ?string $pluralModelLabel = 'Satuan';
    protected static ?string $modelLabel = 'Satuan';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('satuan_id')
                ->required()
                ->relationship('satuan', 'name')
                ->live()
                ->afterStateUpdated(
                    function (Set $set, $state){
                        $satuan = Satuan::find($state);
                        $set('satuan_name', $satuan->name);
                        $set('satuan_kode', $satuan->kode);
                    }
                ),
                TextInput::make('harga')
                ->prefix('Rp ')
                ->required(),
                TextInput::make('jumlah_konversi')
                ->required()
                ->numeric()
                ->default('1'),
                Select::make('is_satuan_terkecil')
                ->required()
                ->options([
                    true => 'Iya',
                    false => 'Tidak'
                ]),
                TextInput::make('satuan_name')
                ->hidden(),
                TextInput::make('satuan_kode')
                ->hidden()
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('satuan.name'),
                TextColumn::make('harga')
                ->label('Harga')
                ->money('IDR', locale: 'id_ID'),
                Tables\Columns\TextColumn::make('jumlah_konversi'),
                Tables\Columns\IconColumn::make('is_satuan_terkecil')
                ->boolean()
                ->trueColor('success')
                ->falseColor('danger'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
