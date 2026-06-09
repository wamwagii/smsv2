<?php

namespace App\Filament\Exports;

use App\Models\Student;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class StudentExporter extends Exporter
{
    protected static ?string $model = Student::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('admission_number'),
            ExportColumn::make('first_name'),
            ExportColumn::make('middle_name'),
            ExportColumn::make('last_name'),
            ExportColumn::make('date_of_birth'),
            ExportColumn::make('gender'),
            ExportColumn::make('photo'),
            ExportColumn::make('birth_certificate_number'),
            ExportColumn::make('phone_number'),
            ExportColumn::make('email'),
            ExportColumn::make('physical_address'),
            ExportColumn::make('class_id'),
            ExportColumn::make('academic_year_id'),
            ExportColumn::make('roll_number'),
            ExportColumn::make('kcpse_index_number'),
            ExportColumn::make('kcpe_grade'),
            ExportColumn::make('kcpe_score'),
            ExportColumn::make('father_name'),
            ExportColumn::make('father_phone'),
            ExportColumn::make('mother_name'),
            ExportColumn::make('mother_phone'),
            ExportColumn::make('guardian_name'),
            ExportColumn::make('guardian_phone'),
            ExportColumn::make('guardian_relation'),
            ExportColumn::make('status'),
            ExportColumn::make('enrollment_date'),
            ExportColumn::make('graduation_date'),
            ExportColumn::make('medical_notes'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
            ExportColumn::make('deleted_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your student export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
