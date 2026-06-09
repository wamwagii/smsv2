<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{
    use SoftDeletes;
    
    protected $table = 'staff';
    
    protected $fillable = [
        'staff_number',
        'first_name',
        'middle_name',
        'last_name',
        'date_of_birth',
        'gender',
        'photo',
        'phone_number',
        'email',
        'physical_address',
        'employment_type',
        'hire_date',
        'contract_end_date',
        'tsc_number',
        'national_id',
        'kra_pin',
        'nhif_number',
        'nssf_number',
        'qualification',
        'subjects_taught',
        'certifications',
        'bank_name',
        'bank_branch',
        'account_number',
        'position',
        'department_id',
        'role',
        'status',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relation',
    ];
    
    protected $casts = [
        'date_of_birth' => 'date',
        'hire_date' => 'date',
        'contract_end_date' => 'date',
        'subjects_taught' => 'array',
        'certifications' => 'array',
    ];
    
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}