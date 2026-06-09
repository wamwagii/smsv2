<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeStructure extends Model
{
    protected $table = 'fee_structures'; // Make sure this matches your migration
    
    protected $fillable = [
        'class_id',
        'academic_year_id',
        'tuition_fees',
        'activity_fees',
        'library_fees',
        'sports_fees',
        'medical_fees',
        'transport_fees',
        'boarding_fees',
        'uniform_fees',
        'other_fees',
        'total_fees',
        'payment_plan',
        'is_active',
    ];
    
    protected $casts = [
        'payment_plan' => 'array',
        'is_active' => 'boolean',
        'tuition_fees' => 'decimal:2',
        'activity_fees' => 'decimal:2',
        'library_fees' => 'decimal:2',
        'sports_fees' => 'decimal:2',
        'medical_fees' => 'decimal:2',
        'transport_fees' => 'decimal:2',
        'boarding_fees' => 'decimal:2',
        'uniform_fees' => 'decimal:2',
        'other_fees' => 'decimal:2',
        'total_fees' => 'decimal:2',
    ];
    
    public function class()
    {
        return $this->belongsTo(Classes::class);
    }
    
    public function academicYear()
    {
        return $this->belongsTo(AcademicYears::class);
    }
}