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
        Schema::create('supply_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name',100);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('supply_units', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('supplies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('supply_categories');
            $table->foreignId('unit_id')->nullable()->constrained('supply_units');
            $table->string('name')->unique();
            $table->string('item_image')->nullable();
            $table->string('sku')->nullable();
            $table->decimal('price',16,2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplies');
    }
};
