<?php

namespace App\Filament\Resources\CashFlowResource\RelationManagers;

use App\Models\Account;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CashDFlowRelationManager extends RelationManager
{
    protected static string $relationship = 'CashDFlow';

    protected static ?string $title = 'Detail Akun';
    protected static ?string $pluralLabel = 'Detail Akun';
    protected static ?string $modelLabel = 'Detail Akun';

    public function form(Form $form): Form
    {
        return $form
            ->schema([Select::make('account_id')
                ->relationship('account', 'name')
                ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->kode} ({$record->type}) - {$record->name}")
                ->searchable()
                ->preload()
                ->label('Akun')
                ->required()
                ->live()
                ->afterStateUpdated(function (Set $set, $state) {
                $kredit = Account::find($state);
                    $set('account_name', $kredit->name);
                    $set('account_kode', $kredit->kode);
                }),
            Hidden::make('account_name')
                ->required(),
            Hidden::make('account_kode')
                ->required(),
            TextInput::make('jumlah')
                ->prefix('Rp ')
                ->numeric()
                ->required(),
            Textarea::make('keterangan'),
            FileUpload::make('photo')
                ->image()
                ->resize(50),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('account_name')
            ->columns([
                Tables\Columns\TextColumn::make('account_name'),
                Tables\Columns\TextColumn::make('jumlah')
                ->summarize(
                    Sum::make()
                        ->money('IDR', locale: 'id_ID')
                        ->label('Total')
                )
                ->money('IDR', locale: 'id_ID')
                ->label('Total'),
                Tables\Columns\TextColumn::make('keterangan'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->hidden(fn () => $this->getOwnerRecord()->is_approve === 'approved'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->hidden(fn () => $this->getOwnerRecord()->is_approve === 'approved'),
                Tables\Actions\DeleteAction::make()->hidden(fn () => $this->getOwnerRecord()->is_approve === 'approved'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
