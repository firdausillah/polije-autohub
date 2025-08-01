<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockAdjustmentResource\Pages;
use App\Filament\Resources\StockAdjustmentResource\RelationManagers;
use App\Helpers\CodeGenerator;
use App\Models\Account;
use App\Models\Inventory;
use App\Models\Jurnal;
use App\Models\Modal;
use App\Models\StockAdjustment;
use App\Models\StockDAdjustment;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StockAdjustmentResource extends Resource
{
    protected static ?string $model = StockAdjustment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getModelLabel(): string
    {
        return 'Penyesuaian Stok';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Penyesuaian Stok';
    }

    public static function InsertJurnal($record, $status)
    {
        try {
            if ($status == 'approved') {

                // a. Jika Stok Berkurang (Kerugian/Penyusutan Stok):
                // D. Penyesuaian Persediaan xxx
                //     C. Persediaan Sparepart xxx
                
                // b. Jika Stok Bertambah (Penambahan Stok Fisik Tak Tercatat):
                // D. Persediaan Sparepart xxx
                //     C. Penyesuaian Persediaan xxx


                // account
                $account_persediaan = Account::where('kode', 1101)->first(); // Persediaan Sparepart
                $account_penyesuaian_persediaan = Account::where('kode', 6003)->first(); // Persediaan Sparepart
                $StockDAdjustments = StockDAdjustment::where('stock_adjustment_id', $record->id)->get();
                
                foreach ($StockDAdjustments as $key => $val) {

                    if($val->jumlah_terkecil_selisih > 0){
                        $account_debit = $account_persediaan;
                        $account_kredit = $account_penyesuaian_persediaan;
                        $movement_type = 'IN-ADJ';
                    }else{
                        $account_debit = $account_penyesuaian_persediaan;
                        $account_kredit = $account_persediaan;
                        $movement_type = 'OUT-ADJ';
                    }

                    // debit
                    Jurnal::create([
                        'transaksi_h_id' => $record->id,
                        'transaksi_d_id' => $val->id,
                        'account_id' => $account_debit->id,
                        'keterangan' => $record->keterangan,
                        'kode' => $record->kode,
                        'tanggal_transaksi' => $record->tanggal_transaksi,
                        'relation_name' => '',
                        'relation_nomor_telepon' => '',
                        'account_name' => $account_debit->name,
                        'account_kode' => $account_debit->kode,
                        'transaction_type' => 'stock adjustment',
                        'debit' => abs($val->harga_subtotal),
                        'kredit' => 0,
                    ]);
    
                    // kredit
                    Jurnal::create([
                        'transaksi_h_id' => $record->id,
                        'transaksi_d_id' => $val->id,
                        'account_id' => $account_kredit->id,
                        'keterangan' => $record->keterangan,
                        'kode' => $record->kode,
                        'tanggal_transaksi' => $record->tanggal_transaksi,
                        'relation_name' => '',
                        'relation_nomor_telepon' => '',
                        'account_name' => $account_kredit->name,
                        'account_kode' => $account_kredit->kode,
                        'transaction_type' => 'stock adjustment',
                        'debit' => 0,
                        'kredit' => abs($val->harga_subtotal),
                    ]);

                    //Inventory
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
                        'movement_type' => $movement_type,
                        'jumlah_unit' => abs($val->jumlah_terkecil_selisih),
                        'jumlah_konversi' => $val->jumlah_konversi,
                        'jumlah_terkecil' => abs($val->jumlah_terkecil_selisih),
                        'harga_unit' => abs($val->harga_unit),
                        'harga_terkecil' => $val->harga_terkecil,
                        'harga_subtotal' => abs($val->harga_subtotal),
                        'relation_name' => '',
                        'relation_nomor_telepon' => ''
                    ]);

                }
                return ['title' => 'Approval Berhasil', 'body' => 'Penyesuaian Stok Berhasil Diapprove', 'status' => 'success'];
            } else {
                // Cancel transaksi: rollback jurnal & inventory
                Inventory::where([
                    'kode' => $record->kode
                ])->delete();

                Jurnal::where([
                    'kode' => $record->kode,
                    'transaction_type' => 'stock adjustment'
                ])->delete();
                return ['title' => 'Reject Berhasil', 'body' => 'Penyesuaian Stok Berhasil Direject', 'status' => 'success'];

                // Modal::where([
                //     'transaksi_h_id' => $record->id,
                //     'keterangan' => 'stock adjustment'
                // ])->delete();
            }
        } catch (\Throwable $th) {
            Log::error('InsertJurnal error: ' . $th->getMessage(), [
                'file' => $th->getFile(),
                'line' => $th->getLine(),
                'record_id' => $record->id ?? null
            ]);
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
                Textarea::make('keterangan'),
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
                TextColumn::make('user.name')
                ->label('Petugas')

            ])
            ->filters([Tables\Filters\TrashedFilter::make(),
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
                ActionGroup::make([
                    Tables\Actions\Action::make('approve')
                        ->action(function (StockAdjustment $record) {
                            if (empty($record->kode)) {
                                $record->kode = CodeGenerator::generateTransactionCode('STA', 'stock_adjustments', 'kode');
                            }

                            $isApproving = in_array($record->is_approve, ['pending', 'rejected']);
                            $status = $isApproving ? 'approved' : 'rejected';

                            // self::InsertJurnal($record, $status);
                            $message = self::InsertJurnal($record, $status);
                            if($message['status'] == 'success'){
                                $record->is_approve = $status;
                                $record->approved_by = Auth::id();
                                $record->save();
                            }
                            
                            Notification::make()
                                ->title($message['title'])
                                ->{($message['status'] == 'success' ? 'success' : 'warning')}()
                                ->body($message['body'])
                                ->send();
                        })
                        ->color(fn (StockAdjustment $record) => $record->is_approve === 'approved' ? 'danger' : 'info')
                        ->icon(fn (StockAdjustment $record) => $record->is_approve === 'approved' ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                        ->requiresConfirmation()
                        ->label(fn (StockAdjustment $record) => $record->is_approve === 'approved' ? 'Reject' : 'Approve'),

                    Tables\Actions\EditAction::make()
                    ->visible(fn (StockAdjustment $record) => $record->is_approve !== 'approved'),
                ])
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
            RelationManagers\StockDAdjustmentRelationManager::class,
        ];
    }
    
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockAdjustments::route('/'),
            'create' => Pages\CreateStockAdjustment::route('/create'),
            'edit' => Pages\EditStockAdjustment::route('/{record}/edit'),
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
