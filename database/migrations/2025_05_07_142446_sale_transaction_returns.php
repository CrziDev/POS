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
        Schema::create('sale_transaction_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_transaction_id')->constrained('sale_transactions')->onDelete('cascade');
            $table->foreignId('sale_transaction_item_id')->constrained('sale_transaction_items');
            $table->foreignId('branch_id')->constrained('branches');
            $table->timestamp('returned_at')->nullable();
            $table->foreignId('handled_by')->constrained('employees');
            $table->foreignId('returned_item_id')->constrained('supplies');
            $table->string('issue_type');
            $table->integer('quantity')->default(1);
            $table->decimal('price_sold',16,2)->default(1);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('sale_transaction_return_replacements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_id')->constrained('sale_transaction_returns');
            $table->foreignId('replacement_item_id')->nullable()->constrained('supplies');
            $table->integer('replacement_quantity')->default(0);
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_transaction_returns');
        Schema::dropIfExists('sale_transaction_return_items');
    }
};
