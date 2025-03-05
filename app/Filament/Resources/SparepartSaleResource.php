<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SparepartSaleResource\Pages;
use App\Filament\Resources\SparepartSaleResource\RelationManagers;
use App\Filament\Resources\SparepartSaleResource\RelationManagers\SparepartDSaleRelationManager;
use App\Helpers\CodeGenerator;
use App\Models\Account;
use App\Models\Inventory;
use App\Models\Jurnal;
use App\Models\Sparepart;
use App\Models\SparepartDSale;
use App\Models\SparepartSale;
use App\Models\SparepartSatuans;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\Filter as FiltersFilter;
use Filament\Tables\Actions\ActionGroup as TablesActionsActionGroup;
use Illuminate\Support\Facades\Auth as FacadesAuth;

class SparepartSaleResource extends Resource
{
    protected static ?string $model = SparepartSale::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'POS & Cash Flow';

    public static function updateSubtotal($get, $set): void
    {
        $sparepart_satuan = SparepartSatuans::where(['id' => $get('sparepart_satuan_id')])->with('sparepart')->first();

        $harga_subtotal = floatval($sparepart_satuan->harga) * floatval(($get('jumlah_unit')));

        // dd($sparepart);

        $is_pajak = $sparepart_satuan->sparepart->is_pajak;
        if ($is_pajak == 1) {
            $pajak = $harga_subtotal * 0.11;
            $set('pajak', $pajak);
        }else{
            $pajak = 0;
            $set('pajak', 0);
        }

        $set('harga_subtotal', $harga_subtotal);

    }

    public static function updatedTotals($get, $set): void
    {
        $selectedSparepart = collect($get('SparepartDSale'))->filter(fn ($item) => !empty($item['sparepart_satuan_id']));
        $detail_harga = SparepartSatuans::whereIn('id', $selectedSparepart->pluck('sparepart_satuan_id'))->with('sparepart')->get();
        // dd($detail_harga->pluck('harga', 'id'));
        // dd($detail_harga->pluck('sparepart.is_pajak', 'id'));

        // $harga = 
        // $pajak = ($detail_harga->sparepart->is_pajak==0?$detail_harga->harga*0.11:0);

        // $harga_subtotal = 0;
        // $pajak = 0;
        $subtotal = $selectedSparepart->reduce(function ($subtotal, $item) use ($detail_harga) {
            return $subtotal + ($detail_harga[$item['home_service_id']] * 1);
        }, 0);
        // $pajak = $selectedSparepart->reduce(function ($subtotal, $item) use ($detail_harga) {
        //     return $subtotal + ($detail_harga[$item['home_service_id']] * 1);
        // }, 0);
        dd($subtotal);
        // $selectedSparepart = collect($livewire->data['SparepartDSale'] ?? [])->filter(fn ($item) => !empty($item['sparepart_satuan_id']));
        
        $set('sub_total', $selectedSparepart->sum('harga_subtotal'));
        $set('total', $selectedSparepart->sum('harga_subtotal') + $selectedSparepart->sum('pajak'));
        $set('pajak_total', $selectedSparepart->sum('pajak'));
    }

    public static function InsertJurnal($record, $status): void
    {
        if ($status == 'approved') {
            // jurnal begin
            // debit
            Jurnal::create([
                'transaksi_h_id'    => $record->id,
                'transaksi_d_id'    => $record->id,
                'account_id'    => $record->account_id, //

                'keterangan'    => $record->keterangan,
                'kode'  => $record->kode,
                'tanggal_transaksi' => $record->tanggal_transaksi,

                'relation_name' => $record->customer_name,
                'relation_nomor_telepon'    => $record->customer_nomor_telepon,

                'account_name'  => $record->account_name, //
                'account_kode'  => $record->account_kode, //
                'transaction_type'  => 'pembelian sparepart',

                'debit' => $record->total,
                'kredit'    => 0,
            ]);

            // kredit
            $account_kredit = Account::find(5);
            Jurnal::create([
                'transaksi_h_id'    => $record->id,
                'transaksi_d_id'    => $record->id,
                'account_id'    => $account_kredit->id,

                'keterangan'    => $record->keterangan,
                'kode'  => $record->kode,
                'tanggal_transaksi' => $record->tanggal_transaksi,

                'relation_name' => $record->customer_name,
                'relation_nomor_telepon'    => $record->customer_nomor_telepon,

                'account_name'  => $account_kredit->name,
                'account_kode'  => $account_kredit->kode,
                'transaction_type'  => 'pembelian sparepart',

                'debit' => 0,
                'kredit'    => $record->total,
            ]);
            // jurnal end

            // inventory begin
            $SparepartDSales = SparepartDSale::where('sparepart_sale_id', $record->id)->get();
            foreach ($SparepartDSales as $val) {
                Inventory::create([
                    'transaksi_h_id' => $record->id,
                    'transaksi_d_id' => $val->id,
                    'sparepart_id' => $val->sparepart_id,
                    'satuan_id' => $val->satuan_id,

                    'name' => '',
                    'kode' => $record->kode,
                    'keterangan' => $record->keterangan,
                    'tanggal_transaksi' => $record->tanggal_transaksi,
                    'transaksi_h_kode' => $record->kode,

                    'sparepart_name' => $val->sparepart_name,
                    'sparepart_kode' => $val->sparepart_kode,

                    'satuan_terkecil_name' => $val->satuan_terkecil_name,
                    'satuan_terkecil_kode' => $val->satuan_terkecil_kode,

                    'movement_type' => 'IN-PUR',

                    'jumlah_unit' => $val->jumlah_unit,
                    'jumlah_konversi' => $val->jumlah_konversi,
                    'jumlah_terkecil' => $val->jumlah_terkecil,

                    'harga_unit' => $val->harga_unit,
                    'harga_terkecil' => $val->harga_terkecil,
                    'harga_subtotal' => $val->harga_subtotal,

                    'relation_name' => $record->customer_name,
                    'relation_nomor_telepon' => $record->customer_nomor_telepon
                ]);
            }
            // inventory end
        } else {
            Inventory::where(['transaksi_h_id' => $record->id, 'movement_type' => 'IN-PUR'])->delete();
            Jurnal::where(['transaksi_h_id' => $record->id, 'transaction_type' => 'pembelian sparepart'])->delete();
        }
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Order')
                    ->schema([
                        Repeater::make('SparepartDSale')
                            ->label('Order Sparepart')
                            ->relationship('SparepartDSale')
                            ->columns([
                                // 'sm' =>1,
                                'md' =>2,
                                // 'lg' =>3
                                ])
                            ->schema([
                                Select::make('sparepart_satuan_id')
                                ->relationship('sparepartSatuan', 'sparepart_name')
                                ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->sparepart->name} - {$record->satuan_name} ({$record->harga})")
                                ->searchable()
                                ->preload()
                                ->live() // Trigger update saat berubah
                                ->afterStateUpdated(
                                    function (Get $get, Set $set, $state) {
                                        $set('jumlah_unit', 0);
                                        // $set('pajak', 0);
                                        // $set('harga_subtotal', 0);
                                        self::updateSubtotal($get, $set);
                                    }
                                ),

                                TextInput::make('jumlah_unit')
                                    // ->default(1)
                                    ->required()
                                    ->numeric()
                                    ->live()
                                    ->afterStateUpdated(
                                        function (Get $get, Set $set, $state){
                                            ($state !='' ? self::updateSubtotal($get, $set) : '');

                                        }
                                    )
                                    ->gt(0)
                                    ->disabled(fn (Get $get) => !$get('sparepart_satuan_id')),
                                TextInput::make('pajak')
                                ->live()
                                ->label('Pajak')
                                ->gt(0)
                                ->prefix('Rp ')
                                ->numeric()
                                ->readOnly(),
                                TextInput::make('harga_subtotal')
                                ->required()
                                ->live()
                                ->label('Harga subtotal')
                                ->gt(0)
                                ->prefix('Rp ')
                                ->numeric()
                                ->readOnly(),
                            ])
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                self::updatedTotals($get, $set);
                            }),

                        Grid::make()
                            ->columns('3')
                            ->schema([
                                TextInput::make('sub_total')
                                    ->gt(0)
                                    ->prefix('Rp ')
                                    ->numeric()
                                    ->readOnly(),
                                TextInput::make('total')
                                    ->gt(0)
                                    ->prefix('Rp ')
                                    ->numeric()
                                    ->readOnly(),
                                TextInput::make('pajak_total')
                                    ->gt(0)
                                    ->prefix('Rp ')
                                    ->numeric()
                                    ->readOnly()
                            ])
                    ]),
                    Wizard\Step::make('Data Pelanggan')
                        ->schema([
                            TextInput::make('customer_name')
                            ->label('Nama pelanggan'),
                            TextInput::make('customer_nomor_telepon')
                            ->label('Nomor Telepon Pelanggan')
                        ]),
                    Wizard\Step::make('Pembayaran')
                    ->schema([
                        Grid::make()
                            ->columns('2')
                            ->schema([
                                Select::make('account_id')
                                ->required()
                                ->relationship('account', 'name')
                                ->live()
                                ->afterStateUpdated(function (Set $set, $state) {
                                    $account = Account::find($state);
                                    $set('account_name', $account->name);
                                    $set('account_kode', $account->kode);
                                }),
                                FileUpload::make('photo')
                                    ->label('Bukti pembayaran')
                                    ->image()
                                    ->resize(50),
                                Hidden::make('account_name'),
                                Hidden::make('account_kode'),
                            ])
                    ]),
                ])
                ->columnSpan('full')
                ->skippable()
            ])->disabled(fn ($record) => $record && $record->is_approve === 'approved');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tanggal_transaksi')
                ->label('Tanggal'),
                TextColumn::make('kode')
                    ->searchable(),
                TextColumn::make('is_approve')
                ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    }),
                TextColumn::make('customer_name')
                ->searchable()
                    ->label('customer'),
                TextColumn::make('account_name')
                ->searchable()
                    ->label('account'),
                TextColumn::make('total')->money('IDR', locale: 'id_ID'),

            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                FiltersFilter::make('created_at')
                ->form([
                    DatePicker::make('from')->default(Carbon::now()->startOfMonth()),
                    DatePicker::make('to')->default(Carbon::now()->endOfMonth()),
                ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn ($query) => $query->whereDate('created_at', '>=', $data['from']))
                            ->when($data['to'], fn ($query) => $query->whereDate('created_at', '<=', $data['to']));
                    })
                    ->indicateUsing(function (array $data) {
                        return 'Data Bulan Ini';
                    }),
            ])
            ->actions([
                TablesActionsActionGroup::make([
                    Tables\Actions\Action::make('approve')
                    ->action(function (SparepartSale $record) {
                        if (empty($record->kode)) {
                            $record->kode = CodeGenerator::generateTransactionCode('SSL', 'sparepart_sales', 'kode');
                        }

                        $isApproving = in_array($record->is_approve, ['pending', 'rejected']);
                        $status = $isApproving ? 'approved' : 'rejected';

                        self::InsertJurnal($record, $status);
                        $record->is_approve = $status;
                        $record->approved_by = FacadesAuth::id();
                        $record->save();

                        Notification::make()
                            ->title("Sparepart Sale $status")
                            ->success()
                            ->body("Sparepart Sale has been $status.")
                            ->send();
                    })
                        ->color(fn (SparepartSale $record) => $record->is_approve === 'approved' ? 'danger' : 'info')
                        ->icon(fn (SparepartSale $record) => $record->is_approve === 'approved' ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                        ->requiresConfirmation()
                        ->label(fn (SparepartSale $record) => $record->is_approve === 'approved' ? 'Reject' : 'Approve'),

                    Tables\Actions\EditAction::make()
                        ->visible(fn (SparepartSale $record) => $record->is_approve !== 'approved'),
                ])
            ])

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    // public static function getRelations(): array
    // {
    //     return [
    //         SparepartDSaleRelationManager::class,
    //     ];
    // }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSparepartSales::route('/'),
            'create' => Pages\CreateSparepartSale::route('/create'),
            'edit' => Pages\EditSparepartSale::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function canDelete($record): bool
    {
        return $record->is_approve !== 'approved';
    }
}
