<?php

use App\Enums\PurchaseOrderStatusEnums;
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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('prepared_by')->constrained('users');
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->decimal('total_amount',16,2)->default();
            $table->string('status')->default(PurchaseOrderStatusEnums::PENDING->value);
            $table->string('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders');
            $table->foreignId('supply_id')->constrained('supplies');
            $table->boolean('is_price_set')->nullable();
            $table->integer('quantity')->default(0);
            $table->decimal('price',16,2)->default(0);
            $table->decimal('total_amount',16,2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
