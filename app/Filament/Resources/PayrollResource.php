<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayrollResource\Pages;
use App\Filament\Resources\PayrollResource\RelationManagers;
use App\Models\Payroll;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Spatie\Permission\Models\Role;

class PayrollResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?string $navigationLabel = 'Payrolls';

    public static function getModelLabel(): string
    {
        return 'Payrolls'; // Ganti nama yang muncul di halaman resource
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                ->readOnly(),
                TextInput::make('gaji_pokok')
                ->prefix('Rp ')
                ->numeric(),
                TextInput::make('minimal_pendapatan_untuk_mendapat_gaji_pokok')
                ->prefix('Rp ')
                ->numeric(),
                TextInput::make('minimal_pendapatan_untuk_mendapat_bonus')
                ->prefix('Rp ')
                ->numeric(),
                TextInput::make('persentase_bonus')
                ->suffix('%')
                ->numeric(),
                Select::make('sumber_pendapatan')
                ->options([
                    "Penjualan Sparepart" => "Penjualan Sparepart",
                    "Jasa Service" => "Jasa Service"
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Role Name'),
                Tables\Columns\TextColumn::make('gaji_pokok')->money('IDR', locale: 'id_ID'),
                Tables\Columns\TextColumn::make('sumber_pendapatan'),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayrolls::route('/'),
            // 'create' => Pages\CreatePayroll::route('/create'), 
            'edit' => Pages\EditPayroll::route('/{record}/edit'),
        ];
    }
}
