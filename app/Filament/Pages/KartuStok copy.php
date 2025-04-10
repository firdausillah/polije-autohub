<?php

namespace App\Filament\Pages;

use App\Models\Account;
use App\Models\JsonDataCoba;
use App\Models\KartuStok as ModelsKartuStok;
use App\Models\Sparepart;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Carbon\Carbon;
use DeepCopy\Filter\Filter as FilterFilter;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Tables;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PHPUnit\Event\TestSuite\Filtered;

class KartuStok extends Page implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.kartu-stok';

    protected static ?string $navigationGroup = 'Laporan';

    public array $data = [];

    public function table(Table $table): Table
    {

        // ->query(fn () => ModelsKartuStok::getLaporanByTanggal())
        // dd(ModelsKartuStok::query());
        return $table
        ->query(
            KartuStok::query()
        )
        ->columns([
            Tables\Columns\TextColumn::make('kode'),
            Tables\Columns\TextColumn::make('sparepart_name'),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('sparepart_id')
                ->label('Filter Kategori')
                ->options(\App\Models\Sparepart::pluck('name', 'id'))
                ->searchable()
        ])
        ->paginated(true);
    }

    // public function
    // {
    //     $data = $this->getTableQuery()->get();
    //     return view(self::$view, ['data' => $data]);
    // }



    public function getTableQuery()
    {
        $query = DB::table('inventories')
        ->select('kode', 'sparepart_name', 'tanggal_transaksi');
        // ->when($this->filters['date_range'], function ($query) {
        //     $query->whereBetween('date_column', [
        //         $this->filters['date_range']['start'],
        //         $this->filters['date_range']['end']
        //     ]);
        // });

        // Kamu bisa menambahkan filter select lainnya di sini jika diperlukan
        if (isset($this->filters['select_filter'])) {
            $query->where('select_column', $this->filters['select_filter']);
        }

        return $query;
    }

    public function getFilters(): array
    {
        return [
            Select::make('id_sparepart')
                ->label('Pilih Sparepart')
                ->options(function () {
                    return DB::table('spareparts')
                        ->pluck('name', 'id')
                        ->toArray();
                })
                ->searchable(),
        ];
    }

    public function mount(): void
    {
        $this->data = \App\Models\Jurnal::all()->toArray();
    }
}
