<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceScheduleResource\Pages;
use App\Filament\Resources\ServiceScheduleResource\RelationManagers;
use App\Filament\Resources\ServiceScheduleResource\RelationManagers\ServiceDChecklistRelationManager;
use App\Filament\Resources\ServiceScheduleResource\RelationManagers\ServiceDServicesRelationManager;
use App\Filament\Resources\ServiceScheduleResource\RelationManagers\ServiceDSparepartRelationManager;
use App\Helpers\CodeGenerator;
use App\Models\ServiceSchedule;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServiceScheduleResource extends Resource
{
    protected static ?string $model = ServiceSchedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('vehicle_id')
                ->relationship('vehicle', 'registration_number')
                ->searchable()
                ->label('Kendaraan')
                ->createOptionForm([
                    Grid::make()
                    ->columns(2)
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
                ]),
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
                ->relationship('mekanik', 'user_name')
                ->live()
                ->afterStateUpdated(
                    function(Set $set, $state){
                        $mekanik = User::find($state)->name;
                        $set('mekanik_name', $mekanik);
                    }
                ),
                TextInput::make('total_estimasi_waktu')
                ->suffix(' Menit')
                ->readOnly(),
                Grid::make(3)
                ->schema([
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('vehicle.registration_number')
                ->label('Nopol'),
                TextColumn::make('kode'),
                TextColumn::make('customer_name')
                ->label('Customer'),
                TextColumn::make('mekanik_name')
                ->label('Mekanik'),
                TextColumn::make('kepala_mekanik_name')
                ->label('Kepala Mekanik'),
                TextColumn::make('service_status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'Daftar' => 'info',
                    'Proses' => 'warning',
                    'Cancel' => 'danger',
                    'Selesai' => 'success',
                })
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
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
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServiceSchedules::route('/'),
            'create' => Pages\CreateServiceSchedule::route('/create'),
            'edit' => Pages\EditServiceSchedule::route('/{record}/edit'),
        ];
    }
}
