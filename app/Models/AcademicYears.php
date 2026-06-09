<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYears extends Model
{
    protected $table = 'academic_years';
    
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'status',
        'is_current',
    ];
    
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_current' => 'boolean',
    ];
    
    // Add this relationship
    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'academic_year_id');
    }
}