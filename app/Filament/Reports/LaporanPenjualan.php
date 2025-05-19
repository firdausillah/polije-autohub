<?php

namespace App\Filament\Reports;

use App\Models\SalesReport;
use EightyNine\Reports\Report;
use EightyNine\Reports\Components\Body;
use EightyNine\Reports\Components\Footer;
use EightyNine\Reports\Components\Header;
use EightyNine\Reports\Components\Text;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;

class LaporanPenjualan extends Report
{
    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->hasRole(['super_admin', 'Manager', 'Admin']);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function getFilter()
    {
        return 'Periode: ' . \Carbon\Carbon::parse($this->data['start'])->translatedFormat('d F Y') . ' - ' . \Carbon\Carbon::parse($this->data['end'])->translatedFormat('d F Y');
    }

    public function header(Header $header): Header
    {
        return $header
            ->schema([
                Header\Layout\HeaderColumn::make()
                    ->alignCenter()
                    ->schema([
                        Text::make("Polije Autohub")
                        ->font2Xl()
                            ->fontBold(),
                        Text::make("Laporan Penjualan")
                        ->fontXl()
                            ->fontBold(),
                        Text::make($this->getFilter())
                            ->fontNormal()
                    ]),
            ]);
    }


    public function body(Body $body): Body
    {
        return $body
        ->schema([
            Body\Layout\BodyColumn::make()
                ->schema([
                    Body\Table::make()
                        ->columns([
                            Body\TextColumn::make("sparepart_name"),
                            Body\TextColumn::make("saldo"),
                            Body\TextColumn::make("qty_terjual"),
                            Body\TextColumn::make("total_penjualan")
                            ->money('IDR')
                            ->alignRight()
                            ->sum(),
                        ])
                        ->data(
                            function (?array $filters) {

                                $startDate = $filters['start'] ?? now()->startOfMonth();
                                $endDate = $filters['end'] ?? now()->endOfMonth();

                                $data = SalesReport::getData($startDate, $endDate);

                                return collect($data);
                            }
                        )
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
                DatePicker::make('start')
                    ->label('Rentang Tanggal')
                    ->default(now()->startOfMonth()), // Default ke 1 bulan terakhir
                DatePicker::make('end')
                    ->label('Rentang Tanggal')
                    ->default(now()->endOfMonth())
            ]);
    }
}
