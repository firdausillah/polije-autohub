<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CashFlowResource\Pages;
use App\Filament\Resources\CashFlowResource\RelationManagers;
use App\Helpers\CodeGenerator;
use App\Models\Account;
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

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'POS & Cash Flow';

    public static function InsertJurnal($record, $status): void
    {
        if($status == 'approved'){
            // debit
            Jurnal::create([
                'transaksi_h_id'    => $record->id,
                'transaksi_d_id'    => $record->id,
                'account_id'    => $record->account_debit_id,

                'keterangan'    => $record->keterangan,
                'kode'  => $record->kode,
                'tanggal_transaksi' => $record->tanggal_transaksi,

                'relation_name' => '',
                'relation_nomor_telepon'    => '',

                'account_name'  => $record->account_debit_name,
                'account_kode'  => $record->account_debit_kode,
                'transaction_type'  => 'jurnal umum',

                'debit' => $record->total,
                'kredit'    => 0,
            ]);

            // kredit
            Jurnal::create([
                'transaksi_h_id'    => $record->id,
                'transaksi_d_id'    => $record->id,
                'account_id'    => $record->account_kredit_id,

                'keterangan'    => $record->keterangan,
                'kode'  => $record->kode,
                'tanggal_transaksi' => $record->tanggal_transaksi,

                'relation_name' => '',
                'relation_nomor_telepon'    => '',

                'account_name'  => $record->account_kredit_name,
                'account_kode'  => $record->account_kredit_kode,
                'transaction_type'  => 'jurnal umum',

                'debit' => 0,
                'kredit'    => $record->total,
            ]);
        } else {
            Jurnal::where(['transaksi_h_id' => $record->id, 'transaction_type' => 'jurnal umum'])->delete();
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
                Select::make('account_debit_id')
                ->relationship('accounts', 'name')
                ->getOptionLabelFromRecordUsing(fn($record) => "{$record->kode} ({$record->type}) - {$record->name}")
                ->searchable()
                ->preload()
                ->label('Akun Debit')
                ->required()
                ->live()
                ->afterStateUpdated(function (Set $set, $state) {
                    $debit = Account::find($state);
                    $set('account_debit_name', $debit->name);
                    $set('account_debit_kode', $debit->kode);
                }),
                Select::make('account_kredit_id')
                ->relationship('accounts', 'name')
                ->getOptionLabelFromRecordUsing(fn($record) => "{$record->kode} ({$record->type}) - {$record->name}")
                ->searchable()
                ->preload()
                ->label('Akun Kredit')
                ->required()
                ->live()
                ->afterStateUpdated(function (Set $set, $state) {
                    $kredit = Account::find($state);
                    $set('account_kredit_name', $kredit->name);
                    $set('account_kredit_kode', $kredit->kode);
                }),
                TextInput::make('total')
                ->prefix('Rp ')
                ->numeric()
                ->required(),
                Textarea::make('keterangan'),
                FileUpload::make('photo')
                ->image()
                ->resize(50),

                Hidden::make('account_debit_name'),
                Hidden::make('account_kredit_name'),
                Hidden::make('account_debit_kode'),
                Hidden::make('account_kredit_kode'),
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
                TextColumn::make('account_debit_name')
                ->label('Akun debit')
                ->searchable(),
                TextColumn::make('account_kredit_name')
                ->label('Akun kredit')
                ->searchable(),
                // ImageColumn::make('photo'),
                TextColumn::make('total')->money('IDR', locale: 'id_ID'),

            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Filter::make('tanggal_transaksi')
                ->form([
                    DatePicker::make('from')->default(Carbon::now()->startOfMonth()),
                    DatePicker::make('to')->default(Carbon::now()->endOfMonth()),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when($data['from'], fn ($query) => $query->whereDate('tanggal_transaksi', '>=', $data['from']))
                        ->when($data['to'], fn ($query) => $query->whereDate('tanggal_transaksi', '<=', $data['to']));
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

                            self::InsertJurnal($record, $status);
                            $record->is_approve = $status;
                            $record->approved_by = FacadesAuth::id();
                            $record->save();

                            Notification::make()
                                ->title("Cash Flow $status")
                                ->success()
                                ->body("Cash flow has been $status.")
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
            //
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
