<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Resources\Pages\EditRecord;

class BaseEditRecord extends EditRecord
{
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); // Redirect ke list setelah edit
    }
}