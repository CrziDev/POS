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
        Schema::create('returned_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_transaction_id')->constrained('sale_transactions')->onDelete('cascade');
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('handled_by')->constrained('employees');
            $table->timestamp('returned_date')->nullable();
            $table->text('remarks')->nullable();
            $table->string('status')->default('Pending');
            $table->timestamps();
        });
    
        Schema::create('return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('returned_transaction_id')->constrained('returned_transactions')->onDelete('cascade');
            $table->foreignId('original_item_id')->constrained('sale_transaction_items');
            $table->foreignId('replacement_item_id')->nullable()->constrained('supplies');
            $table->integer('qty_returned');
            $table->integer('qty_replaced');
            $table->decimal('original_item_price', 16, 2);
            $table->decimal('replacement_item_price', 16, 2)->nullable();
            $table->decimal('value_difference', 16, 2);
            $table->string('issue');
            $table->boolean('is_saleble');
            $table->timestamps();
        });

        Schema::create('replacement_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('returned_transaction_id')->constrained('returned_transactions')->onDelete('cascade');
            $table->decimal('amount_paid', 16, 2);
            $table->string('payment_method'); 
            $table->string('reference_no')->nullable(); 
            $table->string('date_paid')->nullable(); 
            $table->foreignId('received_by')->constrained('users');
            $table->timestamps();
        });

    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_transactions');
        Schema::dropIfExists('return_items');
    }
};
