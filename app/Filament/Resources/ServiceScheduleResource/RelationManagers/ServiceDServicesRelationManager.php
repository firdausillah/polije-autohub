<?php

namespace App\Filament\Resources\ServiceScheduleResource\RelationManagers;

use App\Models\Service;
use App\Models\ServiceDServices;
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
use Illuminate\Database\Eloquent\Model;

class ServiceDServicesRelationManager extends RelationManager
{
    protected static string $relationship = 'ServiceDServices';

    protected static ?string $title = 'Services';
    protected static ?string $pluralLabel = 'Services';
    protected static ?string $modelLabel = 'Services';

    public static function updateSubtotal($get, $set): void
    {
        $service = Service::find($get('services_id'));
        
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
                Select::make('services_id')
                ->relationship('services', 'name')
                ->required()
                ->preload()
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
                ->numeric()
                ->afterStateUpdated(
                    function (Set $set, Get $get) {
                        Self::updateSubtotal($get, $set);
                    }
                )
                ->gt(0),
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
            ->poll('2s')
            ->columns([
                Tables\Columns\TextColumn::make('service_name'),
                Tables\Columns\TextColumn::make('jumlah'),
                Tables\Columns\CheckboxColumn::make('checklist_hasil'),
                Tables\Columns\TextInputColumn::make('keterangan'),
                Tables\Columns\TextColumn::make('harga_unit')
                ->visible(auth()->user()->hasRole(['Kepala Mekanik', 'super_admin', 'manager']))
                ->money('IDR', locale: 'id_ID'),
                Tables\Columns\TextColumn::make('harga_subtotal')
                ->visible(auth()->user()->hasRole(['Kepala Mekanik', 'super_admin', 'manager']))
                ->money('IDR', locale: 'id_ID'),
                Tables\Columns\TextColumn::make('estimasi_waktu_pengerjaan'),


                Tables\Columns\TextColumn::make('harga_subtotal')
                ->visible(auth()->user()->hasRole(['Kepala Mekanik', 'super_admin', 'manager']))
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
                Tables\Actions\CreateAction::make()
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

    public function canCreate(): bool
    {
        return auth()->user()->hasRole(['Kepala Mekanik', 'super_admin', 'manager']);
    }

    public function canDelete(Model $record): bool
    {
        return auth()->user()->hasRole(['Kepala Mekanik', 'super_admin', 'manager']);
    }
}
