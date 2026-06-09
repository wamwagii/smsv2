<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Mathematics, English, Kiswahili
            $table->string('code', 10)->unique(); // MAT, ENG, KIS
            $table->foreignId('department_id')->constrained('departments');
            $table->enum('category', ['core', 'elective', 'extra_curricular']);
            $table->integer('theory_hours_per_week')->default(0);
            $table->integer('practical_hours_per_week')->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('subjects');
    }
};