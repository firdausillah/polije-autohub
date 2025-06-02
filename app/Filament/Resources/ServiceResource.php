<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Filament\Resources\ServiceResource\RelationManagers;
use App\Helpers\CodeGenerator;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Master';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                ->required(),
                TextInput::make('kode')
                ->default(fn () => CodeGenerator::generateSimpleCode('SV', 'services', 'kode'))
                ->readOnly(),

                Select::make('service_m_category_id')
                ->relationship('serviceMCategory', 'name')
                ->label('Kategori Service')
                ->required(),

                Select::make('service_m_type_id')
                ->relationship('serviceMType', 'name')
                ->label('Tipe Service')
                ->required(),
                TextInput::make('harga_1')
                ->currencyMask(',')
                ->label('Biaya Umum')
                ->prefix('Rp ')
                ->numeric()
                ->required(),
                TextInput::make('harga_2')
                ->currencyMask(',')
                ->label('Biaya Mahasiswa')
                ->prefix('Rp ')
                ->numeric()
                ->required(),
                // TextInput::make('estimasi_waktu_pengerjaan')
                // ->suffix('menit')
                // ->numeric(),
                Textarea::make('keterangan')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('serviceMCategory.name')
                ->label('Kategori')
                ->searchable(),
                TextColumn::make('serviceMType.name')
                ->label('Tipe')
                ->searchable(),
                TextColumn::make('name')
                ->searchable(),
                // TextColumn::make('kode')
                // ->searchable(),
                TextColumn::make('harga_1')
                ->label('Biaya Umum')
                ->money('IDR', locale: 'id_ID'),
                TextColumn::make('harga_2')
                ->label('Biaya Mahasiswa')
                ->money('IDR', locale: 'id_ID'),
                // TextColumn::make('estimasi_waktu_pengerjaan')
                // ->suffix(' menit')
                // ->alignCenter(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
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
