<?php

namespace App\Filament\Reports;

use App\Models\SalesReport;
use App\Models\VUserincomeDaily;
use EightyNine\Reports\Report;
use EightyNine\Reports\Components\Body;
use EightyNine\Reports\Components\Footer;
use EightyNine\Reports\Components\Header;
use EightyNine\Reports\Components\Text;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;

class UserIncomeReport extends Report
{
    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->hasRole(['super_admin', 'Manager', 'Admin', 'pimpinan', 'Kepala Unit']);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function getFilter()
    {
        return 'Per Tanggal: ' . \Carbon\Carbon::parse($this->data['tanggalAkhir'])->translatedFormat('d F Y');
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
                        Text::make("Laporan Perbandingan Pendapatan")
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
                                Body\TextColumn::make("name"),
                                Body\TextColumn::make("keterangan"),
                                Body\TextColumn::make("service_perbandingan_persen")
                                ->suffix('%')
                                ->label('Service')
                                ->color(fn ($state) => $state >= 100 ? 'primary' : 'danger'),
                                Body\TextColumn::make("part_perbandingan_persen")
                                ->suffix('%')
                                ->label('Sparepart')
                                ->color(fn ($state) => $state >= 100 ? 'primary' : 'danger'),
                                Body\TextColumn::make("liquid_perbandingan_persen")
                                ->suffix('%')
                                ->label('Liquid')
                                ->color(fn ($state) => $state >= 100 ? 'primary' : 'danger'),
                                Body\TextColumn::make("total_perbandingan_persen")
                                ->suffix('%')
                                ->label('Total')
                                ->color(fn ($state) => $state >= 100 ? 'primary' : 'danger')
                            ])
                            ->data(
                                function (?array $filters) {

                                    // $sort_by = $filters['sort_by']??null;
                                    $tanggalAkhir = $filters['tanggalAkhir'] ?? now();

                                    $data = VUserincomeDaily::getIncomeComparisonReport($tanggalAkhir);

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
                // Select::make('sort_by')
                // ->label('Urutkan Menurut')
                // ->searchable()
                // ->options([
                //     'name_asc' => 'Nama A ke Z',
                //     'name_desc' => 'Nama Z ke A',
                //     'saldo_asc' => 'Saldo Kecil ke Besar',
                //     'saldo_desc' => 'Saldo Besar ke Kecil',
                //     'terjual_asc' => 'Terjual Kecil ke Besar',
                //     'terjual_desc' => 'Terjual Besar ke Kecil',
                //     // 'penjualan_asc' => 'Total Penjualan Kecil ke Besar',
                //     // 'penjualan_desc' => 'Total Penjualan Besar ke Kecil',
                // ])
                // ->preload(),
                DatePicker::make('tanggalAkhir')
                    ->label('Rentang Tanggal')
                    ->default(now())
            ]);
    }
}
