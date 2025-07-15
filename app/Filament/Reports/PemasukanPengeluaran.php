<?php

namespace App\Filament\Reports;

use App\Models\Account;
use App\Models\LabaRugi;
use App\Models\PemasukanPengeluaran as ModelsPemasukanPengeluaran;
use Carbon\Carbon;
use EightyNine\Reports\Report;
use EightyNine\Reports\Components\Body;
use EightyNine\Reports\Components\Footer;
use EightyNine\Reports\Components\Header;
use EightyNine\Reports\Components\Text;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;

class PemasukanPengeluaran extends Report
{
    // public ?string $heading = "Report";

    // public ?string $subHeading = "A great report";

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->hasRole(['super_admin', 'Manager', 'Admin', 'pimpinan']);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function getFilter()
    {
        return 'Periode: ' . \Carbon\Carbon::parse($this->data['start'])->translatedFormat('d F Y') . ' - ' . \Carbon\Carbon::parse($this->data['end'])->translatedFormat('d F Y');
    }


    public function getAccountName()
    {

        $account = isset($this->data['account_id']) ? Account::find($this->data['account_id'])->name : 'Akun Belum Dipilih';

        $periode = 'Periode: ' . \Carbon\Carbon::parse($this->data['start'])->translatedFormat('d F Y') . ' - ' . \Carbon\Carbon::parse($this->data['end'])->translatedFormat('d F Y');
        // dd(account::find($this->data['account_id'])->name);

        return ['account' => $account, 'periode' => $periode];
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
                        Text::make("Laporan Pemasukan & Pengeluaran")
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
            ->schema([Body\Layout\BodyColumn::make()
                ->schema([
                    Text::make($this->getAccountName()['account'])
                        ->fontXl()
                        ->fontBold()
                        ->primary(),
                    Body\Table::make()
                        ->columns([
                            Body\TextColumn::make("tanggal_transaksi")
                            ->label('Tanggal')
                            ->dateTime('d/M/Y'),
                            // Body\TextColumn::make("account_name")
                            //     ->label('Akun'),
                            Body\TextColumn::make("kode"),
                            Body\TextColumn::make("transaction_type")
                            ->label("Tipe Transaksi"),
                            Body\TextColumn::make("debit")
                                ->money('IDR')
                                ->alignRight(),
                                // ->sum(),
                            Body\TextColumn::make("kredit")
                                ->money('IDR')
                                ->alignRight(),
                                // ->sum(),
                            Body\TextColumn::make("saldo")
                                ->money('IDR')
                                ->alignRight(),
                                // ->sum(),
                        ])
                        ->data(
                            function (?array $filters) {
                                $accountId = $filters['account_id']??null;
                                $startDate = $filters['start'] ?? now()->startOfMonth();
                                $endDate = $filters['end'] ?? now()->endOfMonth();

                                $data = ModelsPemasukanPengeluaran::getLaporanByTanggal($accountId, $startDate, Carbon::parse($endDate)->addDay()->toDateString());

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
                Select::make('account_id')
                ->label('Akun')
                // ->searchable()
                ->options(function () {
                    return \App\Models\Account::whereIn('id', [1,2])->orderBy('name')->pluck('name', 'id');
                })
                ->preload(),
                DatePicker::make('start')
                    ->label('Mulai Tanggal')
                    ->default(now()->startOfMonth()), // Default ke 1 bulan terakhir
                DatePicker::make('end')
                    ->label('Sampai Tanggal')
                    ->default(now()->endOfMonth())
            ]);
    }
}
