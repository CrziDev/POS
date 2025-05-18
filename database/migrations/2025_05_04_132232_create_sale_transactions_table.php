<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sale_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('processed_by')->constrained('employees');
            $table->foreignId('branch_id')->constrained('branches');
            $table->decimal('discount_value',16,2)->default(0);
            $table->decimal('total_amount',16,2)->default(0);
            $table->date('transaction_date');
            $table->string('status')->default('Pending');
            $table->timestamps();
        });

        Schema::create('sale_transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_transaction_id')->constrained('sale_transactions');
            $table->foreignId('supply_id')->constrained('supplies');
            $table->decimal('original_price',16,2)->default(0);
            $table->decimal('price_amount',16,2)->default(0);
            $table->integer('returned_quantity')->default(0);
            $table->integer('quantity');
            $table->timestamps();
        });

        Schema::create('sale_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_transaction_id')->constrained('sale_transactions');
            $table->foreignId('processed_by')->constrained('employees');
            $table->string('payment_method');
            $table->string('payment_reference');
            $table->date('date_paid');
            $table->decimal('amount_paid',16,2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_transactions');
    }
};
