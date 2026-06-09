<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->decimal('marks_obtained', 5, 2);
            $table->integer('total_marks')->default(100);
            $table->decimal('percentage', 5, 2)->storedAs('(marks_obtained / total_marks) * 100');
            $table->string('grade')->nullable(); // A, B+, B, etc.
            $table->text('teacher_comments')->nullable();
            $table->json('assessment_breakdown')->nullable(); // [{'cat1': 15}, {'cat2': 18}, {'exam': 62}]
            $table->timestamps();
            
            $table->unique(['student_id', 'exam_id', 'subject_id'], 'unique_student_exam_subject');
            $table->index('student_id');
            $table->index('exam_id');
            $table->index('subject_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('results');
    }
};