<?php

namespace App\Filament\Reports;

use App\Models\SalesReport;
use EightyNine\Reports\Report;
use EightyNine\Reports\Components\Body;
use EightyNine\Reports\Components\Footer;
use EightyNine\Reports\Components\Header;
use EightyNine\Reports\Components\Text;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
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
                            // Body\TextColumn::make("total_penjualan")
                            // ->money('IDR')
                            // ->alignRight()
                            // ->sum(),
                        ])
                        ->data(
                            function (?array $filters) {

                                $sort_by = $filters['sort_by']??null;
                                $startDate = $filters['start'] ?? now()->startOfMonth();
                                $endDate = $filters['end'] ?? now()->endOfMonth();

                                $data = SalesReport::getData($sort_by, $startDate, $endDate);

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
                Select::make('sort_by')
                ->label('Urutkan Menurut')
                ->searchable()
                ->options([
                    'name_asc' => 'Nama A ke Z',
                    'name_desc' => 'Nama Z ke A',
                    'saldo_asc' => 'Saldo Kecil ke Besar',
                    'saldo_desc' => 'Saldo Besar ke Kecil',
                    'terjual_asc' => 'Terjual Kecil ke Besar',
                    'terjual_desc' => 'Terjual Besar ke Kecil',
                    // 'penjualan_asc' => 'Total Penjualan Kecil ke Besar',
                    // 'penjualan_desc' => 'Total Penjualan Besar ke Kecil',
                ])
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
