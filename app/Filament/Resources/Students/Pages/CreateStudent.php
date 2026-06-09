<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Resources\Students\StudentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;


    // Redirect to the student list page after creating a new student
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
