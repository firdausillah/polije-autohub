<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleResource\Pages;
use App\Filament\Resources\VehicleResource\RelationManagers;
use App\Helpers\CodeGenerator;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
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

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('registration_number')
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
                            } else{
                                return[
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Vehicle::withCount('serviceHistories'))
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('kode')
                    ->searchable(),
                TextColumn::make('nomor_telepon')
                    ->searchable(),
                TextColumn::make('service_histories_count')
                    ->label('Jumlah Kedatangan')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
            ])

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVehicles::route('/'),
            'create' => Pages\CreateVehicle::route('/create'),
            'edit' => Pages\EditVehicle::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
