<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('timetable_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained();
            $table->foreignId('subject_id')->constrained();
            $table->foreignId('staff_id')->constrained();
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday']);
            $table->time('start_time');
            $table->time('end_time');
            $table->string('room_number', 20)->nullable();
            $table->enum('term', ['term_1', 'term_2', 'term_3']);
            $table->foreignId('academic_year_id')->constrained('academic_years');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['class_id', 'day_of_week']);
            $table->index('staff_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('timetable_entries');
    }
};
