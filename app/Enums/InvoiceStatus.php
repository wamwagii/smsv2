<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Pending        = 'pending';
    case PartiallyPaid  = 'partially_paid';
    case Paid           = 'paid';
    case Overdue        = 'overdue';
    case Waived         = 'waived';
    case Cancelled      = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending       => 'Pending',
            self::PartiallyPaid => 'Partially Paid',
            self::Paid          => 'Paid',
            self::Overdue       => 'Overdue',
            self::Waived        => 'Waived',
            self::Cancelled     => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending       => 'gray',
            self::PartiallyPaid => 'warning',
            self::Paid          => 'success',
            self::Overdue       => 'danger',
            self::Waived        => 'info',
            self::Cancelled     => 'gray',
        };
    }

    /**
     * Statuses that mean "the school is no longer expecting payment".
     */
    public static function writtenOff(): array
    {
        return [
            self::Waived->value,
            self::Cancelled->value,
        ];
    }
}