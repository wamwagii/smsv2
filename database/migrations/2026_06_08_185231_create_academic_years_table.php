<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // 2024, 2025
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_current')->default(false);
            $table->enum('status', ['active', 'archived'])->default('active');
            $table->timestamps();
            
            $table->index('is_current');
            $table->unique('name');
        });
    }

    public function down()
    {
        Schema::dropIfExists('academic_years');
    }
};