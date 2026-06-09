<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 50)->unique(); // INV/2024/001
            $table->foreignId('student_id')->constrained();
            $table->foreignId('fee_structure_id')->constrained('fee_structures');
            $table->enum('term', ['term_1', 'term_2', 'term_3']);
            $table->decimal('amount', 10, 2);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('balance', 10, 2)->storedAs('amount - amount_paid');
            $table->date('due_date');
            $table->enum('status', ['pending', 'partially_paid', 'paid', 'overdue', 'waived'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('student_id');
            $table->index('status');
            $table->index('due_date');
            $table->index('invoice_number');
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoices');
    }
};