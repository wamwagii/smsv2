<?php

namespace App\Filament\Resources\Departments\Pages;

use App\Filament\Resources\Departments\DepartmentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDepartment extends CreateRecord
{
    protected static string $resource = DepartmentResource::class;

    // Redirect to the department list page after creating a new department
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');

    }
}
