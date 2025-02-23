<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Resources\Pages\CreateRecord;

class BaseCreateRecord extends CreateRecord
{
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); // Redirect ke list setelah create
    }
}
