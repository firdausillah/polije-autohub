<?php

namespace App\Helpers;

use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;

class ActionHelper
{
    public static function approveAction(): Action
    {
        return Action::make('approve')
            ->action(function ($record) {
                $record->is_approve = 1;
                $record->save();

                Notification::make()
                    ->title('Approved')
                    ->success()
                    ->body('Data has been approved.')
                    ->send();
            })
            ->color('info')
            ->requiresConfirmation()
            ->visible(fn ($record) => !$record->is_approve);
    }
}
