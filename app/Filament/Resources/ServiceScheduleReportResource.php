<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceScheduleReportResource\Pages;
use App\Filament\Resources\ServiceScheduleReportResource\RelationManagers;
use App\Models\ServiceDServices;
use App\Models\ServiceDSparepart;
use App\Models\ServiceScheduleReport;
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
                        ->openUrlInNewTab()
                        ->action(function ($record) {
                            // Cek apakah file sudah ada
                            if (!$record->invoice_file) {
                                // Generate PDF
                                $pdf = new \Mpdf\Mpdf([
                                    'tempDir' => storage_path('app/mpdf-temp')
                                ]);

                                $html = view(
                                    'invoices.service_template',
                                    [
                                        'transaction' => $record,
                                        'transaction_d_service' => ServiceDServices::where(['service_schedule_id' => $record->id])->get(),
                                        'transaction_d_sparepart' => ServiceDSparepart::where(['service_schedule_id' => $record->id])->get()
                                    ]
                                )
                                    ->render();
                                $filename = 'invoice-' . \Illuminate\Support\Str::random(5) . $record->id . \Illuminate\Support\Str::random(5) . '.pdf';
                                $path = storage_path("app/invoices/service/{$filename}");
                                $pdf->WriteHTML($html);
                                $pdf->Output($path, \Mpdf\Output\Destination::FILE);

                                // Simpan nama file di database
                                $record->update(['invoice_file' => $filename]);
                            }

                            // Gunakan file yang sudah ada
                            $downloadUrl = route('service.invoice.download', ['filename' => $record->invoice_file]);
                            $message = "Halo! Terimakasih sudah mempercayai Polije Autohub untuk meningkatkan kenyamanan berkendara anda. Berikut adalah invoice anda: \n{$downloadUrl}";
                            $waLink = 'https://wa.me/' . $record->nomor_telepon . '?text=' . urlencode($message);

                            return redirect($waLink);
                        })
                        ->visible(fn ($record) => !empty($record->nomor_telepon && $record->is_approve == 'approved')),
                    Tables\Actions\Action::make('preview_invoice')
                        ->label('Lihat Invoice')
                        ->url(fn ($record) => route('invoice.service_preview', $record))
                        ->openUrlInNewTab()
                        ->icon('heroicon-o-document-text')
                        ->visible(fn ($record) => !empty($record->nomor_telepon && $record->is_approve == 'approved')),
                    // Tables\Actions\Action::make('preview_service')
                    //     ->label('Lihat Detail Service')
                    //     ->url(fn ($record) => route('service.history', $record))
                    //     ->openUrlInNewTab()
                    //     ->icon('heroicon-o-document-text')
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
