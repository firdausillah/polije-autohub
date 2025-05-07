<?php

namespace App\Filament\Resources\ServiceScheduleResource\RelationManagers;

use App\Models\Service;
use App\Models\ServiceDServices;
use App\Models\ServiceSchedule;
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
use Illuminate\Support\Collection;

class ServiceDServicesRelationManager extends RelationManager
{
    protected static string $relationship = 'ServiceDServices';

    protected static ?string $title = 'Services';
    protected static ?string $pluralLabel = 'Services';
    protected static ?string $modelLabel = 'Services';

    public static function updateSubtotal($get, $set, $is_customer_umum): void
    {
        $service = Service::find($get('service_id'));
        
        $harga = $is_customer_umum==1?$service->harga_1: $service->harga_2;

        $harga_subtotal = floatval($harga ?? 0) * floatval($get('jumlah') ?? 0);
        $estimasi_waktu_pengerjaan = ($service->estimasi_waktu_pengerjaan ?? 0) * floatval(($get('jumlah') ?? 0));

        $set('service_name', $service->name??'');
        $set('service_kode', $service->kode??'');
        $set('harga_unit', $harga??0);
        $set('estimasi_waktu_pengerjaan', $estimasi_waktu_pengerjaan);
        $set('harga_subtotal', $harga_subtotal);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('service_m_type_id')
                ->relationship('serviceMType', 'name')
                ->label('Tipe Service')
                ->required()
                ->preload(),
                Select::make('service_id')
                ->relationship('service', 'name')
                ->required()
                ->preload()
                ->live()
                ->options(fn (Get $get): Collection => Service::query() ->where('service_m_type_id', $get('service_m_type_id')) ->pluck('name', 'id'))
                ->afterStateUpdated(
                    function(Set $set, Get $get){
                        $is_customer_umum = $this->getOwnerRecord()->is_customer_umum;
                        Self::updateSubtotal($get, $set, $is_customer_umum);
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
                        $is_customer_umum = $this->getOwnerRecord()->is_customer_umum;
                        Self::updateSubtotal($get, $set, $is_customer_umum);
                    }
                )
                ->gt(0),
                Grid::make()
                ->columns(3)
                ->schema([
                    TextInput::make('harga_unit')
                    ->required()
                    ->label('Harga')
                    ->prefix('Rp')
                    ->readOnly(),
                    TextInput::make('harga_subtotal')
                    ->required()
                    ->prefix('Rp')
                    ->readOnly(),
                    Textinput::make('estimasi_waktu_pengerjaan')
                    ->required()
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
                Tables\Columns\TextInputColumn::make('keterangan')
                ->visible(auth()->user()->hasRole(['Kepala Unit', 'Mekanik'])),
                Tables\Columns\TextColumn::make('harga_unit')
                ->visible(auth()->user()->hasRole(['Kepala Unit', 'super_admin', 'manager']))
                ->money('IDR', locale: 'id_ID'),
                Tables\Columns\TextColumn::make('harga_subtotal')
                ->visible(auth()->user()->hasRole(['Kepala Unit', 'super_admin', 'manager']))
                ->money('IDR', locale: 'id_ID'),
                Tables\Columns\TextColumn::make('estimasi_waktu_pengerjaan'),


                Tables\Columns\TextColumn::make('harga_subtotal')
                ->visible(auth()->user()->hasRole(['Kepala Unit', 'super_admin', 'manager']))
                ->summarize(
                    Sum::make()
                        ->money('IDR', locale: 'id_ID')
                        ->label('Total')
                )
                ->money('IDR', locale: 'id_ID'),
                Tables\Columns\TextColumn::make('estimasi_waktu_pengerjaan')
                ->label('Estimasi Waktu')
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
                Tables\Actions\CreateAction::make()->hidden(fn () => $this->getOwnerRecord()->is_approve === 'approved')
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->hidden(fn () => $this->getOwnerRecord()->is_approve === 'approved'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->visible(!auth()->user()->hasRole('Mekanik')),
                ]),
            ]);
    }

    public function canCreate(): bool
    {
        return auth()->user()->hasRole(['Kepala Unit']);
    }

    public function canDelete(Model $record): bool
    {
        return auth()->user()->hasRole(['Kepala Unit']);
    }
}
