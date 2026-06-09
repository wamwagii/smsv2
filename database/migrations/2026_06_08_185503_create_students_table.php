<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('admission_number', 50)->unique(); // ADM/2024/001
            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100);
            $table->date('date_of_birth');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->string('photo')->nullable(); // Path to photo
            $table->string('birth_certificate_number', 50)->nullable();
            
            // Contact Information
            $table->string('phone_number', 15)->nullable();
            $table->string('email', 100)->nullable()->unique();
            $table->text('physical_address')->nullable();
            
            // Academic Information
            $table->foreignId('class_id')->constrained('classes');
            $table->foreignId('academic_year_id')->constrained('academic_years');
            $table->string('roll_number', 50)->nullable();
            
            // Kenyan Specific Fields
            $table->string('kcpse_index_number', 20)->nullable(); // For candidates
            $table->enum('kcpe_grade', ['A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D+', 'D', 'D-', 'E'])->nullable();
            $table->integer('kcpe_score')->nullable();
            
            // Guardian Information
            $table->string('father_name', 100)->nullable();
            $table->string('father_phone', 15)->nullable();
            $table->string('mother_name', 100)->nullable();
            $table->string('mother_phone', 15)->nullable();
            $table->string('guardian_name', 100)->nullable();
            $table->string('guardian_phone', 15)->nullable();
            $table->string('guardian_relation')->nullable();
            
            // Status
            $table->enum('status', ['active', 'alumni', 'transferred', 'suspended', 'expelled'])->default('active');
            $table->date('enrollment_date');
            $table->date('graduation_date')->nullable();
            $table->text('medical_notes')->nullable(); // Allergies, conditions
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('admission_number');
            $table->index('class_id');
            $table->index('status');
            $table->index('gender');
        });
    }

    public function down()
    {
        Schema::dropIfExists('students');
    }
};
