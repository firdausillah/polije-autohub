<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalaryBonusResource\Pages;
use App\Filament\Resources\SalaryBonusResource\RelationManagers;
use App\Models\Account;
use App\Models\CashFlow;
use App\Models\SalaryBonus;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalaryBonusResource extends Resource
{
    protected static ?string $model = SalaryBonus::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Laporan';

    public static function getModelLabel(): string
    {
        return 'Gaji dan Bonus';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Gaji dan Bonus';
    }

    public static function InsertCashFlow($record, $status): void
    {
        if ($status == 'approved') {

            // dd($record);
            $account_kredit = Account::find(1); //account kas

            DB::transaction(function () use ($account_kredit, $record) {
                // Insert ke tabel cash_flows
                $cashFlow = CashFlow::create([
                    'account_id' => $account_kredit->id,
                    'account_name' => $account_kredit->name,
                    'account_kode' => $account_kredit->kode,

                    'created_by' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now(),

                    'account_type' => 'Kredit',
                    'keterangan' => 'Gaji dan Bonus Periode ' . $record->start_date . ' s/d ' . $record->end_date,

                    'total' => $record->salary_total + $record->bonus_total,
                    'tanggal_transaksi' => now(),
                ]);

                // Ambil ID CashFlow barusan
                $cashFlowId = $cashFlow->id;
                $user_active = Auth::id();

                // cashflow detail gaji begin
                DB::insert("
                INSERT INTO cash_d_flows (
                    cash_flow_id,

                    account_id,
                    account_kode,
                    account_name,

                    created_by,
                    created_at,
                    updated_at,

                    jumlah
                )
                SELECT
                    $cashFlowId,

                    sa.id,
                    sa.kode,
                    sa.name,

                    $user_active,
                    now(),
                    now(),

                    SUM(a.salary)
                FROM salary_d_bonuses a
                LEFT JOIN (
                    SELECT id, name, kode,
                        CASE
                            WHEN kode = 5005 THEN 11
                            WHEN kode = 5006 THEN 9
                            WHEN kode = 5007 THEN 3
                            WHEN kode = 5008 THEN 8
                        END AS role_id
                    FROM accounts
                    WHERE kode IN (5005,5006,5007,5008)
                ) sa ON a.role_id = sa.role_id
                WHERE a.salary_bonus_id = ?
                GROUP BY a.role_id, sa.id, sa.kode, sa.name
            ", [$record->id]);
                DB::insert("
                    INSERT INTO cash_d_flows (
                        cash_flow_id,

                        account_id,
                        account_kode,
                        account_name,

                        created_by,
                        created_at,
                        updated_at,

                        jumlah
                    )
                    SELECT
                        $cashFlowId,

                        ba.id,
                        ba.kode,
                        ba.name,

                        $user_active,
                        now(),
                        now(),

                        SUM(a.bonus)
                    FROM salary_d_bonuses a
                    LEFT JOIN (
                        SELECT id, name, kode,
                            CASE
                                WHEN kode = 5001 THEN 11
                                WHEN kode = 5002 THEN 9
                                WHEN kode = 5003 THEN 3
                                WHEN kode = 5004 THEN 8
                            END AS role_id
                        FROM accounts
                        WHERE kode IN (5001,5002,5003,5004)
                    ) ba ON a.role_id = ba.role_id
                    WHERE a.salary_bonus_id = ?
                    GROUP BY a.role_id, ba.id, ba.kode, ba.name
                ", [$record->id]);
            });
                // cashflow detail bonus begin
            // cashflow detail end

        // } else {
        //     CashFlow::where(['transaksi_h_id' => $record->id, 'movement_type' => 'IN-PUR'])->delete();
        }
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            DatePicker::make('start_date')
                ->readOnly()
                ->default(function () {
                    $lastEndDate = SalaryBonus::orderBy('end_date', 'desc')->limit(1)->first()?->end_date;

                    return $lastEndDate
                        ? Carbon::parse($lastEndDate)->addDay()->toDateString()
                        : Carbon::now()->startOfMonth()->toDateString();
                }),

                DatePicker::make('end_date')
                ->readOnly()
                ->default(NOW()),
                TextInput::make('salary_total')
                ->readOnly(),
                TextInput::make('bonus_total')
                ->readOnly(),
                TextInput::make('pendapatan_total')
                ->readOnly(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make( 'start_date')
                ->label('Periode')
                ->formatStateUsing(function(SalaryBonus $salary_bonus){
                    $start = $salary_bonus->start_date ? Carbon::parse($salary_bonus->start_date)->format('d M Y') : '-';
                    $end = $salary_bonus->end_date ? Carbon::parse($salary_bonus->end_date)->format('d M Y') : '-';
                    return "$start s/d $end";
                }),
                // TextColumn::make( 'end_date'),
                TextColumn::make('salary_total')->money('IDR', locale: 'id_ID'),
                TextColumn::make('bonus_total')->money('IDR', locale: 'id_ID'),
                TextColumn::make('pendapatan_total')->money('IDR', locale: 'id_ID'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('Tarik ke CashFlow Umum')
                    ->action(function (SalaryBonus $record) {

                        $isApproving = in_array($record->is_approve, ['pending', 'rejected']);
                        $status = $isApproving ? 'approved' : 'rejected';

                        // if($record->total != null){
                            $record->is_approve = $status;
                            $record->approved_by = Auth::id();
                            $record->approved_at = NOW();
                            $record->save();

                            self::InsertCashFlow($record, $status);

                        // }

                        Notification::make()
                            ->title("Pulling data $status")
                            ->success()
                            ->body("Pulling data has been $status.")
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalDescription(fn (SalaryBonus $record) => $record->is_approve === 'approved' ? 'Reject tidak akan menarik kembali data dari Cashflow, jika ingin mengubah data, hapus dulu data Gaji dan Bonus di menu Cashflow agar data tidak double' : 'Anda akan menarik data ini ke menu Cashflow')
                    ->color(fn (SalaryBonus $record) => $record->is_approve === 'approved' ? 'danger' : 'info')
                    ->icon(fn (SalaryBonus $record) => $record->is_approve === 'approved' ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->label(fn (SalaryBonus $record) => $record->is_approve === 'approved' ? 'Reject' : 'Approve'),

                
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
            RelationManagers\SalaryDBonusRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalaryBonuses::route('/'),
            'create' => Pages\CreateSalaryBonus::route('/create'),
            'edit' => Pages\EditSalaryBonus::route('/{record}/edit'),
        ];
    }
}
