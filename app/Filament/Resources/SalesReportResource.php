<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesReportResource\Pages;
use App\Filament\Resources\SalesReportResource\RelationManagers;
use App\Models\SalesReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use function Laravel\Prompts\text;

class SalesReportResource extends Resource
{
    protected static ?string $model = SalesReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Laporan';

    public static function getModelLabel(): string
    {
        return 'Laporan Penjualan';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Laporan Penjualan';
    }

    public static function canAccess(): bool
    {
        return false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('name')
                ->searchable()
                ->sortable()
                ->toggleable(),
                TextColumn::make('harga_modal')->money('IDR', locale: 'id_ID')->sortable()->toggleable(),
                TextColumn::make('harga_jual')->money('IDR', locale: 'id_ID')->sortable()->toggleable(),
                TextColumn::make('hpp')->money('IDR', locale: 'id_ID')->sortable()->toggleable(),
                TextColumn::make('total_penjualan')->money('IDR', locale: 'id_ID')->sortable()->toggleable(),
                TextColumn::make('laba_kotor')->money('IDR', locale: 'id_ID')->sortable()->toggleable(),
                TextColumn::make('qty_terjual')->sortable()->toggleable(),
                TextColumn::make('saldo')->sortable()->toggleable()
                ->label('Saldo Barang'),
                //
            ])
            ->filters([
                //
            // ])
            // ->actions([
            //     Tables\Actions\EditAction::make(),
            // ])
            // ->bulkActions([
            //     Tables\Actions\BulkActionGroup::make([
            //         Tables\Actions\DeleteBulkAction::make(),
            //     ]),
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
            'index' => Pages\ListSalesReports::route('/'),
            // 'create' => Pages\CreateSalesReport::route('/create'),
            // 'edit' => Pages\EditSalesReport::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
