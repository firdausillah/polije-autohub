<?php

namespace App\Filament\Resources\ServiceScheduleResource\RelationManagers;

use App\Models\service;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServiceDServicesRelationManager extends RelationManager
{
    protected static string $relationship = 'ServiceDServices';

    protected static ?string $title = 'Services';
    protected static ?string $pluralLabel = 'Services';
    protected static ?string $modelLabel = 'Services';

    public static function updateSubtotal($get, $set): void
    {
        $service = service::find($get('service_id'));
        
        // $harga_subtotal = floatval($service->harga) * floatval(($get('jumlah')));
        $harga_subtotal = floatval($service->harga ?? 0) * floatval($get('jumlah') ?? 0);
        $estimasi_waktu_pengerjaan = ($service->estimasi_waktu_pengerjaan ?? 0) * floatval(($get('jumlah') ?? 0));

        $set('service_name', $service->name??'');
        $set('service_kode', $service->kode??'');
        $set('harga_unit', $service->harga??0);
        $set('estimasi_waktu_pengerjaan', $estimasi_waktu_pengerjaan);
        $set('harga_subtotal', $harga_subtotal);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('service_id')
                ->relationship('services', 'name')
                ->required()
                ->live()
                ->afterStateUpdated(
                    function(Set $set, Get $get){
                        Self::updateSubtotal($get, $set);
                    }
                )
                ->searchable(),
                Hidden::make('service_name'),
                Hidden::make('service_kode'),
                Textinput::make('jumlah')
                ->required()
                ->default(1)
                ->live()
                ->afterStateUpdated(
                    function (Set $set, Get $get) {
                        Self::updateSubtotal($get, $set);
                    }
                )
                ->numeric(),
                Grid::make()
                ->columns(3)
                ->schema([
                    TextInput::make('harga_unit')
                    ->label('Harga')
                    ->prefix('Rp')
                    ->readOnly(),
                    TextInput::make('harga_subtotal')
                    ->prefix('Rp')
                    ->readOnly(),
                    Textinput::make('estimasi_waktu_pengerjaan')
                    ->suffix('Menit')
                    ->readOnly(),
                ])
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('service_name')
            ->columns([
                Tables\Columns\TextColumn::make('service_name'),
                Tables\Columns\TextColumn::make('jumlah'),
                Tables\Columns\TextColumn::make('harga_unit')
                ->money('IDR', locale: 'id_ID'),
                Tables\Columns\TextColumn::make('harga_subtotal')
                ->money('IDR', locale: 'id_ID'),
                Tables\Columns\TextColumn::make('estimasi_waktu_pengerjaan'),


                Tables\Columns\TextColumn::make('harga_subtotal')
                ->summarize(
                    Sum::make()
                        ->money('IDR', locale: 'id_ID')
                        ->label('Total')
                )
                ->money('IDR', locale: 'id_ID'),
                Tables\Columns\TextColumn::make('estimasi_waktu_pengerjaan')
                ->summarize(
                    Sum::make()
                        ->label('Total')
                        ->suffix(' Menit'),
                )
            ->suffix(' Menit'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
