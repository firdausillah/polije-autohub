<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceScheduleResource\Pages;
use App\Filament\Resources\ServiceScheduleResource\RelationManagers;
use App\Filament\Resources\ServiceScheduleResource\RelationManagers\ServiceDChecklistRelationManager;
use App\Filament\Resources\ServiceScheduleResource\RelationManagers\ServiceDPaymentRelationManager;
use App\Filament\Resources\ServiceScheduleResource\RelationManagers\ServiceDServicesRelationManager;
use App\Filament\Resources\ServiceScheduleResource\RelationManagers\ServiceDSparepartRelationManager;
use App\Helpers\CodeGenerator;
use App\Helpers\updateServiceTotal;
use App\Models\Account;
use App\Models\Checklist;
use App\Models\Inventory;
use App\Models\Jurnal;
use App\Models\Modal;
use App\Models\PayrollJurnal;
use App\Models\ServiceDChecklist;
use App\Models\ServiceDMekanik;
use App\Models\ServiceDPayment;
use App\Models\ServiceDServices;
use App\Models\ServiceDSparepart;
use App\Models\ServiceSchedule;
use App\Models\Sparepart;
use App\Models\SparepartSatuans;
use App\Models\User;
use App\Models\UserRole;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Guava\FilamentModalRelationManagers\Actions\Table\RelationManagerAction as TableRelationManagerAction;
use Guava\FilamentModalRelationManagers\Concerns\CanBeEmbeddedInModals;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ServiceScheduleResource extends Resource
{    
    protected static ?string $model = ServiceSchedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    public static function getModelLabel(): string
    {
        return 'Pelayanan Service';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Pelayanan Service';
    }

    public static function InsertJurnal($record, $status)
    {
        try {
            if ($status == 'approved') {
                // Validasi jumlah bayar
                $payment = ServiceDPayment::where('service_schedule_id', $record->id)->get();
                if ($record->total > $payment->sum('jumlah_bayar')) {
                    return [
                        'title' => 'Approval Gagal',
                        'body' => 'jumlah bayar tidak boleh lebih kecil dari total yang harus dibayarkan di detail payment',
                        'status' => 'warning'
                    ];
                }

                // Jurnal Penjualan Servis + Sparepart dalam Satu Transaksi

                // D. Kas/Bank xxx  
                //     C. Pendapatan Servis xxx  
                //     C. Pendapatan Sparepart xxx  
                //     C. Utang PPN Keluaran

                // Jurnal HPP (Harga Pokok Pelayanan Service)

                // D. Harga Pokok Penjualan xxx  
                //     C. Persediaan Sparepart xxx  

                // Jurnal debit: Kas / Bank
                foreach ($payment as $val) {
                    Jurnal::create([
                        'transaksi_h_id' => $val->service_schedule_id,
                        'transaksi_d_id' => $val->id,
                        'account_id' => $val->account_id,
                        'keterangan' => $val->keterangan,
                        'kode' => $record->kode,
                        'tanggal_transaksi' => now(),
                        'relation_name' => $record->customer_name,
                        'relation_nomor_telepon' => $record->nomor_telepon,
                        'account_name' => $val->account_name,
                        'account_kode' => $val->account_kode,
                        'transaction_type' => 'Pelayanan Service',
                        'debit' => min($val->jumlah_bayar, $val->total_payable),
                        'kredit' => 0,
                    ]);
                }

                // Diskon
                if ($record->discount_total) {
                    $account_debit_discount = Account::findOrFail(25); //Diskon Penjualan
                    Jurnal::create([
                        'transaksi_h_id' => $record->id,
                        'transaksi_d_id' => $record->id,
                        'account_id' => $account_debit_discount->id,
                        'keterangan' => $record->keterangan,
                        'kode' => $record->kode,
                        'tanggal_transaksi' => now(),
                        'relation_name' => $record->customer_name,
                        'relation_nomor_telepon' => $record->nomor_telepon,
                        'account_name' => $account_debit_discount->name,
                        'account_kode' => $account_debit_discount->kode,
                        'transaction_type' => 'Pelayanan Service',
                        'debit' => $record->discount_total,
                        'kredit' => 0,
                    ]);
                }

                // Kredit: Pendapatan Service, Sparepart, dan PPN
                $account_kredit_service = Account::findOrFail(6);
                $account_kredit_sparepart = Account::findOrFail(7);
                $account_kredit_pajak = Account::find(9); // optional

                Jurnal::create([
                    'transaksi_h_id' => $record->id,
                    'transaksi_d_id' => $record->id,
                    'account_id' => $account_kredit_service->id,
                    'keterangan' => $record->keterangan,
                    'kode' => $record->kode,
                    'tanggal_transaksi' => now(),
                    'relation_name' => $record->customer_name,
                    'relation_nomor_telepon' => $record->nomor_telepon,
                    'account_name' => $account_kredit_service->name,
                    'account_kode' => $account_kredit_service->kode,
                    'transaction_type' => 'Pelayanan Service',
                    'debit' => 0,
                    'kredit' => max(0, $record->service_total - $record->discount_service_total),
                ]);

                Jurnal::create([
                    'transaksi_h_id' => $record->id,
                    'transaksi_d_id' => $record->id,
                    'account_id' => $account_kredit_sparepart->id,
                    'keterangan' => $record->keterangan,
                    'kode' => $record->kode,
                    'tanggal_transaksi' => now(),
                    'relation_name' => $record->customer_name,
                    'relation_nomor_telepon' => $record->nomor_telepon,
                    'account_name' => $account_kredit_sparepart->name,
                    'account_kode' => $account_kredit_sparepart->kode,
                    'transaction_type' => 'Pelayanan Service',
                    'debit' => 0,
                    'kredit' => max(0, $record->sparepart_total - $record->discount_sparepart_total),
                ]);

                if ($record->pajak_total && $account_kredit_pajak) {
                    Jurnal::create([
                        'transaksi_h_id' => $record->id,
                        'transaksi_d_id' => $record->id,
                        'account_id' => $account_kredit_pajak->id,
                        'keterangan' => $record->keterangan,
                        'kode' => $record->kode,
                        'tanggal_transaksi' => now(),
                        'relation_name' => $record->customer_name,
                        'relation_nomor_telepon' => $record->nomor_telepon,
                        'account_name' => $account_kredit_pajak->name,
                        'account_kode' => $account_kredit_pajak->kode,
                        'transaction_type' => 'Pelayanan Service',
                        'debit' => 0,
                        'kredit' => $record->pajak_total,
                    ]);
                }

                // Payroll
                $jumlah_unit_terjual = ServiceDSparepart::where('service_schedule_id', $record->id)->sum('jumlah_unit');
                $jumlah_service_terlayani   = ServiceDServices::where('service_schedule_id', $record->id)->sum('jumlah');

                PayrollJurnal::create([
                    'transaksi_h_id' => $record->id,
                    'user_id' => Auth::id(),
                    'name' => User::find(Auth::id())->name,
                    'keterangan' => 'Admin',
                    'transaction_type' => 'Pelayanan Service',
                    'jumlah_sparepart' => $jumlah_unit_terjual,
                    'nominal' => max(0, $record->sparepart_total - $record->discount_service_total),
                ]);

                PayrollJurnal::create([
                    'transaksi_h_id' => $record->id,
                    'user_id' => $record->kepala_unit_id,
                    'name' => User::find($record->kepala_unit_id)->name,
                    'keterangan' => 'Kepala Unit',
                    'transaction_type' => 'Pelayanan Service',
                    'jumlah_service' => $jumlah_service_terlayani,
                    'nominal' => max(0, $record->service_total - $record->discount_service_total),
                ]);

                $mekanik = ServiceDMekanik::where('service_schedule_id', $record->id)->get();
                foreach ($mekanik as $val) {
                    $nominal = ($val->mekanik_percentage / 100) * max(0, $record->service_total - $record->discount_service_total);
                    PayrollJurnal::create([
                        'transaksi_h_id' => $record->id,
                        'user_id' => $val->mekanik_id,
                        'name' => User::find($val->mekanik_id)->name,
                        'keterangan' => 'Mekanik',
                        'transaction_type' => 'Pelayanan Service',
                        'jumlah_service' => $jumlah_service_terlayani,
                        'nominal' => $nominal,
                    ]);
                }

                // HPP
                $sparepart = ServiceDSparepart::where('service_schedule_id', $record->id)->get();
                $account_hpp = Account::findOrFail(10);
                $account_persediaan = Account::findOrFail(3);

                foreach ($sparepart as $val) {
                    $harga_modal = Modal::where('sparepart_id', $val->sparepart_id)->orderBy('id', 'desc')->first()->harga_modal ?? 0;

                    Jurnal::create([
                        'transaksi_h_id' => $val->service_schedule_id,
                        'transaksi_d_id' => $val->sparepart_id,
                        'account_id' => $account_hpp->id,
                        'keterangan' => $val->sparepart_kode . ' - ' . $val->sparepart_name,
                        'kode' => $record->kode,
                        'tanggal_transaksi' => now(),
                        'relation_name' => $record->customer_name,
                        'relation_nomor_telepon' => $record->nomor_telepon,
                        'account_name' => $account_hpp->name,
                        'account_kode' => $account_hpp->kode,
                        'transaction_type' => 'HPP Penjualan',
                        'debit' => $harga_modal * $val->jumlah_terkecil,
                        'kredit' => 0,
                    ]);

                    Jurnal::create([
                        'transaksi_h_id' => $val->service_schedule_id,
                        'transaksi_d_id' => $val->sparepart_id,
                        'account_id' => $account_persediaan->id,
                        'keterangan' => $val->sparepart_kode . ' - ' . $val->sparepart_name,
                        'kode' => $record->kode,
                        'tanggal_transaksi' => now(),
                        'relation_name' => $record->customer_name,
                        'relation_nomor_telepon' => $record->nomor_telepon,
                        'account_name' => $account_persediaan->name,
                        'account_kode' => $account_persediaan->kode,
                        'transaction_type' => 'HPP Penjualan',
                        'debit' => 0,
                        'kredit' => $harga_modal * $val->jumlah_terkecil,
                    ]);
                }


                // inventory begin
                $serviceDSpareparts = ServiceDSparepart::where('service_schedule_id', $record->id)->get();
                foreach ($serviceDSpareparts as $val) {
                    Inventory::create([
                        'transaksi_h_id' => $record->id,
                        'transaksi_d_id' => $val->id,
                        'sparepart_id' => $val->sparepart_id,
                        'satuan_id' => $val->satuan_id,

                        'name' => '',
                        'kode' => $record->kode,
                        'keterangan' => $record->keterangan,
                        'tanggal_transaksi' => date_format(NOW(), 'Y-m-d H:i:s'),
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
                        'relation_nomor_telepon' => $record->nomor_telepon
                    ]);
                }
            // inventory end
                
                return [
                    'title' => 'Berhasil',
                    'body' => 'Jurnal berhasil dibuat.',
                    'status' => 'success'
                ];
            }else{
                // Cancel transaksi: rollback jurnal & inventory
                Inventory::where([
                    'kode' => $record->kode,
                    'movement_type' => 'OUT-SAL'
                ])->delete();

                Jurnal::where([
                    'kode' => $record->kode,
                    'transaction_type' => 'Pelayanan Service'
                ])->delete();

                Jurnal::where([
                    'kode' => $record->kode,
                    'transaction_type' => 'HPP Penjualan'
                ])->delete();

                PayrollJurnal::where([
                    'transaksi_h_id' => $record->id,
                    'transaction_type' => 'Pelayanan Service'
                ])->delete();

                return [
                    'title' => 'Berhasil',
                    'body' => 'Jurnal berhasil dibatalkan.',
                    'status' => 'success'
                ];
            }

        } catch (\Exception $e) {
            Log::error('InsertJurnal Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return [
                'title' => 'Error',
                'body' => 'Terjadi kesalahan saat membuat jurnal: ' . $e->getMessage(),
                'status' => 'danger'
            ];
        }
    }

    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                ->tabs([
                    Tabs\Tab::make('Pelanggan & Kendaraan')
                        ->schema([
                            Grid::make(['sm' => 2])
                            ->schema([
                                Select::make('vehicle_id')
                                    ->relationship('vehicle', 'registration_number')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->registration_number} - ({$record->nomor_rangka})")
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->label('Kendaraan')
                                    ->createOptionForm([
                                        Grid::make([
                                            'sm' => 2,
                                        ])
                                            ->schema([
                                                TextInput::make('registration_number')
                                                    ->label('Nomor Polisi')
                                                    ->required(),
                                                TextInput::make('kode')
                                                    ->default(fn () => CodeGenerator::generateSimpleCode('V', 'vehicles', 'kode'))
                                                    ->readOnly(),
                                                TextInput::make('nomor_rangka'),
                                                TextInput::make('nomor_mesin'),
                                                Select::make('category')
                                                    ->required()
                                                    ->options([
                                                        "Sepeda Motor" => "Sepeda Motor",
                                                        "Mobil" => "Mobil",
                                                        "Lainya" => "Lainya"
                                                    ])
                                                    ->live()
                                                    ->afterStateUpdated(
                                                        function (Set $set, $state) {
                                                            $set('brand', null);
                                                        }
                                                    )
                                                    ->label('Jenis'),
                                                Select::make('brand')
                                                    ->required()
                                                    ->options(
                                                        function (Get $get) {
                                                            if ($get('category') == 'Sepeda Motor') {
                                                                return [
                                                                    "Honda" => "Honda",
                                                                    "Yamaha" => "Yamaha",
                                                                    "Suzuki" => "Suzuki",
                                                                    "Kawasaki" => "Kawasaki",
                                                                    "Viar" => "Viar",
                                                                    "Gesits" => "Gesits",
                                                                    "Kymco" => "Kymco",
                                                                    "Benelli" => "Benelli",
                                                                    "Royal Enfield" => "Royal Enfield",
                                                                    "Vespa" => "Vespa",
                                                                    "Minerva" => "Minerva",
                                                                    "Lainya" => "Lainya",
                                                                ];
                                                            } elseif ($get('category') == 'Mobil') {
                                                                return [
                                                                    "Toyota" => "Toyota",
                                                                    "Daihatsu" => "Daihatsu",
                                                                    "Honda" => "Honda",
                                                                    "Mitsubishi" => "Mitsubishi",
                                                                    "Suzuki" => "Suzuki",
                                                                    "Nissan" => "Nissan",
                                                                    "Hyundai" => "Hyundai",
                                                                    "Kia" => "Kia",
                                                                    "Mazda" => "Mazda",
                                                                    "Isuzu" => "Isuzu",
                                                                    "Wuling" => "Wuling",
                                                                    "DFSK" => "DFSK (Dongfeng Sokon)",
                                                                    "Subaru" => "Subaru",
                                                                    "Chery" => "Chery",
                                                                    "Esemka" => "Esemka",
                                                                    "Timor" => "Timor",
                                                                    "AMMDes" => "AMMDes (Alat Mekanis Multiguna Pedesaan)",
                                                                    "Tawon" => "Tawon",
                                                                    "GEA" => "GEA (Gulirkan Energi Alternatif)",
                                                                    "Lainya" => "Lainya",
                                                                ];
                                                            } else {
                                                                return [
                                                                    "Lainya" => "Lainya"
                                                                ];
                                                            }
                                                        }
                                                    )
                                                    ->live()
                                                    ->searchable()
                                                    ->disabled(fn (Get $get) => !$get('category')),

                                                TextInput::make('type'),
                                                TextInput::make('model'),
                                                TextInput::make('tahun_pembuatan')
                                                    ->numeric()
                                                    ->maxLength(4),
                                                TextInput::make('cc'),
                                                TextInput::make('warna'),
                                                Select::make('bahan_bakar')
                                                    ->options([
                                                        "Bensin" => "Bensin",
                                                        "Solar" => "Solar",
                                                        "Gas" => "Gas",
                                                        "Listrik" => "Listrik",
                                                    ]),
                                                Textarea::make('keterangan'),
                                            ])
                                    ])
                                    ->live()
                                    ->afterStateUpdated(
                                        function ($state, Set $set) {
                                            $riwayat_service = ServiceSchedule::where('vehicle_id', $state)->orderBy('id', 'desc')->first();
                                            // dd($riwayat_service);

                                            if ($riwayat_service !== null) {
                                                $set('customer_name', $riwayat_service->customer_name);
                                                $set('nomor_telepon', $riwayat_service->nomor_telepon);
                                                $set('is_customer_umum', $riwayat_service->is_customer_umum);
                                            } else {
                                                $set('customer_name', '');
                                                $set('nomor_telepon', '+62');
                                                $set('is_customer_umum', 1);
                                            }
                                        }
                                    ),
                                TextInput::make('kode')
                                    ->default(fn () => CodeGenerator::generateTransactionCode('SSJ', 'service_schedules', 'kode'))
                                    ->readOnly(),
                                TextInput::make('customer_name')
                                    ->label('Nama Customer'),
                                TextInput::make('nomor_telepon')
                                    ->default('+62')
                                    ->helperText('tambahkan kode negara (+62). contoh: +62856781234'),
                                Select::make('is_customer_umum')
                                    ->label('Jenis Customer')
                                    ->default(1)
                                    ->options([
                                        1 => 'Umum',
                                        0 => 'Mahasiswa / Karyawan / Ojol'
                                    ]),
                                TextInput::make('km_datang')
                                    ->required()
                                    ->label('KM datang')
                                    ->numeric()
                                    ->suffix('KM'),
                                Textarea::make('keluhan')
                                ->required(),
                                TextInput::make('total_estimasi_waktu')
                                    ->suffix(' Menit')
                                    ->numeric()
                                    ->readOnly(!auth()->user()->hasRole('Kepala Unit')),
                            ]),
                        ]),
                    Tabs\Tab::make('Biaya')
                        ->schema([
                            Grid::make(['sm'=>3])
                            ->schema([
                                TextInput::make('harga_subtotal')
                                    ->currencyMask(',')
                                    ->prefix('Rp')
                                    ->readOnly(),
                                TextInput::make('discount_total')
                                    ->currencyMask(',')
                                    ->prefix('Rp')
                                    ->readOnly(),
                                TextInput::make('total')
                                    ->currencyMask(',')
                                    ->prefix('Rp')
                                    ->readOnly(),
                            ]),
                            Fieldset::make('Rekap Biaya')
                            ->schema([
                                TextInput::make('service_total')
                                    ->currencyMask(',')
                                    ->prefix('Rp')
                                    ->readOnly(),
                                TextInput::make('discount_service_total')
                                    ->currencyMask(',')
                                    ->prefix('Rp')
                                    ->readOnly(),
                                TextInput::make('sparepart_total')
                                    ->currencyMask(',')
                                    ->prefix('Rp')
                                    ->readOnly(),
                                TextInput::make('discount_sparepart_total')
                                    ->currencyMask(',')
                                    ->prefix('Rp')
                                    ->readOnly(),
                            ])
                            ->columns(['sm' => 2]),
                        ]),
                    Tabs\Tab::make('Mekanik')
                        ->schema([
                            Select::make('kepala_unit_id')
                                ->label('Kepala Unit')
                                ->relationship('kepalaMekanik', 'user_name')
                                ->disabled(),
                            Repeater::make('serviceDMekanik')
                            ->label('Mekanik')
                            ->relationship()
                            ->schema([
                                Select::make('mekanik_id')
                                ->searchable()
                                ->required()
                                ->preload()
                                ->relationship('mekanik', 'user_name'),
                                TextInput::make('mekanik_percentage')
                                ->default(100)
                            ])
                            ->columns(2)
                            ->columnSpanFull()
                        ])
                        ->columnSpan('full')
                    
                ])
                ->columnSpan('full')

            // Hidden::make('mekanik_name'),
            ])
            
            ->disabled(auth()->user()->hasRole(['super_admin', 'Manager', 'Admin', 'Kepala Unit']) ? false:true)
            ->columns([
                'sm' => 2,
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                // if (auth()->user()->hasRole('Mekanik')) {
                //     $query->where(function ($query) {
                //         $query->where('mekanik1_id', auth()->id())
                //             ->orWhere('mekanik2_id', auth()->id())
                //             ->orWhere('mekanik3_id', auth()->id());
                //     });
                // }
                $query->where('created_at','like', (now()->toDateString().'%'));
            })
            ->poll('2s')
            ->columns([
                TextColumn::make('vehicle.registration_number')
                ->label('Nopol')
                ->searchable(),
                TextColumn::make('kode')
                ->searchable(),
                TextColumn::make('customer_name')
                ->searchable()
                ->label('Customer'),
                TextColumn::make('kepala_unit_name')
                ->label('Kepala Unit'),
                IconColumn::make('checklist_status')
                ->label('Checklist')
                ->icon(fn ($record) => $record->checklist_status ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                ->trueColor('success')
                ->falseColor('gray'),
                TextColumn::make('service_status')
                ->label('Status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'Daftar' => 'info',
                    'Proses Pengerjaan' => 'warning',
                    'Batal' => 'danger',
                    'Menunggu Pembayaran' => 'warning',
                    'Selesai' => 'success',
                })
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('Kerjakan')
                        ->action(function (ServiceSchedule $record) {
                            $kepala_unit_name = User::find(Auth::id())->name;
                            
                            $record->service_status = 'Proses Pengerjaan'; //daftar, proses pengerjaan, batal, selesai
                            $record->kepala_unit_name = $kepala_unit_name;
                            $record->kepala_unit_id = Auth::id();
                            $record->working_start = NOW();
                            $record->save();

                            Notification::make()
                                ->title("Service dalam Proses Pengerjaan")
                                ->success()
                                ->body("Service Sedang dikerjakan.")
                                ->send();
                        })
                        ->color('warning')
                        ->icon('heroicon-o-clipboard-document-check')
                        // ->requiresConfirmation()
                        ->visible(function (ServiceSchedule $record){
                            if ($record->kepala_unit_id == null && auth()->user()->hasRole('Kepala Unit')) {
                                return true;
                            }
                        }),
                    ViewAction::make(),
                    EditAction::make()
                        ->visible(function (ServiceSchedule $record){
                        if (auth()->user()->hasRole(['super_admin', 'Manager', 'Admin']) OR ($record->kepala_unit_id != null && auth()->user()->hasRole(['Kepala Unit', 'Mekanik']))) {
                            return true;
                        }else{
                            return false;
                        }}),
                    TableRelationManagerAction::make('serviceDChecklist-relation-manager')
                        ->icon('heroicon-o-check-circle')
                        ->label('Checklist')
                        ->relationManager(ServiceDChecklistRelationManager::make()),
                    Action::make('Approve Service')
                        ->color('success')
                        ->action(function(ServiceSchedule $record){

                                $isApproving = $record->service_status == 'Proses Pengerjaan';
                                $status = $isApproving ? 'Menunggu Pembayaran' : 'Proses Pengerjaan';
                                
                                $record->service_status = $status;
                                $record->working_end = $isApproving?NOW():NULL;
                                $record->save();
                                Notification::make()
                                    ->title($isApproving?"Service Selesai, sedang Menunggu Pembayaran":"Service Berhasil dibatalkan")
                                    ->success()
                                    ->body($isApproving ? "Service sedang Menunggu Pembayaran.":"Service Berhasil Dibatalkan")
                                    ->send();
                            })
                        ->color(fn (ServiceSchedule $record) => $record->service_status === 'Menunggu Pembayaran' ? 'danger' : 'info')
                        ->icon(fn (ServiceSchedule $record) => $record->service_status === 'Menunggu Pembayaran' ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                        ->requiresConfirmation()
                        ->label(fn (ServiceSchedule $record) => $record->service_status === 'Menunggu Pembayaran' ? 'Reject Service' : 'Approve Service')
                        ->visible(function (ServiceSchedule $record){
                            if ($record->service_status == "Proses Pengerjaan" OR $record->service_status == "Menunggu Pembayaran" && auth()->user()->hasRole('Kepala Unit')) {
                                return true;
                            }
                        }
                    ),
                    Action::make('Approve Pembayaran')
                    ->action(function (ServiceSchedule $record) {
                        if (empty($record->kode)) {
                            $record->kode = CodeGenerator::generateTransactionCode('SCD', 'service_schedules', 'kode');
                        }
                        
                        $isApproving = in_array($record->is_approve, ['pending', 'rejected']);
                        $status = $isApproving ? 'approved' : 'rejected';

                        if($status=='rejected'){
                            // Hapus file jika ada
                            $filePath = storage_path('app/invoices/service/'. $record->invoice_file);
                            if ($record->invoice_file != null && file_exists($filePath)) {
                                unlink($filePath);
                            }

                            $record->update([
                                'invoice_file' => null,
                            ]);
                        }

                        $message = self::InsertJurnal($record, $status);
                        if ($message['status'] == 'success') {
                            $record->service_status = $isApproving ? 'Selesai' : 'Menunggu Pembayaran';
                            $record->is_approve = $status;
                            $record->approved_by = Auth::id();
                            $record->approved_at = NOW();
                            $record->save();
                        }

                        Notification::make()
                            ->title($message['title'])
                            ->{($message['status'] == 'success' ? 'success' : 'warning')}()
                            ->body($message['body'])
                            ->send();
                    })
                    ->color(fn (ServiceSchedule $record) => $record->is_approve === 'approved' ? 'danger' : 'info')
                    ->icon(fn (ServiceSchedule $record) => $record->is_approve === 'approved' ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->label(fn (ServiceSchedule $record) => $record->is_approve === 'approved' ? 'Reject' : 'Approve')
                    ->visible(function (ServiceSchedule $record){
                        if (($record->service_status == "Menunggu Pembayaran" || $record->service_status == "Selesai") && auth()->user()->hasRole(['Admin', 'super_admin', 'Manager'])) {
                            return true;
                        }
                    }),
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

                            $html = view(
                                'invoices.service_template', 
                                [
                                    'transaction' => $record, 
                                    'transaction_d_service' => ServiceDServices::where(['service_schedule_id' => $record->id])->get(), 
                                    'transaction_d_sparepart' => ServiceDSparepart::where(['service_schedule_id' => $record->id])->get()
                                ])
                                ->render();
                            $filename = 'invoice-' . \Illuminate\Support\Str::random(5) . $record->id . \Illuminate\Support\Str::random(5) . '.pdf';
                            $path = storage_path("app/invoices/service/{$filename}");
                            $pdf->WriteHTML($html);
                            $pdf->Output($path, \Mpdf\Output\Destination::FILE);

                            // Simpan nama file di database
                            $record->update(['invoice_file' => $filename]);
                        }

                        // Gunakan file yang sudah ada
                        $downloadUrl = route('service.invoice.download', ['filename' => $record->invoice_file]);
                        $message = "Halo! Terimakasih sudah mempercayai Polije Autohub untuk meningkatkan kenyamanan berkendara anda. Berikut adalah invoice anda: \n{$downloadUrl}";
                        $waLink = 'https://wa.me/' . $record->nomor_telepon . '?text=' . urlencode($message);

                        return redirect($waLink);
                    })
                    ->visible(fn ($record) => !empty($record->nomor_telepon && $record->is_approve == 'approved')),
                    Tables\Actions\Action::make('preview_invoice')
                    ->label('Lihat Invoice')
                    ->url(fn ($record) => route('invoice.service_preview', $record))
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-document-text')
                    ->visible(fn ($record) => !empty($record->nomor_telepon && $record->is_approve == 'approved')),
                ]),
                // Tables\Actions\EditAction::make(),
            ])
            ->recordAction(Tables\Actions\ViewAction::class)
            ->recordUrl(null)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ServiceDChecklistRelationManager::class,
            ServiceDServicesRelationManager::class,
            ServiceDSparepartRelationManager::class,
            ServiceDPaymentRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServiceSchedules::route('/'),
            'create' => Pages\CreateServiceSchedule::route('/create'),
            'edit' => Pages\EditServiceSchedule::route('/{record}/edit'),
            // 'view' => Pages\ViewServiceSchedule::route('/{record}'),
        ];
    }
    
}
