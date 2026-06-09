<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $table = 'students';
    
    protected $fillable = [
        'admission_number',
        'first_name',
        'last_name',
        'middle_name',
        'date_of_birth',
        'gender',
        'photo',
        'birth_certificate_number',
        'phone_number',
        'email',
        'physical_address',
        'class_id',
        'academic_year_id',
        'roll_number',
        'kcpse_index_number',
        'kcpe_grade',
        'kcpe_score',
        'father_name',
        'father_phone',
        'mother_name',
        'mother_phone',
        'guardian_name',
        'guardian_phone',
        'guardian_relation',
        'status',
        'enrollment_date',
        'graduation_date',
        'medical_notes',
    ];
    
    protected $casts = [
        'date_of_birth' => 'date',
        'enrollment_date' => 'date',
        'graduation_date' => 'date',
    ];
    
    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }
    
    public function academicYear()
    {
        return $this->belongsTo(AcademicYears::class, 'academic_year_id');
    }

    public function results()
{
    return $this->hasMany(Result::class);
}

public function getAverageScoreAttribute()
{
    return $this->results()->avg('percentage');
}
}