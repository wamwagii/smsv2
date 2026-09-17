<?php

namespace App\Filament\Resources\Payments;

use App\Filament\Resources\Payments\Pages\CreatePayment;
use App\Filament\Resources\Payments\Pages\EditPayment;
use App\Filament\Resources\Payments\Pages\ListPayments;
use App\Filament\Resources\Payments\Pages\ViewPayment;
use App\Filament\Resources\Payments\Schemas\PaymentForm;
use App\Filament\Resources\Payments\Schemas\PaymentInfolist;
use App\Filament\Resources\Payments\Tables\PaymentsTable;
use App\Models\Payment;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CurrencyDollar;

    protected static string|\UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'receipt_number';

    public static function form(Schema $schema): Schema
    {
        return PaymentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PaymentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaymentsTable::configure($table);
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
            'index'  => ListPayments::route('/'),
            'create' => CreatePayment::route('/create'),
            'view'   => ViewPayment::route('/{record}'),
            'edit'   => EditPayment::route('/{record}/edit'),
        ];
    }

    /* -----------------------------------------------------------------
     |  Global search
     | -----------------------------------------------------------------
     */

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'receipt_number',
            'mpesa_receipt',
            'transaction_reference',
        ];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return 'Receipt ' . ($record->receipt_number ?? $record->id);
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Student' => $record->student?->full_name ?? '-',
            'Amount'  => 'KES ' . number_format((float) $record->amount, 2),
            'Status'  => ucfirst($record->status ?? '-'),
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
        $pending = static::getModel()::where('status', 'pending')->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    /* -----------------------------------------------------------------
     |  Authorization
     | -----------------------------------------------------------------
     */

    public static function canDelete(Model $record): bool
    {
        if (!User::currentIsAdmin()) {
            return false;
        }

        return $record->canBeDeleted();
    }

    public static function canDeleteAny(): bool
    {
        return User::currentIsAdmin();
    }
}