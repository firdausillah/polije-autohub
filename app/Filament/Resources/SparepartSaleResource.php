<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SparepartSaleResource\Pages;
use App\Filament\Resources\SparepartSaleResource\RelationManagers;
use App\Filament\Resources\SparepartSaleResource\RelationManagers\SparepartDSaleRelationManager;
use App\Helpers\CodeGenerator;
use App\Models\Account;
use App\Models\Inventory;
use App\Models\Jurnal;
use App\Models\Modal;
use App\Models\Sparepart;
use App\Models\SparepartDSale;
use App\Models\SparepartDSalePayment;
use App\Models\SparepartSale;
use App\Models\SparepartSatuans;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Livewire;
use Livewire\Component as LivewireComponent;
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

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    public static function getModelLabel(): string
    {
        return 'Penjualan Sparepart';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Penjualan Sparepart';
    }

    public static function updateSubtotal($get, $set): void
    {
        $sparepart_satuan = SparepartSatuans::where(['id' => $get('sparepart_satuan_id')])->with('sparepart')->first();

        $harga_subtotal = floatval($sparepart_satuan->harga) * floatval(($get('jumlah_unit')));

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
        $set('sparepart_id', $sparepart_satuan->sparepart_id);
        $set('satuan_id', $sparepart_satuan->satuan_id);
    }

    public static function updatedTotals($get, $set): void
    {
        $selectedSparepart = collect($get('sparepartDSale'))->filter(fn ($item) => !empty($item['sparepart_satuan_id']));
        $detail_harga = SparepartSatuans::whereIn('id', $selectedSparepart->pluck('sparepart_satuan_id'))->with('sparepart')->get()->keyBy('id');
        
        $harga_subtotal = $selectedSparepart->map(function ($item) use ($detail_harga) {
            return $item['jumlah_unit'] * $detail_harga[$item['sparepart_satuan_id']]->harga;
        })->sum();
        // dd($selectedSparepart);

        $total_pajak = $selectedSparepart->map(function ($item) use ($detail_harga) {
            return ($detail_harga[$item['sparepart_satuan_id']]->sparepart->is_pajak? ($item['jumlah_unit'] * $detail_harga[$item['sparepart_satuan_id']]->harga)*0.11:0);
        })->sum();
        
        
        $set('total', $harga_subtotal);
        $set('pajak_total', $total_pajak);
    }

    public static function InsertJurnal($record, $status): void
    {
        if ($status == 'approved') {
            // jurnal begin

            // Jurnal Penjualan Sparepart

            // D. Kas/Bank xxx  
            //     C. Pendapatan Sparepart xxx  
            //     C. Utang PPN Keluaran


            // Jurnal HPP (Harga Pokok Penjualan Sparepart)

            // D. Harga Pokok Penjualan xxx  
            //     C. Persediaan Sparepart xxx  


            // debit
            //Kas / Bank
            $payment = SparepartDSalePayment::where('sparepart_sale_id', $record->id)->get();
            foreach ($payment as $key => $val) {
                Jurnal::create([
                    'transaksi_h_id'    => $val->sparepart_sale_id,
                    'transaksi_d_id'    => $val->id,
                    'account_id'    => $val->account_id, //
    
                    'keterangan'    => $val->keterangan,
                    'kode'  => $record->kode,
                    'tanggal_transaksi' => $record->tanggal_transaksi,
    
                    'relation_name' => $record->customer_name,
                    'relation_nomor_telepon'    => $record->customer_nomor_telepon,
    
                    'account_name'  => $val->account_name, //
                    'account_kode'  => $val->account_kode, //
                    'transaction_type'  => 'penjualan sparepart',
    
                    'debit' => $val->jumlah_bayar,
                    'kredit'    => 0,
                ]);
            }

            // kredit
            $account_kredit = Account::find(7); //Pendapatan Penjualan Sparepart
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
                'transaction_type'  => 'penjualan sparepart',

                'debit' => 0,
                'kredit'    => $record->total - $record->pajak_total,
            ]);

            if($record->pajak_total){
                $account_kredit_pajak = Account::find(9); //Utang PPN Keluaran
                Jurnal::create([
                    'transaksi_h_id'    => $record->id,
                    'transaksi_d_id'    => $record->id,
                    'account_id'    => $account_kredit_pajak->id,
    
                    'keterangan'    => $record->keterangan,
                    'kode'  => $record->kode,
                    'tanggal_transaksi' => $record->tanggal_transaksi,
    
                    'relation_name' => $record->customer_name,
                    'relation_nomor_telepon'    => $record->customer_nomor_telepon,
    
                    'account_name'  => $account_kredit_pajak->name,
                    'account_kode'  => $account_kredit_pajak->kode,
                    'transaction_type'  => 'penjualan sparepart',
    
                    'debit' => 0,
                    'kredit'    => $record->pajak_total,
                ]);
            }


            // hpp begin
            $sparepart = SparepartDSale::where('sparepart_sale_id', $record->id)->get();
            $account_hpp = Account::find(10); //Harga Pokok Penjualan
            $account_persediaan = Account::find(3); //Persediaan Sparepart
            foreach ($sparepart as $key => $val) {
                $harga_modal = Modal::where('sparepart_id', $val->sparepart_id)->orderBy('id', 'desc')->first()->harga_modal;

                Jurnal::create([
                    'transaksi_h_id'    => $val->sparepart_sale_id,
                    'transaksi_d_id'    => $val->sparepart_id,
                    'account_id'    => $account_hpp->id, //

                    'keterangan'    => $val->sparepart_kode.' - '. $val->sparepart_name,
                    'kode'  => $record->kode,
                    'tanggal_transaksi' => $record->tanggal_transaksi,

                    'relation_name' => $record->customer_name,
                    'relation_nomor_telepon'    => $record->customer_nomor_telepon,

                    'account_name'  => $account_hpp->name, //
                    'account_kode'  => $account_hpp->kode, //
                    'transaction_type'  => 'HPP Penjualan',

                    'debit' => $harga_modal,
                    'kredit'    => 0
                ]);

                Jurnal::create([
                        'transaksi_h_id'    => $val->sparepart_sale_id,
                        'transaksi_d_id'    => $val->sparepart_id,
                        'account_id'    => $account_persediaan->id, //

                        'keterangan'    => $val->sparepart_kode.' - '. $val->sparepart_name,
                        'kode'  => $record->kode,
                        'tanggal_transaksi' => $record->tanggal_transaksi,

                        'relation_name' => $record->customer_name,
                        'relation_nomor_telepon'    => $record->customer_nomor_telepon,

                        'account_name'  => $account_persediaan->name, //
                        'account_kode'  => $account_persediaan->kode, //
                        'transaction_type'  => 'HPP Penjualan',

                        'debit' => 0,
                        'kredit'    => $harga_modal
                ]);
            }
            // hpp end
            // jurnal end

            // inventory begin
            $sparepartDSales = SparepartDSale::where('sparepart_sale_id', $record->id)->get();
            foreach ($sparepartDSales as $val) {
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

                    'movement_type' => 'OUT-SAL',

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
            Inventory::where(['transaksi_h_id' => $record->id, 'movement_type' => 'OUT-SAL'])->delete();
            Jurnal::where(['transaksi_h_id' => $record->id, 'transaction_type' => 'penjualan sparepart'])->delete();
            Jurnal::where(['transaksi_h_id' => $record->id, 'transaction_type' => 'HPP Penjualan'])->delete();
        }
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Order')
                    ->schema([
                        Repeater::make('sparepartDSale')
                            ->label('Order Sparepart')
                            ->relationship('sparepartDSale')
                            ->columns([
                                'md' =>3,
                                ])
                            ->schema([
                                Select::make('sparepart_satuan_id')
                                    ->relationship('sparepartSatuan', 'sparepart_name')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->sparepart->name} - {$record->satuan_name} ({$record->harga})")
                                    ->searchable()
                                    ->preload()
                                    ->live(),

                                Hidden::make('sparepart_id'),
                                Hidden::make('satuan_id'),
                                Hidden::make('harga_unit'),
                                Hidden::make('pajak'),

                                TextInput::make('jumlah_unit')
                                    ->required()
                                    // ->default(1)
                                    ->numeric()
                                    ->live()
                                    ->afterStateUpdated(
                                        function (Get $get, Set $set, $state){
                                            ($state !='' ? self::updateSubtotal($get, $set) : '');

                                        }
                                    )
                                    ->gt(0)
                                    ->disabled(fn (Get $get) => !$get('sparepart_satuan_id')),
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
                            ->columns('2')
                            ->schema([
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
                        Repeater::make('sparepartDSalePayment')
                        ->label('Order Sparepart')
                        ->relationship('sparepartDSalePayment')
                        ->schema([
                            Grid::make()
                                ->columns('2')
                                ->schema([
                                    Select::make('account_id')
                                    ->required()
                                    ->relationship('account', 'name')
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get, $state, $livewire) {
                                        $account = Account::find($state);
                                        $set('account_name', $account->name);
                                        $set('account_kode', $account->kode);
                                        // dd(count($livewire->data['sparepart_d_sale_payments']));

                                        if (count($livewire->data['sparepartDSalePayment']) == 1) {
                                            $set('jumlah_bayar', $livewire->data['total']);
                                        }else{
                                            $set('jumlah_bayar', $livewire->data['total'] - array_sum(array_column($livewire->data['sparepartDSalePayment'], 'jumlah_bayar')));

                                        }
                                        
                                    }),
                                    TextInput::make('jumlah_bayar')
                                    ->required(),
                                    FileUpload::make('photo')
                                        ->label('Bukti pembayaran')
                                        ->image()
                                        ->resize(50),
                                    TextInput::make('account_name'),
                                    TextInput::make('account_kode'),
                                ])
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
                ->sortable('desc')
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
