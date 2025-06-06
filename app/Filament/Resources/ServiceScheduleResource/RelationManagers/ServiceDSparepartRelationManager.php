<?php

namespace App\Filament\Resources\ServiceScheduleResource\RelationManagers;

use App\Models\Sparepart;
use App\Models\SparepartSatuans;
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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;

class ServiceDSparepartRelationManager extends RelationManager
{
    protected static string $relationship = 'ServiceDSparepart';

    protected static ?string $title = 'Sparepart';
    protected static ?string $pluralLabel = 'Sparepart';
    protected static ?string $modelLabel = 'Sparepart';

    public static function updateSubtotal($get, $set): void
    {
        $sparepart_satuan = SparepartSatuans::where(['id' => $get('sparepart_satuan_id')])->with('sparepart')->first();
        if($sparepart_satuan != null){
            $harga_subtotal = floatval($sparepart_satuan->harga) * floatval(($get('jumlah_unit')??0));
            $discount = (float) $get('discount');
        
            $is_pajak = Sparepart::find($sparepart_satuan->sparepart_id)->is_pajak;
            if ($is_pajak == 1) {
                $pajak = $harga_subtotal * 0.11;
                $set('pajak', $pajak);
            } else {
                $pajak = 0;
                $set('pajak', 0);
            }
        
            $set('harga_unit', $sparepart_satuan->harga);
            $set('harga_subtotal', $harga_subtotal);
            $set('total', $harga_subtotal-$discount);

            $set('sparepart_id', $sparepart_satuan->sparepart_id);
            $set('satuan_id', $sparepart_satuan->satuan_id);
        }

    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Select::make('sparepart_satuan_id')
                // ->relationship('sparepartSatuan', 'sparepart_name')
                // ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->sparepart->name} - {$record->satuan_name}")
                // ->searchable()
                // ->preload()
                // ->live()
                // ->afterStateUpdated(
                //     function(Set $set, Get $get){
                //         self::updateSubtotal($get, $set);
                //     }
                // ),

                Select::make('sparepart_m_category_id')
                ->relationship('sparepartMCategory', 'name')
                ->label('Kategori Sparepart')
                ->required()
                ->searchable()
                ->preload(),
                Select::make('sparepart_satuan_id')
                ->label('Sparepart')
                ->required()
                ->preload()
                ->live()
                ->options(
                    fn (Get $get): Collection =>
                    \App\Models\SparepartSatuans::whereHas(
                        'sparepart',
                        fn ($q) =>
                        $q->where('sparepart_m_category_id', $get('sparepart_m_category_id'))
                    )
                    ->get()
                    ->mapWithKeys(fn ($item) => [
                        $item->id => "{$item->sparepart->kode} - {$item->sparepart->name}"
                    ])
                )
                ->afterStateUpdated(
                    function(Set $set, Get $get){
                        self::updateSubtotal($get, $set);
                    }
                )
                ->searchable(),

                Hidden::make('sparepart_id'),
                Hidden::make('satuan_id'),
                Hidden::make('pajak'),

                TextInput::make('jumlah_unit')
                    ->required()
                    ->default(1)
                    ->numeric()
                    ->live(debounce: 500)
                    ->afterStateUpdated(
                        function (Get $get, Set $set, $state) {
                            ($state != NULL ? self::updateSubtotal($get, $set) : 0);
                        }
                    )
                    ->gt(0)
                    ->disabled(fn (Get $get) => !$get('sparepart_satuan_id')),
                TextInput::make('discount')
                ->currencyMask(',')
                ->live(debounce: 500)
                ->default(0)
                ->afterStateUpdated(
                    function (Get $get, Set $set, $state) {
                        // ($state != NULL ?  : 0);
                        self::updateSubtotal($get, $set);
                    }
                )
                ->prefix('Rp'),
                Grid::make(['sm' => 3])
                ->schema([
                    TextInput::make('harga_unit')
                    ->currencyMask(',')
                    ->required()
                    ->label('Harga')
                    ->prefix('Rp')
                    ->readOnly(),
                    TextInput::make('harga_subtotal')
                    ->currencyMask(',')
                    ->required()
                    ->prefix('Rp')
                    ->readOnly(),
                    TextInput::make('total')
                    ->currencyMask(',')
                    ->required()
                    ->prefix('Rp')
                    ->readOnly(),
                    // Textinput::make('estimasi_waktu_pengerjaan')
                    // ->required()
                    // ->suffix('Menit')
                    // ->readOnly(),
                ])
            ])
            ->columns(2);
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
                ->visible(auth()->user()->hasRole(['Kepala Unit', 'Mekanik'])),
                Tables\Columns\TextColumn::make('harga_unit')
                ->visible(!auth()->user()->hasRole('mekanik'))
                    ->money('IDR', locale: 'id_ID'),

            Tables\Columns\TextColumn::make('harga_subtotal')
            ->visible(!auth()->user()->hasRole('mekanik'))
            ->summarize(
                Sum::make()
                    ->money('IDR', locale: 'id_ID')
                    ->label('')
            )
            ->money('IDR', locale: 'id_ID'),

            Tables\Columns\TextColumn::make('discount')
            ->visible(!auth()->user()->hasRole('mekanik'))
            ->summarize(
                Sum::make()
                    ->money('IDR', locale: 'id_ID')
                    ->label('')
            )
            ->money('IDR', locale: 'id_ID'),

            Tables\Columns\TextColumn::make('total')
            ->visible(!auth()->user()->hasRole('mekanik'))
            ->summarize(
                Sum::make()
                    ->money('IDR', locale: 'id_ID')
                    ->label('')
            )
            ->money('IDR', locale: 'id_ID'),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->hidden(fn () => $this->getOwnerRecord()->is_approve === 'approved'),
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
