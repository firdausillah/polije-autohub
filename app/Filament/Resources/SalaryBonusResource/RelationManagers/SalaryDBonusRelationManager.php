<?php

namespace App\Filament\Resources\SalaryBonusResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SalaryDBonusRelationManager extends RelationManager
{
    protected static string $relationship = 'SalaryDBonus';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Forms\Components\TextInput::make('name')
                //     ->required()
                //     ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                ->label('Nama User'),
                Tables\Columns\TextColumn::make('payroll.name')
                ->label('Role'),
                Tables\Columns\TextColumn::make('pendapatan')
                ->summarize(
                Sum::make()
                ->money('IDR', locale: 'id_ID')
                ->label('Total')
                )
                ->money('IDR', locale: 'id_ID')
                ->sortable()
                ->toggleable(),
                Tables\Columns\TextColumn::make('salary')
                ->label('Gaji')
                ->summarize(
                Sum::make()
                ->money('IDR', locale: 'id_ID')
                ->label('Total')
                )
                ->money('IDR', locale: 'id_ID')
                ->sortable()
                ->toggleable(),
                Tables\Columns\TextColumn::make('bonus')
                ->summarize(
                Sum::make()
                ->money('IDR', locale: 'id_ID')
                ->label('Total')
                )
                ->money('IDR', locale: 'id_ID')
                ->sortable()
                ->toggleable(),
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
