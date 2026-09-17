<?php

namespace App\Filament\Resources\Invoices;

use App\Filament\Resources\Invoices\Pages\CreateInvoice;
use App\Filament\Resources\Invoices\Pages\EditInvoice;
use App\Filament\Resources\Invoices\Pages\ListInvoices;
use App\Filament\Resources\Invoices\Pages\ViewInvoice;
use App\Filament\Resources\Invoices\Schemas\InvoiceForm;
use App\Filament\Resources\Invoices\Schemas\InvoiceInfolist;
use App\Filament\Resources\Invoices\Tables\InvoicesTable;
use App\Models\Invoice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentText;

    protected static string|\UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'invoice_number';

    public static function form(Schema $schema): Schema
    {
        return InvoiceForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return InvoiceInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InvoicesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListInvoices::route('/'),
            'create' => CreateInvoice::route('/create'),
            'view'   => ViewInvoice::route('/{record}'),
            'edit'   => EditInvoice::route('/{record}/edit'),
        ];
    }

    /* -----------------------------------------------------------------
     |  Global search
     | -----------------------------------------------------------------
     */

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'invoice_number',
            'student.admission_number',
            'student.first_name',
            'student.last_name',
        ];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->invoice_number ?? ('Invoice #' . $record->id);
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Student' => $record->student?->full_name ?? '-',
            'Amount'  => 'KES ' . number_format((float) $record->amount, 2),
            'Status'  => ucfirst(str_replace('_', ' ', $record->status ?? '-')),
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()
            ->with('student');
    }

    /* -----------------------------------------------------------------
     |  Navigation
     | -----------------------------------------------------------------
     */

    public static function getNavigationBadge(): ?string
    {
        $overdue = static::getModel()::where('status', '!=', 'paid')
            ->whereDate('due_date', '<', today())
            ->count();

        return $overdue > 0 ? (string) $overdue : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}