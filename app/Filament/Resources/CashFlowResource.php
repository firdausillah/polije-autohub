<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CashFlowResource\Pages;
use App\Filament\Resources\CashFlowResource\RelationManagers;
use App\Models\CashFlow;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CashFlowResource extends Resource
{
    protected static ?string $model = CashFlow::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('account_debit_id')
                ->relationship('accounts', 'name')
                ->getOptionLabelFromRecordUsing(fn($record) => "{$record->kode} - {$record->name}")
                ->searchable()
                ->preload()
                ->label('Akun Debit'),
                Select::make('account_kredit_id')
                ->relationship('accounts', 'name')
                ->getOptionLabelFromRecordUsing(fn($record) => "{$record->kode} - {$record->name}")
                ->searchable()
                ->preload()
                ->label('Akun Kredit'),
                TextInput::make('kode'),
                TextInput::make('total')
                ->prefix('Rp ')
                ->numeric()
                ->required(),
                Textarea::make('keterangan'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCashFlows::route('/'),
            'create' => Pages\CreateCashFlow::route('/create'),
            'edit' => Pages\EditCashFlow::route('/{record}/edit'),
        ];
    }
}
