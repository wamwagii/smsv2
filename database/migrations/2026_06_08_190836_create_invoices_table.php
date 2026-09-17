<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            $table->string('invoice_number', 50)->unique(); // e.g. INV/2024/0001

            $table->foreignId('student_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('fee_structure_id')
                ->constrained('fee_structures')
                ->restrictOnDelete();

            $table->enum('term', ['term_1', 'term_2', 'term_3']);
            $table->decimal('amount', 10, 2);
            $table->decimal('amount_paid', 10, 2)->default(0);

            // MySQL 5.7+ / MariaDB 10.2+ required
            $table->decimal('balance', 10, 2)
                ->storedAs('amount - amount_paid');

            $table->date('due_date');
            $table->enum('status', ['pending', 'partially_paid', 'paid', 'overdue', 'waived'])
                ->default('pending');
            $table->text('notes')->nullable();

            $table->timestamps();

            // Individual indexes
            $table->index('status');
            $table->index('due_date');
            $table->index('created_at');

            // Composite indexes for common queries
            $table->index(['student_id', 'status']);
            $table->index(['status', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};