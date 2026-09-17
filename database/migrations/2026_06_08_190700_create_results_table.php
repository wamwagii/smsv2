<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('results', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained()->restrictOnDelete();
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->foreignId('class_id')->constrained()->restrictOnDelete();

            $table->decimal('marks_obtained', 5, 2);
            $table->integer('total_marks')->default(100);
            $table->decimal('percentage', 5, 2)->nullable(); // computed in model
            $table->string('grade', 5)->nullable();
            $table->text('teacher_comments')->nullable();
            $table->json('assessment_breakdown')->nullable();

            $table->timestamps();

            $table->unique(['student_id', 'exam_id', 'subject_id'], 'unique_student_exam_subject');
            $table->index('student_id');
            $table->index('exam_id');
            $table->index('subject_id');
            $table->index(['class_id', 'exam_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};