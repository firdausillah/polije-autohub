<?php

namespace App\Filament\Resources\ServiceScheduleResource\RelationManagers;

use App\Models\Checklist;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Group;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServiceDChecklistRelationManager extends RelationManager
{
    protected static string $relationship = 'ServiceDChecklist';

    protected static ?string $title = 'Checklist';
    protected static ?string $pluralLabel = 'Checklist';
    protected static ?string $modelLabel = 'Checklist';

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
