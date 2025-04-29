<?php

namespace App\Filament\Reports;

use App\Models\LabaRugi as ModelsLabaRugi;
use EightyNine\Reports\Report;
use EightyNine\Reports\Components\Body;
use EightyNine\Reports\Components\Footer;
use EightyNine\Reports\Components\Header;
use EightyNine\Reports\Components\Text;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Tables\Columns\Summarizers\Sum;
use Carbon\Carbon;
use EightyNine\Reports\Components\Body\TextColumn;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Support\Facades\Auth;

class LabaRugi extends Report
{

    // public ?string $heading = "Laporan Laba Rugi";

    // public ?string $subHeading = "A great report";

    public static function canAccess(): bool
    {
    return auth()->check() && auth()->user()->hasRole(['super_admin', 'Manager', 'Admin']);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function getFilter(){
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
                    Text::make("Laporan Laba Rugi")
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
                        Text::make("Pendapatan")
                            ->fontSm()
                            ->fontBold()
                            ->primary(),
                        Body\Table::make()
                            ->columns([
                                Body\TextColumn::make("name")
                                ->label('Akun'),
                                // Body\TextColumn::make("kode"),
                                Body\TextColumn::make("jumlah")
                                ->money('IDR')
                                ->alignRight()
                                ->sum(),
                            ])
                            ->data(
                                function (?array $filters) {
                                    $startDate = $filters['start'] ?? now()->startOfMonth();
                                    $endDate = $filters['end'] ?? now()->endOfMonth();

                                    $data = ModelsLabaRugi::getPendapatan($startDate, $endDate);

                                    return collect($data);
                                }
                            ),
                        // VerticalSpace::make(),
                    ]),
                Body\Layout\BodyColumn::make()
                    ->schema([
                        Text::make("Harga Pokok Penjualan")
                            ->fontSm()
                            ->fontBold()
                            ->primary(),
                        Body\Table::make()
                            ->columns([
                                Body\TextColumn::make("name")
                                ->label(''),
                                // Body\TextColumn::make("kode"),
                                Body\TextColumn::make("jumlah")
                                ->money('IDR')
                                ->alignRight(),
                            ])
                            ->data(
                                function (?array $filters) {
                                    $startDate = $filters['start'] ?? now()->startOfMonth();
                                    $endDate = $filters['end'] ?? now()->endOfMonth();

                                    $data = ModelsLabaRugi::getHpp($startDate, $endDate);

                                    return collect($data);
                                }
                            ),
                        // VerticalSpace::make(),
                    ]),
                Body\Layout\BodyColumn::make()
                    ->schema([
                        Text::make("Laba Kotor")
                            ->fontSm()
                            ->fontBold()
                            ->primary(),
                        Body\Table::make()
                            ->columns([
                                Body\TextColumn::make("name")
                                ->label(''),
                                // Body\TextColumn::make("kode"),
                                Body\TextColumn::make("jumlah")
                                ->money('IDR')
                                ->alignRight(),
                            ])
                            ->data(
                                function (?array $filters) {
                                    $startDate = $filters['start'] ?? now()->startOfMonth();
                                    $endDate = $filters['end'] ?? now()->endOfMonth();

                                    $data = ModelsLabaRugi::getLabaKotor($startDate, $endDate);
                                    // dd($data);

                                    return collect($data);
                                }
                            ),
                        // VerticalSpace::make(),
                    ]),
                Body\Layout\BodyColumn::make()
                    ->schema([
                        Text::make("Beban Operasional")
                            ->fontSm()
                            ->fontBold()
                            ->primary(),
                        Body\Table::make()
                            ->columns([
                                Body\TextColumn::make("name")
                                ->label('Akun'),
                                // Body\TextColumn::make("kode"),
                                Body\TextColumn::make("jumlah")
                                ->money('IDR')
                                ->alignRight()
                                ->sum(),
                            ])
                            ->data(
                                function (?array $filters) {
                                    $startDate = $filters['start'] ?? now()->startOfMonth();
                                    $endDate = $filters['end'] ?? now()->endOfMonth();

                                    $data = ModelsLabaRugi::getBebanOperasional($startDate, $endDate);
                                    // dd($data);

                                    return collect($data);
                                }
                            ),
                        // VerticalSpace::make(),
                    ]),
                Body\Layout\BodyColumn::make()
                    ->schema([
                        Text::make("Laba Bersih")
                            ->fontSm()
                            ->fontBold()
                            ->primary(),
                        Body\Table::make()
                            ->columns([
                                Body\TextColumn::make("name")
                                ->label(''),
                                // Body\TextColumn::make("kode"),
                                Body\TextColumn::make("jumlah")
                                ->money('IDR')
                                ->alignRight(),
                            ])
                            ->data(
                                function (?array $filters) {
                                    $startDate = $filters['start'] ?? now()->startOfMonth();
                                    $endDate = $filters['end'] ?? now()->endOfMonth();

                                    $data = ModelsLabaRugi::getLabaOperasional($startDate, $endDate);
                                    // dd($data);

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
                DatePicker::make('start')
                ->label('Mulai Tanggal')
                ->default(now()->startOfMonth()), // Default ke 1 bulan terakhir
                DatePicker::make('end')
                ->label('Sampai Tanggal')
                ->default(now()->endOfMonth())
            ]);
    }
}
