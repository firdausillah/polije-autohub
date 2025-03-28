<?php

namespace App\Filament\Resources\ServiceScheduleResource\RelationManagers;

use App\Models\Sparepart;
use App\Models\SparepartSatuans;
use Filament\Forms;
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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServiceDSparepartRelationManager extends RelationManager
{
    protected static string $relationship = 'ServiceDSparepart';

    protected static ?string $title = 'Sparepart';
    protected static ?string $pluralLabel = 'Sparepart';
    protected static ?string $modelLabel = 'Sparepart';

    public static function updateSubtotal($get, $set): void
    {
        $sparepart_satuan = SparepartSatuans::where(['id' => $get('sparepart_satuan_id')])->with('sparepart')->first();

        $harga_subtotal = floatval($sparepart_satuan->harga) * floatval(($get('jumlah_unit')??0));

        $set('harga_unit', $sparepart_satuan->harga);
        $set('harga_subtotal', $harga_subtotal);
        $set('sparepart_id', $sparepart_satuan->sparepart_id);
        $set('satuan_id', $sparepart_satuan->satuan_id);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('sparepart_satuan_id')
                ->relationship('sparepartSatuan', 'sparepart_name')
                ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->sparepart->name} - {$record->satuan_name} ({$record->harga})")
                ->searchable()
                ->preload()
                ->live()
                ->afterStateUpdated(
                    function(Set $set, Get $get){
                        self::updateSubtotal($get, $set);
                    }
                ),

                Hidden::make('sparepart_id'),
                Hidden::make('satuan_id'),
                Hidden::make('harga_unit'),
                Hidden::make('pajak'),

                TextInput::make('jumlah_unit')
                    ->required()
                    ->default(1)
                    ->numeric()
                    ->live()
                    ->afterStateUpdated(
                        function (Get $get, Set $set, $state) {
                            ($state != '' ? self::updateSubtotal($get, $set) : '');
                        }
                    )
                    ->gt(0)
                    ->disabled(fn (Get $get) => !$get('sparepart_satuan_id')),
                TextInput::make('harga_subtotal')
                ->required()
                ->prefix('Rp')
                ->readOnly(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->poll('2s')
            ->columns([
                Tables\Columns\TextColumn::make('sparepart_name')
                ->searchable(),
                Tables\Columns\TextColumn::make('satuan_name'),
                Tables\Columns\TextColumn::make('jumlah_unit'),
                Tables\Columns\CheckboxColumn::make('checklist_hasil'),
                Tables\Columns\TextInputColumn::make('keterangan')
                ->visible(auth()->user()->hasRole(['Kepala Mekanik', 'Mekanik'])),
                Tables\Columns\TextColumn::make('harga_unit')
                ->visible(auth()->user()->hasRole(['Kepala Mekanik', 'super_admin', 'manager']))
                    ->money('IDR', locale: 'id_ID'),
                Tables\Columns\TextColumn::make('harga_subtotal')
                ->visible(auth()->user()->hasRole(['Kepala Mekanik', 'super_admin', 'manager']))
                    ->summarize(
                        Sum::make()
                            ->money('IDR', locale: 'id_ID')
                            ->label('Total')
                    )
                    ->money('IDR', locale: 'id_ID')

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

    public function canCreate(): bool
    {
        return auth()->user()->hasRole(['Kepala Mekanik', 'super_admin', 'manager']);
    }

    public function canDelete(Model $record): bool
    {
        return auth()->user()->hasRole(['Kepala Mekanik', 'super_admin', 'manager']);
    }
}
