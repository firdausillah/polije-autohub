<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceScheduleReportResource\Pages;
use App\Filament\Resources\ServiceScheduleReportResource\RelationManagers;
use App\Helpers\FormatRupiah;
use App\Models\ServiceDPayment;
use App\Models\ServiceDServices;
use App\Models\ServiceDSparepart;
use App\Models\ServiceScheduleReport;
use App\Models\SparepartDSalePayment;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServiceScheduleReportResource extends Resource
{
    protected static ?string $model = ServiceScheduleReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Laporan';

    public static function getModelLabel(): string
    {
        return 'Riwayat Service';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Riwayat Service';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            ]);
        }
        
        public static function table(Table $table): Table
        {
            return $table->modifyQueryUsing(fn (Builder $query) => $query->where('is_approve', 'approved')->where('deleted_at', null))
            ->columns([
                TextColumn::make('approved_at')->label('Tanggal')->searchable()->sortable(),
                TextColumn::make('vehicle.registration_number')->label('Nopol')->searchable()->sortable(),
                TextColumn::make('kode')->searchable()->sortable(),
                TextColumn::make('customer_name')->searchable()->sortable(),
                TextColumn::make('nomor_telepon')->searchable()->sortable(),
                TextColumn::make('mekanik_name')->searchable()->sortable(),
                TextColumn::make('kepala_unit_name')->searchable()->sortable(),
                //
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Filter::make('approved_at')
                ->form([
                    DatePicker::make('created_from'),
                    DatePicker::make('created_until'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['created_from'],
                            fn (Builder $query, $date): Builder => $query->whereDate('approved_at', '>=', $date),
                        )
                        ->when(
                            $data['created_until'],
                            fn (Builder $query, $date): Builder => $query->whereDate('approved_at', '<=', $date),
                        );
                })
            ])
            ->actions([
                ActionGroup::make([
                Tables\Actions\Action::make('kirimInvoice')
                ->label('Kirim Invoice')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Kirim invoice ke WhatsApp?')
                ->modalDescription('Invoice akan dikirim ke nomor pelanggan.')
                ->url(function ($record) {
                    if ($record->invoice_file) {
                        $payments = ServiceDPayment::where(['service_schedule_id' => $record->id])->get();
                        $total_bayar = $payments->sum('jumlah_bayar');
                        $total_kembalian = $payments->sum('payment_change');
                        // dd($total_bayar);
                        $downloadUrl = route('service.invoice.download', ['filename' => $record->invoice_file]);

                        // $message = "Halo! Terima kasih telah mempercayai Polije Autohub.\nBerikut adalah invoice Anda:\n{$downloadUrl}\n\nJika ada pertanyaan, silakan hubungi kami kembali. 🙏";

                        $message = "Polije Autohub \nJl. Mastrip No.164, Sumbersari, Jember.\n\nPelanggan Yth,\n$record->customer_name\nTanggal : $record->approved_at\n\nBerikut adalah invoice Anda:\n{$downloadUrl}\n\n====================\nDetail Biaya :\nTotal Tagihan : " . FormatRupiah::rupiah($record->harga_subtotal, true) . "\nDiscount : " . FormatRupiah::rupiah($record->discount_total, true) . "\nTotal : ".FormatRupiah::rupiah($record->total, true)."\n-------------------------------\nTotal Bayar : " . FormatRupiah::rupiah($total_bayar, true) . "\nKembalian : " . FormatRupiah::rupiah($total_kembalian, true) . "\n\nJika ada pertanyaan, silakan hubungi kami kembali.\nContact Person : 081132211515";

                        return 'https://wa.me/' . $record->nomor_telepon . '?text=' . rawurlencode($message);
                    }
                })
                ->openUrlInNewTab()
                ->visible(fn ($record) => !empty($record->nomor_telepon && $record->is_approve == 'approved')),
                Tables\Actions\Action::make('preview_invoice')
                ->label('Lihat Invoice')
                ->url(function ($record) {
                    if ($record->invoice_file) {
                        return route('service.invoice.download', ['filename' => $record->invoice_file]);
                    }
                })
                ->openUrlInNewTab()
                ->icon('heroicon-o-document-text')
                ->visible(fn ($record) => !empty($record->nomor_telepon && $record->is_approve == 'approved')),
                ])
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
            'index' => Pages\ListServiceScheduleReports::route('/')
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
