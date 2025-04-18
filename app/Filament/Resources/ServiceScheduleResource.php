<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceScheduleResource\Pages;
use App\Filament\Resources\ServiceScheduleResource\RelationManagers;
use App\Filament\Resources\ServiceScheduleResource\RelationManagers\ServiceDChecklistRelationManager;
use App\Filament\Resources\ServiceScheduleResource\RelationManagers\ServiceDPaymentRelationManager;
use App\Filament\Resources\ServiceScheduleResource\RelationManagers\ServiceDServicesRelationManager;
use App\Filament\Resources\ServiceScheduleResource\RelationManagers\ServiceDSparepartRelationManager;
use App\Helpers\CodeGenerator;
use App\Models\Account;
use App\Models\Checklist;
use App\Models\Inventory;
use App\Models\Jurnal;
use App\Models\Modal;
use App\Models\ServiceDChecklist;
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
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
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
        if ($status == 'approved') {
            // Jurnal Begin

            // Jurnal Penjualan Servis + Sparepart dalam Satu Transaksi

            // D. Kas/Bank xxx  
            //     C. Pendapatan Servis xxx  
            //     C. Pendapatan Sparepart xxx  
            //     C. Utang PPN Keluaran

            // Jurnal HPP (Harga Pokok Pelayanan Service)

            // D. Harga Pokok Penjualan xxx  
            //     C. Persediaan Sparepart xxx  


            // debit
            //Kas / Bank
            $payment = ServiceDPayment::where('service_schedule_id', $record->id)->get();
            if ($record->total != $payment->sum('jumlah_bayar')) {
                return ['title' => 'Approval Gagal', 'body' => 'total harus sama dengan total jumlah di detail', 'status' => 'warning'];
            }
            foreach ($payment as $key => $val) {
                Jurnal::create([
                    'transaksi_h_id'    => $val->service_schedule_id,
                    'transaksi_d_id'    => $val->id,
                    'account_id'    => $val->account_id, //

                    'keterangan'    => $val->keterangan,
                    'kode'  => $record->kode,
                    'tanggal_transaksi' => date_format(NOW(), 'Y-m-d H:i:s'),

                    'relation_name' => $record->customer_name,
                    'relation_nomor_telepon'    => $record->nomor_telepon,

                    'account_name'  => $val->account_name, //
                    'account_kode'  => $val->account_kode, //
                    'transaction_type'  => 'Pelayanan Service',

                    'debit' => ($val->jumlah_bayar==null?0:$val->jumlah_bayar),
                    'kredit'    => 0,
                ]);
            }

            // kredit
            $account_kredit_service = Account::find(6); //Pendapatan Jasa Service
            Jurnal::create([
                'transaksi_h_id'    => $record->id,
                'transaksi_d_id'    => $record->id,
                'account_id'    => $account_kredit_service->id,

                'keterangan'    => $record->keterangan,
                'kode'  => $record->kode,
                'tanggal_transaksi' => date_format(NOW(), 'Y-m-d H:i:s'),

                'relation_name' => $record->customer_name,
                'relation_nomor_telepon'    => $record->nomor_telepon,

                'account_name'  => $account_kredit_service->name,
                'account_kode'  => $account_kredit_service->kode,
                'transaction_type'  => 'Pelayanan Service',

                'debit' => 0,
                'kredit'    => ($record->service_total==null?0:$record->service_total),
            ]);
            
            $account_kredit_sparepart = Account::find(7); //Pendapatan Penjualan Sparepart service
            Jurnal::create([
                'transaksi_h_id'    => $record->id,
                'transaksi_d_id'    => $record->id,
                'account_id'    => $account_kredit_sparepart->id,

                'keterangan'    => $record->keterangan,
                'kode'  => $record->kode,
                'tanggal_transaksi' => date_format(NOW(), 'Y-m-d H:i:s'),

                'relation_name' => $record->customer_name,
                'relation_nomor_telepon'    => $record->nomor_telepon,

                'account_name'  => $account_kredit_sparepart->name,
                'account_kode'  => $account_kredit_sparepart->kode,
                'transaction_type'  => 'Pelayanan Service',

                'debit' => 0,
                'kredit'    => ($record->sparepart_total==null?0:$record->sparepart_total),
            ]);

            if ($record->pajak_total) {
                $account_kredit_pajak = Account::find(9); //Utang PPN Keluaran
                Jurnal::create([
                    'transaksi_h_id'    => $record->id,
                    'transaksi_d_id'    => $record->id,
                    'account_id'    => $account_kredit_pajak->id,

                    'keterangan'    => $record->keterangan,
                    'kode'  => $record->kode,
                    'tanggal_transaksi' => date_format(NOW(), 'Y-m-d H:i:s'),

                    'relation_name' => $record->customer_name,
                    'relation_nomor_telepon'    => $record->nomor_telepon,

                    'account_name'  => $account_kredit_pajak->name,
                    'account_kode'  => $account_kredit_pajak->kode,
                    'transaction_type'  => 'Pelayanan Service',

                    'debit' => 0,
                    'kredit'    => ($record->pajak_total==null?0:$record->pajak_total),
                ]);
            }


            // hpp begin
            $sparepart = ServiceDSparepart::where('service_schedule_id', $record->id)->get();
            $account_hpp = Account::find(10); //Harga Pokok Penjualan
            $account_persediaan = Account::find(3); //Persediaan Sparepart
            foreach ($sparepart as $key => $val) {
                $harga_modal = Modal::where('sparepart_id', $val->sparepart_id)->orderBy('id', 'desc')->first()->harga_modal;

                Jurnal::create([
                    'transaksi_h_id'    => $val->service_schedule_id,
                    'transaksi_d_id'    => $val->sparepart_id,
                    'account_id'    => $account_hpp->id, //

                    'keterangan'    => $val->sparepart_kode . ' - ' . $val->sparepart_name,
                    'kode'  => $record->kode,
                    'tanggal_transaksi' => date_format(NOW(), 'Y-m-d H:i:s'),

                    'relation_name' => $record->customer_name,
                    'relation_nomor_telepon'    => $record->nomor_telepon,

                    'account_name'  => $account_hpp->name, //
                    'account_kode'  => $account_hpp->kode, //
                    'transaction_type'  => 'HPP Penjualan',

                    'debit' => ($harga_modal==null?0:$harga_modal),
                    'kredit'    => 0
                ]);

                Jurnal::create([
                    'transaksi_h_id'    => $val->service_schedule_id,
                    'transaksi_d_id'    => $val->sparepart_id,
                    'account_id'    => $account_persediaan->id, //

                    'keterangan'    => $val->sparepart_kode . ' - ' . $val->sparepart_name,
                    'kode'  => $record->kode,
                    'tanggal_transaksi' => date_format(NOW(), 'Y-m-d H:i:s'),

                    'relation_name' => $record->customer_name,
                    'relation_nomor_telepon'    => $record->nomor_telepon,

                    'account_name'  => $account_persediaan->name, //
                    'account_kode'  => $account_persediaan->kode, //
                    'transaction_type'  => 'HPP Penjualan',

                    'debit' => 0,
                    'kredit'    => ($harga_modal==null?0:$harga_modal)
                ]);
            }
            // hpp end
            // jurnal end

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
            return ['title' => 'Approval Berhasil', 'body' => 'Pelayanan Service Berhasil Diapprove', 'status' => 'success'];
        } else {
            Inventory::where(['transaksi_h_id' => $record->id, 'movement_type' => 'OUT-SAL'])->delete();
            Jurnal::where(['transaksi_h_id' => $record->id, 'transaction_type' => 'Pelayanan Service'])->delete();
            Jurnal::where(['transaksi_h_id' => $record->id, 'transaction_type' => 'HPP Penjualan'])->delete();

            return ['title' => 'Reject Berhasil', 'body' => 'Pelayanan Service Berhasil Direject', 'status' => 'success'];
        }
    }
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([Grid::make(['sm' => 2])
                ->schema([
                    Select::make('vehicle_id')
                        ->relationship('vehicle', 'registration_number')
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
                                    TextInput::make('nomor_rangka'),
                                    TextInput::make('nomor_mesin'),
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
                                $riwayat_service = ServiceSchedule::where('vehicle_id', $state)->first();

                                if ($riwayat_service !== null) {
                                    $set('customer_name', $riwayat_service->customer_name);
                                    $set('nomor_telepon', $riwayat_service->nomor_telepon);
                                } else {
                                    $set('customer_name', '');
                                    $set('nomor_telepon', '');
                                }
                            }
                        ),
                    TextInput::make('kode')
                        ->default(fn () => CodeGenerator::generateTransactionCode('SSJ', 'service_schedules', 'kode'))
                        ->readOnly(),
                    TextInput::make('customer_name')
                        ->label('Nama Customer'),
                    TextInput::make('nomor_telepon')
                        ->numeric(),
                    TextInput::make('km_datang')
                        ->required()
                        ->label('KM datang')
                        ->numeric()
                        ->suffix('KM'),
                    Textarea::make('keluhan')
                        ->required(),
                    Select::make('mekanik_id')
                        ->label('Mekanik')
                        ->required()
                        ->relationship('mekanik', 'user_name')
                        ->live()
                        ->afterStateUpdated(
                            function (Set $set, $state) {
                                $mekanik = User::find($state)->name;
                                $set('mekanik_name', $mekanik);
                            }
                        ),
                    Select::make('kepala_mekanik_id')
                        ->label('Kepala Mekanik')
                        ->relationship('kepalaMekanik', 'user_name')
                        ->disabled()
                        ->live()
                        ->afterStateUpdated(
                            function (Set $set, $state) {
                                $mekanik = User::find($state)->name;
                                $set('mekanik_name', $mekanik);
                            }
                        ),
                ]),
            Grid::make(['sm' => 4])
                ->schema([
                    TextInput::make('total_estimasi_waktu')
                        ->suffix(' Menit')
                        ->readOnly(),
                    TextInput::make('service_total')
                        ->prefix('Rp')
                        ->readOnly(),
                    TextInput::make('sparepart_total')
                        ->prefix('Rp')
                        ->readOnly(),
                    TextInput::make('total')
                        ->prefix('Rp')
                        ->readOnly(),
                ]),

            Hidden::make('mekanik_name'),
            ])
            ->disabled(auth()->user()->hasRole(['super_admin', 'Manager']) ? false:true)
            ->columns([
                'sm' => 2,
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                if (auth()->user()->hasRole('Mekanik')) {
                    $query->where('mekanik_id', auth()->id());
                }
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
                TextColumn::make('mekanik_name')
                ->label('Mekanik'),
                TextColumn::make('kepala_mekanik_name')
                ->label('Kepala Mekanik'),
                IconColumn::make('checklist_status')
                ->label('Checklist')
                ->icon(fn ($record) => $record->checklist_status ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                ->trueColor('info')
                ->falseColor('warning'),
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
                            $kepala_mekanik_name = User::find(Auth::id())->name;
                            
                            $record->service_status = 'Proses Pengerjaan'; //daftar, proses pengerjaan, batal, selesai
                            $record->kepala_mekanik_name = $kepala_mekanik_name;
                            $record->kepala_mekanik_id = Auth::id();
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
                            if ($record->kepala_mekanik_id == null && auth()->user()->hasRole('Kepala Mekanik')) {
                                return true;
                            }
                        }),
                    ViewAction::make(),
                    EditAction::make()
                        ->visible(function (ServiceSchedule $record){
                        if (auth()->user()->hasRole(['super_admin', 'Manager']) OR ($record->kepala_mekanik_id != null && auth()->user()->hasRole(['Kepala Mekanik', 'Mekanik']))) {
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
                                $record->service_status = 'Menunggu Pembayaran';
                                $record->save();
                                Notification::make()
                                    ->title("Service Selesai, sedang Menunggu Pembayaran")
                                    ->success()
                                    ->body("Service sedang Menunggu Pembayaran.")
                                    ->send();
                            })
                        ->visible(function (ServiceSchedule $record){
                            if ($record->service_status == "Proses Pengerjaan" && auth()->user()->hasRole('Kepala Mekanik')) {
                                return true;
                            }
                    }),
                    Action::make('Approve Pembayaran')
                    ->action(function (ServiceSchedule $record) {
                        if (empty($record->kode)) {
                            $record->kode = CodeGenerator::generateTransactionCode('SCD', 'service_schedules', 'kode');
                        }
                        
                        $isApproving = in_array($record->is_approve, ['pending', 'rejected']);
                        $status = $isApproving ? 'approved' : 'rejected';

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
                        if (($record->service_status == "Menunggu Pembayaran" || $record->service_status == "Selesai") && auth()->user()->hasRole(['admin', 'super_admin'])) {
                            return true;
                        }
                    })
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
