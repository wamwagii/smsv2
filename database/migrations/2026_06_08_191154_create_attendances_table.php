<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained();
            $table->foreignId('class_id')->constrained();
            $table->date('date');
            $table->enum('status', ['present', 'absent', 'late', 'excused', 'holiday']);
            $table->time('arrival_time')->nullable();
            $table->time('departure_time')->nullable();
            $table->text('reason')->nullable(); // For absent/excused
            $table->foreignId('marked_by')->nullable()->constrained('staff');
            $table->timestamps();
            
            $table->unique(['student_id', 'date']);
            $table->index(['class_id', 'date']);
            $table->index('date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendances');
    }
};