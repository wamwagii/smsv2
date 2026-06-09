<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained();
            $table->foreignId('academic_year_id')->constrained('academic_years');
            $table->decimal('tuition_fees', 10, 2);
            $table->decimal('activity_fees', 10, 2)->default(0);
            $table->decimal('library_fees', 10, 2)->default(0);
            $table->decimal('sports_fees', 10, 2)->default(0);
            $table->decimal('medical_fees', 10, 2)->default(0);
            $table->decimal('transport_fees', 10, 2)->default(0);
            $table->decimal('boarding_fees', 10, 2)->default(0);
            $table->decimal('uniform_fees', 10, 2)->default(0);
            $table->decimal('other_fees', 10, 2)->default(0);
            $table->decimal('total_fees', 10, 2)->storedAs('tuition_fees + activity_fees + library_fees + sports_fees + medical_fees + transport_fees + boarding_fees + uniform_fees + other_fees');
            $table->json('payment_plan')->nullable(); // [{'term':1,'due_date':'2024-03-15','amount':15000}]
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['class_id', 'academic_year_id'], 'unique_fees_per_class_year');
        });
    }

    public function down()
    {
        Schema::dropIfExists('fees_structures');
    }
};
