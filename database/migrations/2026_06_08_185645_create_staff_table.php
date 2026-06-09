<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->string('staff_number', 50)->unique(); // TCH/2024/001
            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100);
            $table->date('date_of_birth');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->string('photo')->nullable(); // Path to photo
            
            // Contact Information
            $table->string('phone_number', 15)->unique();
            $table->string('email', 100)->unique();
            $table->text('physical_address')->nullable();
            
            // Employment Details
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'temporary']);
            $table->date('hire_date');
            $table->date('contract_end_date')->nullable();
            $table->string('tsc_number', 50)->nullable(); // Teachers Service Commission Number
            $table->string('national_id', 20)->unique();
            $table->string('kra_pin', 20)->unique()->nullable(); // KRA PIN
            $table->string('nhif_number', 20)->nullable();
            $table->string('nssf_number', 20)->nullable();
            
            // Professional Details
            $table->string('qualification')->nullable(); // B.Ed, Diploma, etc.
            $table->json('subjects_taught')->nullable(); // ['Mathematics', 'English']
            $table->json('certifications')->nullable(); // ['First Aid', 'CPD']
            
            // Bank Details
            $table->string('bank_name', 100)->nullable();
            $table->string('bank_branch', 100)->nullable();
            $table->string('account_number', 50)->nullable();
            
            // Position & Role
            $table->string('position')->nullable(); // Class Teacher, HOD, Deputy Principal
            $table->foreignId('department_id')->nullable()->constrained('departments');
            $table->enum('role', ['teacher', 'admin', 'accountant', 'librarian', 'support', 'management']);
            
            // Status
            $table->enum('status', ['active', 'on_leave', 'suspended', 'resigned', 'terminated'])->default('active');
            
            // Emergency Contact
            $table->string('emergency_contact_name', 100)->nullable();
            $table->string('emergency_contact_phone', 15)->nullable();
            $table->string('emergency_contact_relation', 50)->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('staff_number');
            $table->index('email');
            $table->index('phone_number');
            $table->index('status');
            $table->index('role');
        });
    }

    public function down()
    {
        Schema::dropIfExists('staff');
    }
};
