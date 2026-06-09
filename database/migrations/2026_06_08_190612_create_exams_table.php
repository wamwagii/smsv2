<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // End of Term 1, Mid Term 2
            $table->enum('term', ['term_1', 'term_2', 'term_3']);
            $table->foreignId('academic_year_id')->constrained('academic_years');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('total_marks')->default(100);
            $table->integer('passing_marks')->default(50);
            $table->text('description')->nullable();
            $table->enum('status', ['upcoming', 'ongoing', 'completed', 'published'])->default('upcoming');
            $table->timestamps();
            
            $table->index(['academic_year_id', 'term']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('exams');
    }
};