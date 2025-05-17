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
use Illuminate\Support\Facades\Storage;
use Mpdf\Mpdf;
use Illuminate\Support\Str;

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
            $pajak = $harga_subtotal * 0.12;
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

        // dd($selectedSparepart);
        
        $harga_subtotal = $selectedSparepart->map(function ($item) use ($detail_harga) {
            return (float) $item['jumlah_unit'] * $detail_harga[$item['sparepart_satuan_id']]->harga;
        })->sum();

        $discount_d_total = $selectedSparepart->map(function ($item) use ($detail_harga) {
            return (float) $item['discount'];
        })->sum();

        // dd($discount_d_total);
        // $discount_total = $discount_d_total==0?$get('discount_total'):$discount_d_total;

        $total_pajak = $selectedSparepart->map(function ($item) use ($detail_harga) {
            return ($detail_harga[$item['sparepart_satuan_id']]->sparepart->is_pajak? ((float) $item['jumlah_unit'] * $detail_harga[$item['sparepart_satuan_id']]->harga)*0.12:0);
        })->sum();
        
        
        $set('pajak_total', $total_pajak);
        $set('discount_total', $discount_d_total);
        $set('sub_total', $harga_subtotal);
        $set('total', $harga_subtotal - $discount_d_total);
    }

    public static function updatePaymentChange($get, $set): void
    {
        $selectedPaymentMethod = collect($get('sparepartDSalePayment'))->filter(fn ($item) => !empty($item['account_id']));
        $total_jumlah_bayar = $selectedPaymentMethod->map(function ($item) {
            return (float) $item['jumlah_bayar'];
        })->sum();
        
        $payment_change = ($total_jumlah_bayar==0?$get('total'):$total_jumlah_bayar) - $get('total');
        $set('payment_change', $payment_change);
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
    
                    'debit' => (($val->jumlah_bayar-$val->total_payable)<0?$val->jumlah_bayar:$val->total_payable),
                    'kredit'    => 0,
                ]);
            }

            if($record->discount_total){
                $account_debit_discount = Account::find(25); //Diskon Penjualan
                Jurnal::create([
                    'transaksi_h_id'    => $record->id,
                    'transaksi_d_id'    => $record->id,
                    'account_id'    => $account_debit_discount->id,

                    'keterangan'    => $record->keterangan,
                    'kode'  => $record->kode,
                    'tanggal_transaksi' => $record->tanggal_transaksi,

                    'relation_name' => $record->customer_name,
                    'relation_nomor_telepon'    => $record->customer_nomor_telepon,

                    'account_name'  => $account_debit_discount->name,
                    'account_kode'  => $account_debit_discount->kode,
                    'transaction_type'  => 'penjualan sparepart',

                    'debit'    => $record->discount_total,
                    'kredit' => 0,
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
                'kredit'    => $record->total + $record->discount_total,
                // 'kredit'    => $record->total - $record->pajak_total,
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

                    'debit' => $harga_modal * $val->jumlah_terkecil,
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
                        'kredit'    => $harga_modal * $val->jumlah_terkecil
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
                                'md' =>2,
                                ])
                            ->schema([
                                Select::make('sparepart_satuan_id')
                                    ->relationship('sparepartSatuan', 'sparepart_name')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->sparepart->name} - {$record->satuan_name} ({$record->harga})")
                                    ->searchable()
                                    ->preload()
                                    ->afterStateUpdated(
                                        function (Get $get, Set $set, $state) {
                                            ($state != '' ? self::updateSubtotal($get, $set) : 0);
                                        }
                                    )
                                    ->live(),

                                Hidden::make('sparepart_id'),
                                Hidden::make('satuan_id'),
                                Hidden::make('harga_unit'),
                                Hidden::make('pajak'),

                                TextInput::make('jumlah_unit')
                                    ->required()
                                    ->default(1)
                                    ->numeric()
                                    ->live(debounce: 500)
                                    ->afterStateUpdated(
                                        function (Get $get, Set $set, $state){
                                            ($state !='' ? self::updateSubtotal($get, $set) : 0);

                                        }
                                    )
                                    // ->default(0)
                                    ->disabled(fn (Get $get) => !$get('sparepart_satuan_id')),
                                TextInput::make('harga_subtotal')
                                ->required()
                                ->live()
                                ->label('Harga subtotal')
                                ->default(0)
                                ->prefix('Rp ')
                                ->numeric()
                                ->readOnly(),
                                TextInput::make('discount')
                                ->numeric()
                                ->live(debounce: 500)
                                ->afterStateUpdated(
                                    function (Get $get, Set $set, $state) {
                                        ($state != '' ? self::updateSubtotal($get, $set) : 0);
                                    }
                                )
                                ->default(0)
                            ])
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                self::updatedTotals($get, $set);
                            }),

                        Grid::make()
                            ->columns('3')
                            ->schema([
                                TextInput::make('sub_total')
                                    ->default(0)
                                    ->prefix('Rp ')
                                    ->numeric()
                                    ->readOnly(),
                                TextInput::make('discount_total')
                                    ->default(0)
                                    ->prefix('Rp ')
                                    ->numeric()
                                    ->live(debounce:500)
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        self::updatedTotals($get, $set);
                                    })
                                    ->readOnly(fn($state) => $state!=0),
                                TextInput::make('total')
                                    ->default(0)
                                    ->prefix('Rp ')
                                    ->numeric()
                                    ->readOnly(),
                                Hidden::make('pajak_total')
                                    ->default(0)
                                    // ->numeric()
                                    // ->readOnly()
                            ])
                    ]),
                    Wizard\Step::make('Data Pelanggan')
                        ->schema([
                            TextInput::make('customer_name')
                            ->required()
                            ->label('Nama pelanggan'),
                            TextInput::make('customer_nomor_telepon')
                            ->default('+62')
                            ->required()
                            ->helperText('tambahkan kode negara (+62)')
                            ->label('Nomor Telepon Pelanggan')
                        ]),
                    Wizard\Step::make('Pembayaran')
                    ->schema([
                        Grid::make(2)
                        ->schema([
                            TextInput::make('total')
                                ->default(0)
                                ->prefix('Rp ')
                                ->label('Jumlah yang harus dibayar')
                                ->numeric()
                                ->readOnly(),
                            TextInput::make('payment_change')
                                ->default(0)
                                ->prefix('Rp ')
                                ->label('Kembalian')
                                ->numeric()
                                ->readOnly()
                        ]),
                        Repeater::make('sparepartDSalePayment')
                        ->label('Metode Pembayaran')
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
                                            $set('total_payable', $livewire->data['total']);
                                        }else{
                                            $total_payable = $livewire->data['total'] - array_sum(array_column($livewire->data['sparepartDSalePayment'], 'jumlah_bayar'));
                                            $set('jumlah_bayar', $total_payable);
                                            $set('total_payable', $total_payable);
                                            
                                            // dd($livewire->data['total'], array_sum(array_column($livewire->data['sparepartDSalePayment'], 'jumlah_bayar')));


                                        }
                                        
                                    }),
                                    TextInput::make('jumlah_bayar')
                                    ->required(),
                                    FileUpload::make('photo')
                                        ->label('Bukti pembayaran')
                                        ->image()
                                        ->resize(50),
                                    Hidden::make('total_payable'),
                                    Hidden::make('account_name'),
                                    Hidden::make('account_kode'),
                                ])
                        ])
                        ->live(debounce:500)
                        ->afterStateUpdated(function (Set $set, Get $get) {
                            self::updatePaymentChange($get, $set);
                        }),
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
                ->sortable()
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
            ->defaultSort('tanggal_transaksi', 'desc')
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

                        if ($status == 'rejected') {
                            // Hapus file jika ada
                            $filePath = storage_path('app/invoices/sales/' . $record->invoice_file);
                            if ($record->invoice_file != null && file_exists($filePath)) {
                                unlink($filePath);
                            }

                            $record->update([
                                'invoice_file' => null,
                            ]);
                        }


                        self::InsertJurnal($record, $status);
                        $record->is_approve = $status;
                        $record->approved_by = FacadesAuth::id();
                        $record->approved_at = NOW();
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
                    Tables\Actions\Action::make('kirimInvoice')
                    ->label('Kirim Invoice')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Kirim invoice ke WhatsApp?')
                    ->modalDescription('Invoice akan dikirim ke nomor pelanggan.')
                    ->openUrlInNewTab()
                    ->action(function ($record) {
                        // Cek apakah file sudah ada
                        if (!$record->invoice_file) {
                            // Generate PDF
                            $pdf = new \Mpdf\Mpdf([
                                'tempDir' => storage_path('app/mpdf-temp')
                            ]);

                            $html = view('invoices.template', ['transaction' => $record, 'transaction_d' => SparepartDSale::where(['sparepart_sale_id' => $record->id])->get()])->render();
                            $filename = 'invoice-' . \Illuminate\Support\Str::random(5) . $record->id .\Illuminate\Support\Str::random(5) . '.pdf';
                            $path = storage_path("app/invoices/sales/{$filename}");
                            $pdf->WriteHTML($html);
                            $pdf->Output($path, \Mpdf\Output\Destination::FILE);

                            // Simpan nama file di database
                            $record->update(['invoice_file' => $filename]);
                        }

                        // Gunakan file yang sudah ada
                        $downloadUrl = route('sales.invoice.download', ['filename' => $record->invoice_file]);
                        $message = "Halo! Terimakasih sudah berbelanja Sparepart di Polije Autohub. Berikut adalah invoice belanja anda: \n{$downloadUrl}";
                        $waLink = 'https://wa.me/' . $record->customer_nomor_telepon . '?text=' . urlencode($message);

                        return redirect($waLink);
                    })
                    ->visible(fn ($record) => !empty($record->customer_nomor_telepon && $record->is_approve == 'approved')),
                    Tables\Actions\Action::make('preview_invoice')
                    ->label('Lihat Invoice')
                    ->url(fn ($record) => route('invoice.sales_preview', $record))
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-document-text')
                    ->visible(fn ($record) => !empty($record->customer_nomor_telepon && $record->is_approve == 'approved')),
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
