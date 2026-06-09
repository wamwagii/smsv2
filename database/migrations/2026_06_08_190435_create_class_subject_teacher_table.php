<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('class_subject_teacher', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('staff_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years');
            $table->boolean('is_class_teacher')->default(false);
            $table->timestamps();
            
            $table->unique(['class_id', 'subject_id', 'academic_year_id'], 'unique_class_subject_year');
            $table->index('staff_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('class_subject_teacher');
    }
};