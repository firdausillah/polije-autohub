<?php

namespace App\Filament\Resources\ServiceScheduleResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Guava\FilamentModalRelationManagers\Concerns\CanBeEmbeddedInModals;
use Illuminate\Database\Eloquent\Model;

class ServiceDChecklistRelationManager extends RelationManager
{
    use CanBeEmbeddedInModals;
    protected static string $relationship = 'ServiceDChecklist';

    protected static ?string $title = 'Checklist';
    protected static ?string $pluralLabel = 'Checklist';
    protected static ?string $modelLabel = 'Checklist';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        // Jika modal, tetap izinkan akses
        if (request()->boolean('embedded')) {
            return true;
        }

        // Hanya 'admin' yang bisa melihat di tampilan utama
        return auth()->user()->hasRole('admin');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('checklist.name'),
                Tables\Columns\CheckboxColumn::make('checklist_hasil'),
                Tables\Columns\TextInputColumn::make('keterangan'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }


}
