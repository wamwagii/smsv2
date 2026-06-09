<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Grade 1, Grade 2, ... Grade 12
            $table->integer('level'); // 1,2,3...12
            $table->enum('stream', ['A', 'B', 'C', 'D', 'E'])->nullable(); // For classes with multiple streams
            $table->string('class_code', 20)->unique(); // 1A, 2B, 3C
            $table->integer('capacity')->default(45);
            $table->integer('current_enrollment')->default(0);
            $table->string('class_teacher_id')->nullable(); // FK to staff
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['level', 'stream']);
            $table->index('class_code');
        });
    }

    public function down()
    {
        Schema::dropIfExists('classes');
    }
};