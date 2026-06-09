<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('idempotency_key')->unique(); // For duplicate prevention
            $table->foreignId('invoice_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('student_id')->constrained();
            $table->foreignId('parent_id')->nullable()->constrained('parents');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['mpesa', 'bank_transfer', 'cash', 'cheque', 'card']);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'refunded'])->default('pending');
            
            // M-Pesa Specific Fields
            $table->string('mpesa_receipt', 50)->nullable()->unique();
            $table->string('checkout_request_id', 100)->nullable();
            $table->string('merchant_request_id', 100)->nullable();
            
            // Bank/Card Specific
            $table->string('transaction_reference', 100)->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->string('card_last_four', 4)->nullable();
            
            // Payment Details
            $table->date('payment_date');
            $table->time('payment_time')->nullable();
            $table->text('notes')->nullable();
            $table->json('gateway_response')->nullable(); // Raw response from M-Pesa/Bank
            
            // Receipt
            $table->string('receipt_number', 50)->nullable()->unique();
            $table->string('receipt_path')->nullable(); // PDF receipt path
            
            $table->timestamps();
            
            $table->index('invoice_id');
            $table->index('student_id');
            $table->index('mpesa_receipt');
            $table->index('status');
            $table->index('payment_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
