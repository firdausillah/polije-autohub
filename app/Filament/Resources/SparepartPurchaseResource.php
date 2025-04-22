<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SparepartPurchaseResource\Pages;
use App\Filament\Resources\SparepartPurchaseResource\RelationManagers;
use App\Filament\Resources\SparepartPurchaseResource\RelationManagers\SparepartDPurchaseRelationManager;
use App\Helpers\CodeGenerator;
use App\Helpers\priceFix;
use App\Helpers\Round;
use App\Models\Account;
use App\Models\Modal;
use App\Models\Inventory;
use App\Models\Jurnal;
use App\Models\SparepartDPurchase;
use App\Models\SparepartPurchase;
use App\Models\SparepartSatuans;
use Carbon\Carbon;
use DateTime;
use DeepCopy\Filter\Filter;
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
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter as FiltersFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\ActionGroup as TablesActionsActionGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\DB;

class SparepartPurchaseResource extends Resource
{
    protected static ?string $model = SparepartPurchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function getModelLabel(): string
    {
        return 'Pembelian Sparepart';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Pembelian Sparepart';
    }

    public static function InsertJurnal($record, $status): void
    {
        if ($status == 'approved') {

            // jurnal begin
            
            // D. Persediaan Sparepart xxx  
            //    C. Kas/Bank xxx  

            // debit
            // dd($record);
            $account_debit = Account::find(3); //Persediaan Sparepart
            Jurnal::create([
                'transaksi_h_id'    => $record->id,
                'transaksi_d_id'    => $record->id,
                'account_id'    => $account_debit->id,//

                'keterangan'    => $record->keterangan,
                'kode'  => $record->kode,
                'tanggal_transaksi' => $record->tanggal_transaksi,

                'relation_name' => $record->supplier_name,
                'relation_nomor_telepon'    => $record->supplier_nomor_telepon,

                'account_name'  => $account_debit->name, //
                'account_kode'  => $account_debit->kode,//
                'transaction_type'  => 'pembelian sparepart',

                'debit' => $record->total,
                'kredit'    => 0,
            ]);

            // kredit
            Jurnal::create([
                'transaksi_h_id'    => $record->id,
                'transaksi_d_id'    => $record->id,
                'account_id'    => $record->account_id,

                'keterangan'    => $record->keterangan,
                'kode'  => $record->kode,
                'tanggal_transaksi' => $record->tanggal_transaksi,

                'relation_name' => $record->supplier_name,
                'relation_nomor_telepon'    => $record->supplier_nomor_telepon,

                'account_name'  => $record->account_name,
                'account_kode'  => $record->account_kode,
                'transaction_type'  => 'pembelian sparepart',

                'debit' => 0,
                'kredit'    => $record->total,
            ]);
            // jurnal end

            // inventory begin
            $SparepartDPurchases = SparepartDPurchase::where('sparepart_purchase_id', $record->id)->get();
            $harga_modal = '';
            foreach ($SparepartDPurchases as $val) {
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
                    
                    'movement_type' => 'IN-PUR',

                    'jumlah_unit' => $val->jumlah_unit,
                    'jumlah_konversi' => $val->jumlah_konversi,
                    'jumlah_terkecil' => $val->jumlah_terkecil,

                    'harga_unit' => $val->harga_unit,
                    'harga_terkecil' => $val->harga_terkecil,
                    'harga_subtotal' => $val->harga_subtotal,
                    
                    'relation_name' => $record->supplier_name,
                    'relation_nomor_telepon' => $record->supplier_nomor_telepon
                ]);

                // harga_modal begin
                $harga_modal = SparepartDPurchase::where('sparepart_id', $val->sparepart_id)
                ->leftJoin('sparepart_purchases as b', 'sparepart_d_purchases.sparepart_purchase_id', '=', 'b.id')
                ->where('b.is_approve', 'approved')
                ->orderBy('sparepart_d_purchases.created_at', 'desc') // Urutkan dari yang terbaru
                ->limit(3) // Ambil 3 data terbaru
                    ->get(['harga_subtotal', 'jumlah_terkecil']) // Ambil kolom yang diperlukan
                    ->map(fn ($item) => $item->harga_subtotal / max($item->jumlah_terkecil, 1)) // Hindari pembagian nol
                    ->avg(); // Hitung rata-rata dari hasil pembagian


                Modal::create([
                    'transaksi_h_id' => $record->id,
                    'transaksi_d_id' => $val->id,
                    'tanggal_transaksi' => $record->tanggal_transaksi,
                    'sparepart_id' => $val->sparepart_id,
                    'transaksi_h_kode' => $record->kode,
                    'harga_modal' => round($harga_modal, 2),
                    'keterangan' => 'pembelian sparepart'
                ]);
                // harga_modal end

                // update harga sparepart
                priceFix::priceFixer($val->sparepart_id);
            }
            // inventory end
        } else {
            Inventory::where(['transaksi_h_id' => $record->id, 'movement_type' => 'IN-PUR'])->delete();
            Jurnal::where(['transaksi_h_id' => $record->id, 'transaction_type' => 'pembelian sparepart'])->delete();
            Modal::where(['transaksi_h_id' => $record->id, 'keterangan' => 'pembelian sparepart'])->delete();
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
                TextInput::make('supplier_name')
                ->required(),
                TextInput::make('supplier_nomor_telepon')
                ->numeric(),
                TextInput::make('purchase_receipt')
                ->required()
                ->label('Nota Pembelian'),
                Select::make('account_id')
                ->relationship('account', 'name')
                ->required()
                ->live()
                ->afterStateUpdated(function(Set $set, $state){
                    $account = Account::find($state);
                    $set('account_name', $account->name);
                    $set('account_kode', $account->kode);
                }),
                Textarea::make('keterangan'),
                FileUpload::make('photo')
                ->image()
                ->resize(50),

                Hidden::make('account_name'),
                Hidden::make('account_kode'),
                
            ])->disabled(fn ($record) => $record && $record->is_approve === 'approved');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tanggal_transaksi')
                ->formatStateUsing(fn ($state) => Carbon::parse($state)->translatedFormat('d M Y H:i:s'))
                ->sortable('desc')
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
                TextColumn::make('supplier_name')
                ->searchable()
                ->label('supplier'),
                TextColumn::make('account_name')
                ->searchable()
                ->label('account'),
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
                            $record->kode = CodeGenerator::generateTransactionCode('SPS', 'sparepart_purchases', 'kode');
                        }

                        $isApproving = in_array($record->is_approve, ['pending', 'rejected']);
                        $status = $isApproving ? 'approved' : 'rejected';

                        if($record->total != null){
                            $record->is_approve = $status;
                            $record->approved_by = FacadesAuth::id();
                            $record->approved_at = NOW();
                            $record->save();
                            self::InsertJurnal($record, $status);

                        }

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

    public static function canDelete($record): bool
    {
        return $record->is_approve !== 'approved';
    }

}
