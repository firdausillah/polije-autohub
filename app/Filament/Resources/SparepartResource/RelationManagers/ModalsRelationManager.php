<?php

namespace App\Filament\Resources\SparepartResource\RelationManagers;

use App\Helpers\CodeGenerator;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ModalsRelationManager extends RelationManager
{
    protected static string $relationship = 'modals';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('harga_modal')
                    ->currencyMask(',')
                    ->prefix('Rp')
                    ->required()
                    ->maxLength(255),
                TextInput::make('transaksi_h_kode')
                    ->default(fn () => CodeGenerator::generateTransactionCode('MA', 'modals', 'transaksi_h_kode'))
                    ->readOnly(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('transaksi_h_kode')
            ->columns([
                Tables\Columns\TextColumn::make('transaksi_h_kode')
                ->label('Kode Transaksi')
                ->searchable()
                ->sortable(),
                Tables\Columns\TextColumn::make('harga_modal')
                // ->label('HPP')
                ->money('IDR', locale: 'id_ID'),
                // Tables\Columns\TextColumn::make('transaksi_h_kode'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
