<?php

namespace App\Filament\Resources\Staff\Pages;

use App\Filament\Resources\Staff\StaffResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStaff extends CreateRecord
{
    protected static string $resource = StaffResource::class;

    // Redirect to the staff list page after creating a new staff member
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
