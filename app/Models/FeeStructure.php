<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeStructure extends Model
{
    protected $table = 'fee_structures';
    
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
        'is_active',
        'payment_plan',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'payment_plan' => 'array', // Cast payment_plan to array for easy access
        'tuition_fees' => 'decimal:2',
        'activity_fees' => 'decimal:2',
        'library_fees' => 'decimal:2',
        'sports_fees' => 'decimal:2',
        'medical_fees' => 'decimal:2',
        'transport_fees' => 'decimal:2',
        'boarding_fees' => 'decimal:2',
        'uniform_fees' => 'decimal:2',
        'other_fees' => 'decimal:2',
    ];
    
    // Accessor for payment plan count
    public function getPaymentPlanCountAttribute()
    {
        $plan = $this->payment_plan;
        return is_array($plan) ? count($plan) : 0;
    }
    
    // Mutator to ensure payment_plan is always stored as JSON
    public function setPaymentPlanAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['payment_plan'] = json_encode($value);
        } else {
            $this->attributes['payment_plan'] = $value;
        }
    }
    
    public function class()
    {
        return $this->belongsTo(Classes::class);
    }
    
    public function academicYear()
    {
        return $this->belongsTo(AcademicYears::class);
    }
}