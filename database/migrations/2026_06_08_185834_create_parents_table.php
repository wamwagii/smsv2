<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('parents', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('phone_number', 15)->unique();
            $table->string('email', 100)->unique();
            $table->string('password');
            $table->string('national_id', 20)->unique()->nullable();
            $table->text('physical_address')->nullable();
            $table->enum('relationship', ['father', 'mother', 'guardian', 'other']);
            $table->string('occupation')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->rememberToken();
            $table->timestamps();
            
            $table->index('phone_number');
            $table->index('email');
        });
    }

    public function down()
    {
        Schema::dropIfExists('parents');
    }
};
