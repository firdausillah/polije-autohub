<?php

namespace App\Filament\Resources\StockAdjustmentResource\RelationManagers;

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
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class StockDAdjustmentRelationManager extends RelationManager
{
    protected static string $relationship = 'StockDAdjustment';

    protected static ?string $title = 'Detail Sparepart';
    protected static ?string $pluralLabel = 'Detail Sparepart';
    protected static ?string $modelLabel = 'Detail Sparepart';

    public static function updateJumlahTerkecil($get, $set): void
    {
        $sparepart_satuan = SparepartSatuans::where(['id' => $get('sparepart_satuan_id')])->with('sparepart')->first();

        $sparepart_inventory = DB::table('inventories')
            ->select('sparepart_id', 'satuan_id', DB::raw("
                COALESCE(SUM(
                    CASE 
                        WHEN movement_type IN ('IN-PUR', 'IN-ADJ') THEN jumlah_terkecil
                        WHEN movement_type IN ('OUT-SAL', 'OUT-ADJ') THEN -jumlah_terkecil
                        ELSE 0
                    END
                ), 0) as saldo
            "))
            ->where('tanggal_transaksi', '<=', now())
            ->where('sparepart_id', $sparepart_satuan->sparepart->id)
            ->groupBy('sparepart_id', 'satuan_id')
            ->first();
        // dd($sparepart_inventory);


        $set('harga_unit', $sparepart_satuan->harga);

        $set('jumlah_unit', $sparepart_inventory->saldo);
        $set('jumlah_terkecil_selisih', 0);
        $set('jumlah_terkecil_inventory', $sparepart_inventory->saldo);
        $set('sparepart_id', $sparepart_inventory->sparepart_id);
        $set('satuan_id', $sparepart_inventory->satuan_id);
    }

    // public static function updateJumlahSelisih($get, $set){
        
    // }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('sparepart_satuan_id')
                ->relationship('sparepartSatuan', 'id')
                ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->sparepart->name} - {$record->satuan_name}")
                ->searchable()
                ->preload()
                ->live()
                ->afterStateUpdated(
                    function (Get $get, Set $set, $state) {
                        ($state != '' ? self::updateJumlahTerkecil($get, $set) : 0);
                    }
                )
                ->getSearchResultsUsing(function (string $search) {
                    return \App\Models\SparepartSatuans::query()
                        ->whereHas('sparepart', fn ($query) => 
                            $query->where('name', 'like', "%{$search}%")
                        )
                        ->get()
                        ->mapWithKeys(function ($record) {
                            return [$record->id => "{$record->sparepart->name} - {$record->satuan_name}"];
                        });
                }),
                TextInput::make('jumlah_terkecil_inventory')
                ->readOnly()
                ->label('Jumlah Unit di Sistem')
                ->live(),
                TextInput::make('jumlah_unit')
                ->label('Jumlah Unit Fisik')
                ->afterStateUpdated(
                    function (Get $get, Set $set, $state){
                        $selisih =  $state - $get('jumlah_terkecil_inventory');
                        $set('jumlah_terkecil_selisih', $selisih);
                    }
                )
                ->live(debounce: 500),
                TextInput::make('jumlah_terkecil_selisih')
                ->readOnly()
                ->label('Selisih')
                ->live(),

                Hidden::make('harga_unit'),
                Hidden::make('sparepart_id'),
                Hidden::make('satuan_id'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('sparepart_name')
            ->columns([
                Tables\Columns\TextColumn::make('sparepart_name'),
                Tables\Columns\TextColumn::make('jumlah_terkecil_inventory')
                ->label('Jumlah Sistem'),
                Tables\Columns\TextColumn::make('jumlah_terkecil_selisih')
                ->label('Jumlah Selisih'),
                Tables\Columns\TextColumn::make('jumlah_terkecil')
                ->label('Jumlah Fisik'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->hidden(fn () => $this->getOwnerRecord()->is_approve === 'approved'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->hidden(fn () => $this->getOwnerRecord()->is_approve === 'approved'),
                Tables\Actions\DeleteAction::make()->hidden(fn () => $this->getOwnerRecord()->is_approve === 'approved'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
