<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Stock Ledgers (Kartu Stok Detail)
        Schema::create('stock_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->date('transaction_date');
            $table->string('reference_type'); // 'GRN', 'DELIVERY_ORDER', 'ADJUSTMENT', 'TRANSFER', 'OPENING_BALANCE'
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_no')->nullable();
            $table->enum('type', ['in', 'out']);
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->decimal('current_stock_after', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 2. Stock Adjustments (Penyesuaian / Stock Opname)
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('adjustment_number')->unique();
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('restrict');
            $table->date('adjustment_date');
            $table->enum('status', ['draft', 'posted'])->default('draft');
            $table->text('reason')->nullable();
            $table->foreignId('adjusted_by_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('journal_id')->nullable()->constrained('journals')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('stock_adjustment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_adjustment_id')->constrained('stock_adjustments')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items')->onDelete('restrict');
            $table->decimal('system_stock', 12, 2);
            $table->decimal('physical_stock', 12, 2);
            $table->decimal('difference_qty', 12, 2); // physical - system
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0); // difference_qty * unit_cost
            $table->timestamps();
        });

        // 3. Stock Transfers (Transfer Antar Gudang)
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number')->unique();
            $table->foreignId('from_warehouse_id')->constrained('warehouses')->onDelete('restrict');
            $table->foreignId('to_warehouse_id')->constrained('warehouses')->onDelete('restrict');
            $table->date('transfer_date');
            $table->enum('status', ['draft', 'completed'])->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by_id')->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });

        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_transfer_id')->constrained('stock_transfers')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items')->onDelete('restrict');
            $table->decimal('quantity', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_items');
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('stock_adjustment_items');
        Schema::dropIfExists('stock_adjustments');
        Schema::dropIfExists('stock_ledgers');
    }
};
