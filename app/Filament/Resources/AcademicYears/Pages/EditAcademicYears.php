<?php

namespace App\Filament\Resources\AcademicYears\Pages;

use App\Filament\Resources\AcademicYears\AcademicYearsResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAcademicYears extends EditRecord
{
    protected static string $resource = AcademicYearsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

        // Redirect to the list page after editing an academic year
    protected function getRedirectUrl(): string
    {        
        
        return $this->getResource()::getUrl('index');

    }
}
