<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SparepartPurchaseResource\Pages;
use App\Filament\Resources\SparepartPurchaseResource\RelationManagers;
use App\Filament\Resources\SparepartPurchaseResource\RelationManagers\SparepartDPurchaseRelationManager;
use App\Helpers\CodeGenerator;
use App\Models\Account;
use App\Models\Jurnal;
use App\Models\SparepartPurchase;
use Carbon\Carbon;
use DateTime;
use DeepCopy\Filter\Filter;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter as FiltersFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\ActionGroup as TablesActionsActionGroup;
use Illuminate\Support\Facades\Auth as FacadesAuth;

class SparepartPurchaseResource extends Resource
{
    protected static ?string $model = SparepartPurchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Sparepart';

    public static function InsertJurnal($record, $status): void
    {
        if ($status == 'approved') {
            // debit
            Jurnal::create([
                'transaksi_h_id'    => $record->id,
                'transaksi_d_id'    => $record->id,
                'account_id'    => $record->account_debit_id,

                'keterangan'    => $record->keterangan,
                'kode'  => $record->kode,
                'tanggal_transaksi' => $record->tanggal_transaksi,

                'relation_nama' => '',
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

                'relation_nama' => '',
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
                TextInput::make('supplier_nama')
                ->required(),
                TextInput::make('supplier_nomor_telepon'),
                TextInput::make('purchase_receipt')
                ->label('Nota Pembelian'),
                Textarea::make('keterangan'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tanggal_transaksi')
                ->label('Tanggal'),
                TextColumn::make('kode'),
                TextColumn::make('is_approve')
                ->label('Status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'pending' => 'gray',
                    'approved' => 'success',
                    'rejected' => 'danger',
                }),
                TextColumn::make('supplier_nama'),
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
                    ->action(function (SparepartPurchase $record) {
                        if (empty($record->kode)) {
                            $record->kode = CodeGenerator::generateTransactionCode('CFL', 'sparepart_purchases', 'kode');
                        }

                        $isApproving = in_array($record->is_approve, ['pending', 'rejected']);
                        $status = $isApproving ? 'approved' : 'rejected';

                        self::InsertJurnal($record, $status);
                        $record->is_approve = $status;
                        $record->approved_by = FacadesAuth::id();
                        $record->save();

                        Notification::make()
                            ->title("Sparepart Purchase $status")
                            ->success()
                            ->body("Sparepart Purchase has been $status.")
                            ->send();
                    })
                        ->color(fn (SparepartPurchase $record) => $record->is_approve === 'approved' ? 'danger' : 'info')
                        ->icon(fn (SparepartPurchase $record) => $record->is_approve === 'approved' ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                        ->requiresConfirmation()
                        ->label(fn (SparepartPurchase $record) => $record->is_approve === 'approved' ? 'Reject' : 'Approve'),

                    Tables\Actions\EditAction::make()
                        ->visible(fn (SparepartPurchase $record) => $record->is_approve !== 'approved'),
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
            SparepartDPurchaseRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSparepartPurchases::route('/'),
            'create' => Pages\CreateSparepartPurchase::route('/create'),
            'edit' => Pages\EditSparepartPurchase::route('/{record}/edit'),
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
