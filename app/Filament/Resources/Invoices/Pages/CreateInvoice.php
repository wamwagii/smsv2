<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Filament\Resources\Invoices\InvoiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    //Redirect to invoice page after creation
    protected function getRedirectUrl(): string

    {       
    
    return $this->getResource()::getUrl('index');
    
    }
}
