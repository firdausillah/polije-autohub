<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Filament\Resources\ServiceResource\RelationManagers;
use App\Helpers\CodeGenerator;
use App\Models\Service;
use Filament\Forms;
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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                ->required(),
                TextInput::make('kode')
                ->default(fn () => CodeGenerator::generateSimpleCode('SV', 'services', 'kode'))
                ->readOnly(),
                TextInput::make('harga')
                ->label('Biaya')
                ->prefix('Rp ')
                ->numeric()
                ->required(),
                TextInput::make('komisi_mekanik')
                ->required()
                ->suffix('%')
                ->numeric(),
                TextInput::make('estimasi_waktu_pengerjaan')
                ->suffix('menit')
                ->required()
                ->numeric(),
                Textarea::make('keterangan')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('kode'),
                TextColumn::make('harga')
                ->label('Biaya')
                ->money('IDR', locale: 'id_ID'),
                TextColumn::make('komisi_mekanik')
                ->suffix('%')
                ->alignCenter(),
                TextColumn::make('estimasi_waktu_pengerjaan')
                ->suffix(' menit')
                ->alignCenter(),
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
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
