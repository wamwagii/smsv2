<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('student_parent', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_id')->constrained()->onDelete('cascade');
            $table->boolean('is_primary_contact')->default(false);
            $table->boolean('receives_notifications')->default(true);
            $table->timestamps();
            
            $table->unique(['student_id', 'parent_id']);
            $table->index('parent_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_parent');
    }
};