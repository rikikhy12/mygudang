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
        if (! Schema::hasTable('item_branch_prices')) {
            Schema::create('item_branch_prices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
                $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
                $table->decimal('selling_price', 15, 2)->nullable();
                $table->decimal('purchase_price', 15, 2)->nullable();
                $table->decimal('min_margin_percent', 8, 2)->nullable();
                $table->decimal('min_stock', 12, 2)->nullable();
                $table->boolean('is_available')->default(true);
                $table->timestamps();

                $table->unique(['item_id', 'branch_id'], 'item_branch_price_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_branch_prices');
    }
};
