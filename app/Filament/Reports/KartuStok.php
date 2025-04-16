<?php

namespace App\Filament\Reports;

use App\Models\Account;
use App\Models\KartuStok as ModelsKartuStok;
use App\Models\Sparepart;
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
    // public ?string $heading = "Report";

    // public ?string $subHeading = "A great report";

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->hasRole(['super_admin', 'Manager']);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function getSparepartName()
    {
        
        $sparepart = isset($this->data['sparepart_id'])?Sparepart::find($this->data['sparepart_id'])->name:'Sparepart Belum Dipilih';

        $periode = 'Periode: ' . \Carbon\Carbon::parse($this->data['start'])->translatedFormat('d F Y') . ' - ' . \Carbon\Carbon::parse($this->data['end'])->translatedFormat('d F Y');
        // dd(Sparepart::find($this->data['sparepart_id'])->name);

        return ['sparepart' => $sparepart, 'periode' => $periode];
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
                        Text::make("Laporan Kartu Stok")
                        ->fontXl()
                        ->fontBold(),
                        Text::make($this->getSparepartName()['periode'])
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
                    Text::make($this->getSparepartName()['sparepart'])
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
                            // Body\TextColumn::make("sparepart_name")
                            // ->label('Sparepart'),
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
                        )
                        // ->([
                        //     'class' => 'overflow-x-auto w-full block', // Tambahin scroll
                        // ]),
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
