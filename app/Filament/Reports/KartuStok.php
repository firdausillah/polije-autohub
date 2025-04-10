<?php

namespace App\Filament\Reports;

use App\Models\Account;
use App\Models\KartuStok as ModelsKartuStok;
use EightyNine\Reports\Report;
use EightyNine\Reports\Components\Body;
use EightyNine\Reports\Components\Footer;
use EightyNine\Reports\Components\Header;
use Filament\Forms\Form;
use EightyNine\FilamentReports\Components\Filters\DateRangeFilter;
use EightyNine\Reports\Components\Text;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;

class KartuStok extends Report
{
    public ?string $heading = "Report";
    

    // public ?string $subHeading = "A great report";

    public function header(Header $header): Header
    {
        return $header
            ->schema([
                // ...
            ]);
    }


    public function body(Body $body): Body
    {

        return $body
        ->schema([
            Body\Layout\BodyColumn::make()
                ->schema([
                    Text::make("Kartu Stok Sparepart")
                        ->fontXl()
                        ->fontBold()
                        ->primary(),
                    // Text::make("This is a list of registered users from the specified date range")
                    // ->fontSm()
                    // ->secondary(),
                    Body\Table::make()
                        ->columns([
                            Body\TextColumn::make("created_at")
                            ->label('Tanggal')
                            ->dateTime('d/M/Y'),
                            Body\TextColumn::make("transaksi_kode")
                            ->label('Kode'),
                            Body\TextColumn::make("sparepart_name")
                            ->label('Sparepart'),
                            // Body\TextColumn::make("satuan"),
                            Body\TextColumn::make("relation_name")
                            ->label('Relasi'),
                            Body\TextColumn::make("qty_masuk"),
                            Body\TextColumn::make("qty_keluar"),
                            Body\TextColumn::make("saldo"),
                        ])
                        ->data(
                            function (?array $filters) {

                                $sparepartId = $filters['sparepart_id']??null;
                                $startDate = $filters['start']??now()->startOfMonth();
                                $endDate = $filters['end']??now()->endOfMonth();
                                
                                $data = ModelsKartuStok::getLaporanByTanggal($sparepartId, $startDate, $endDate);

                                return collect($data);
                            }
                        ),
                    // VerticalSpace::make(),
                ]),
        ]);
    }
    

    public function footer(Footer $footer): Footer
    {
        return $footer
            ->schema([
                // ...
            ]);
    }

    public function filterForm(Form $form): Form
    {

        return $form
            ->schema([
                Select::make('sparepart_id')
                ->label('Sparepart')
                // ->searchable()
                ->options(function () {
                    return \App\Models\Sparepart::orderBy('name')->pluck('name', 'id');
                })
                ->preload(),
                DatePicker::make('start')
                ->label('Rentang Tanggal')
                ->default(now()->startOfMonth()), // Default ke 1 bulan terakhir
                DatePicker::make('end')
                ->label('Rentang Tanggal')
                ->default(now()->endOfMonth()) 
            ]);
    }
}
