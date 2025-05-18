<?php

namespace App\Filament\Resources\ServiceScheduleResource\RelationManagers;

use App\Models\Account;
use App\Models\ServiceDPayment;
use App\Policies\ServicePolicy;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Livewire\Component as Livewire;

class ServiceDPaymentRelationManager extends RelationManager
{
    protected static string $relationship = 'ServiceDPayment';

    protected static ?string $title = 'Pembayaran';
    protected static ?string $pluralLabel = 'Pembayaran';
    protected static ?string $modelLabel = 'Pembayaran';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->service_status === 'Menunggu Pembayaran' && auth()->user()->hasRole(['super_admin', 'Admin', 'Manager']);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('account_id')
                ->relationship('account', 'name')
                ->live()
                ->required()
                ->afterStateUpdated(
                    function(Set $set, $state, $record){
                        // dd($record);
                        $account = Account::find($state);
                        $set('account_name', $account->name);
                        $set('account_kode', $account->kode);
                    }
                ),
                Hidden::make('account_name'),
                Hidden::make('account_kode'),
                TextInput::make('jumlah_bayar')
                ->required()
                ->label('Jumlah Bayar')
                ->live(debounce: 500)
                ->afterStateUpdated(
                    function(Get $get, Set $set){
                        $payment_change = (float) $get('jumlah_bayar') - (float) $get('total_payable');
                        $set('payment_change', $payment_change>0?$payment_change:0);
                    }
                )
                ->default(
                    function (Livewire $livewire) {
                        $record = $livewire->ownerRecord;
                        $existing_payment = ServiceDPayment::where('service_schedule_id', $record->id)
                            ->pluck('jumlah_bayar')
                            ->toArray();

                        $total_payable = $record->total - array_sum($existing_payment);
                        // dd($record->total, array_sum($existing_payment), $existing_payment, $record->total - array_sum($existing_payment));
                        return $total_payable<0?0:$total_payable;
                    }
                ),
                TextInput::make('total_payable')
                ->required()
                ->default(
                function (Livewire $livewire) {
                    $record = $livewire->ownerRecord;
                    $existing_payment = ServiceDPayment::where('service_schedule_id', $record->id)
                        ->pluck('jumlah_bayar')
                        ->toArray();

                    $total_payable = $record->total - array_sum($existing_payment);
                    return $total_payable < 0 ? 0 : $total_payable;
                }
                ),
                TextInput::make('payment_change')
                ->label('kembalian')
                ->numeric()
                ->readOnly(),
                FileUpload::make('photo')
                    ->label('Bukti pembayaran')
                    ->image()
                    ->resize(50),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('account_name')
                ->label('Akun'),
                // Tables\Columns\TextColumn::make('jumlah_bayar')
                // ->money('IDR', locale: 'id_ID'),

                Tables\Columns\TextColumn::make('jumlah_bayar')
                ->summarize(
                    Sum::make()
                        ->money('IDR', locale: 'id_ID')
                        ->label('')
                )
                ->money('IDR', locale: 'id_ID'),

                Tables\Columns\TextColumn::make('payment_change')
                ->label('Kembalian')
                ->summarize(
                    Sum::make()
                        ->money('IDR', locale: 'id_ID')
                        ->label('')
                )
                ->money('IDR', locale: 'id_ID'),

                ImageColumn::make('photo')
                ->label('Bukti Pembayaran')
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
}
