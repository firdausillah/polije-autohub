<?php

namespace App\Filament\Resources\SparepartResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HppsRelationManager extends RelationManager
{
    protected static string $relationship = 'hpps';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('transaksi_h_kode')
                    ->required()
                    ->maxLength(255),
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
                Tables\Columns\TextColumn::make('hpp')
                ->label('HPP')
                ->money('IDR', locale: 'id_ID'),
                // Tables\Columns\TextColumn::make('transaksi_h_kode'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
