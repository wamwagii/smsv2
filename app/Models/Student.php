<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use SoftDeletes;
    
    protected $table = 'students';
    
    protected $fillable = [
        'admission_number',
        'first_name',
        'middle_name',
        'last_name',
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
        'deleted_at' => 'datetime',
    ];
    
    public function class()
    {
        return $this->belongsTo(Classes::class);
    }
    
    public function academicYear()
    {
        return $this->belongsTo(AcademicYears::class);
    }
    
    public function results()
    {
        return $this->hasMany(Result::class);
    }
    
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
    
    // Use Guardian::class (not Parents::class) to match your setup
    public function parents()
    {
        return $this->belongsToMany(Guardian::class, 'student_parent', 'student_id', 'parent_id');
    }
    
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
    
    // Accessor for full name
    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . ($this->middle_name ? $this->middle_name . ' ' : '') . $this->last_name);
    }
    
    // Scope for active students
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}