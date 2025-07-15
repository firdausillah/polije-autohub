<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CashFlowResource\Pages;
use App\Filament\Resources\CashFlowResource\RelationManagers;
use App\Helpers\CodeGenerator;
use App\Models\Account;
use App\Models\CashDFlow;
use App\Models\CashFlow;
use App\Models\Jurnal;
use Carbon\Carbon;
use Filament\Actions\ActionGroup;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Notifications\Actions\ActionGroup as ActionsActionGroup;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup as TablesActionsActionGroup;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\Date;

class CashFlowResource extends Resource
{
    protected static ?string $model = CashFlow::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    public static function getModelLabel(): string
    {
        return 'Jurnal Umum';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Jurnal Umum';
    }

    public static function insertJurnalDetail($record, $table_type, $account_type, $data_prepare){
        Jurnal::create([
            'transaksi_h_id'    => $data_prepare['header_id'],
            'transaksi_d_id'    => $record->id,
            'account_id'    => $record->account_id,

            'keterangan'    => $record->keterangan,
            'kode'  => $data_prepare['kode'],
            'tanggal_transaksi' => $data_prepare['tanggal'],

            'relation_name' => '',
            'relation_nomor_telepon'    => '',

            'account_name'  => $record->account_name,
            'account_kode'  => $record->account_kode,
            'transaction_type'  => 'jurnal umum',

            'debit' => ($account_type == 'debit')
                        ? (($table_type == 'header')
                            ? $record->total
                            : (($table_type == 'detail') ? $record->jumlah : 0))
                        : 0,
            'kredit'=>($account_type == 'kredit')
                        ? (($table_type == 'header')
                            ? $record->total
                            : (($table_type == 'detail') ? $record->jumlah : 0))
                        : 0,
        ]);
    }

    public static function InsertJurnal($record, $status)
    {
        // dd($record);
        if($status == 'approved'){
            $detail = CashDFlow::where('cash_flow_id', $record->id)->get();

            if($record->total != $detail->sum('jumlah')){
                return ['title' =>'Approval Gagal', 'body' =>'total harus sama dengan total jumlah di detail', 'status' => 'warning'];
            }

            $data_prepare = [
                'header_id' => $record->id,
                'kode' => $record->kode,
                'tanggal' => $record->tanggal_transaksi,
            ];
            
            if($record->account_type == 'Debit'){
                Self::insertJurnalDetail($record, 'header', 'debit', $data_prepare);
                foreach($detail as $val){
                    Self::insertJurnalDetail($val, 'detail', 'kredit', $data_prepare);
                }
            }else{
                foreach($detail as $val){
                    Self::insertJurnalDetail($val, 'detail', 'debit', $data_prepare);
                }
                Self::insertJurnalDetail($record, 'header', 'kredit', $data_prepare);
            }

            return ['title' => 'Approval Berhasil', 'body' => 'Cash Flow Berhasil Diapprove', 'status' => 'success'];
        } else {
            Jurnal::where(['transaksi_h_id' => $record->id, 'transaction_type' => 'jurnal umum'])->delete();

            return ['title' => 'Reject Berhasil', 'body' => 'Cash Flow Berhasil Direject', 'status' => 'success'];
        }
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                DateTimePicker::make('tanggal_transaksi')
                ->label('Tanggal')
                ->required()
                ->default(NOW()),
                TextInput::make('kode')
                ->readOnly(),
                Select::make('account_type')
                ->label('Jenis Akun')
                ->required()
                ->default('Debit')
                ->options([
                   'Debit' => 'Debit', 
                   'Kredit' => 'Kredit' 
                ]),
                Select::make('account_id')
                ->relationship('account', 'name')
                ->getOptionLabelFromRecordUsing(fn($record) => "{$record->kode} ({$record->type}) - {$record->name}")
                ->searchable()
                ->preload()
                ->label('Akun')
                ->required()
                ->live()
                ->afterStateUpdated(function (Set $set, $state) {
                    $kredit = Account::find($state);
                    $set('account_name', $kredit->name);
                    $set('account_kode', $kredit->kode);
                }),
                TextInput::make('total')
                ->prefix('Rp ')
                ->numeric()
                ->required(),
                Textarea::make('keterangan'),
                // FileUpload::make('photo')
                // ->image()
                // ->resize(50),

                Hidden::make('account_name')
                ->required(),
                Hidden::make('account_kode')
                ->required(),
            ])->disabled(fn ($record) => $record && $record->is_approve === 'approved');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tanggal_transaksi')
                ->formatStateUsing(fn ($state) => Carbon::parse($state)->translatedFormat('d M Y H:i:s')),
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
                TextColumn::make('account_name')
                ->label('Akun')
                ->searchable(),
                TextColumn::make('account_type')
                ->label('Jenis Akun')
                ->searchable(),
                // ImageColumn::make('photo'),
                TextColumn::make('total')->money('IDR', locale: 'id_ID'),

            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Filter::make('created_at')
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
                        ->action(function (CashFlow $record) {
                            if (empty($record->kode)) {
                                $record->kode = CodeGenerator::generateTransactionCode('CFL', 'cash_flows', 'kode');
                            }

                            $isApproving = in_array($record->is_approve, ['pending', 'rejected']);
                            $status = $isApproving ? 'approved' : 'rejected';

                            $message = self::InsertJurnal($record, $status);
                            if($message['status'] == 'success'){
                                $record->is_approve = $status;
                                $record->approved_by = FacadesAuth::id();
                                $record->save();
                            }
                            
                            Notification::make()
                                ->title($message['title'])
                                ->{($message['status'] == 'success' ? 'success' : 'warning')}()
                                ->body($message['body'])
                                ->send();
                        })
                        ->color(fn (CashFlow $record) => $record->is_approve === 'approved' ? 'danger' : 'info')
                        ->icon(fn (CashFlow $record) => $record->is_approve === 'approved' ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                        ->requiresConfirmation()
                        ->label(fn (CashFlow $record) => $record->is_approve === 'approved' ? 'Reject' : 'Approve'),

                    Tables\Actions\EditAction::make()
                    ->visible(fn (CashFlow $record) => $record->is_approve !== 'approved'),
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

    public static function getRelations(): array
    {
        return [
            RelationManagers\CashDFlowRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCashFlows::route('/'),
            'create' => Pages\CreateCashFlow::route('/create'),
            'edit' => Pages\EditCashFlow::route('/{record}/edit'),
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
