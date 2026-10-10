<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Alter item_categories and items item_type column to varchar for flexibility (MySQL)
        if (DB::getDriverName() === 'mysql') {
            if (Schema::hasColumn('items', 'item_type')) {
                DB::statement("ALTER TABLE items MODIFY COLUMN item_type VARCHAR(50) DEFAULT 'finished_good'");
            }
            if (Schema::hasColumn('item_categories', 'type')) {
                DB::statement("ALTER TABLE item_categories MODIFY COLUMN type VARCHAR(50) DEFAULT 'finished_good'");
            }
        }

        // 2. Item Unit Conversions (Multi Satuan: 1 BALL = 20 DUS = 480 PCS)
        Schema::create('item_unit_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->foreignId('unit_id')->constrained('units')->onDelete('restrict');
            $table->decimal('conversion_factor', 14, 4)->default(1); // multiplier to base unit
            $table->decimal('selling_price_override', 15, 2)->nullable();
            $table->decimal('purchase_price_override', 15, 2)->nullable();
            $table->string('barcode')->nullable();
            $table->timestamps();

            $table->unique(['item_id', 'unit_id'], 'item_unit_conv_unique');
        });

        // 3. Item Warehouse Prices & Limits (Harga & HPP Berbeda per Cabang/Gudang)
        Schema::create('item_warehouse_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->decimal('selling_price', 15, 2)->nullable();
            $table->decimal('purchase_price', 15, 2)->nullable();
            $table->decimal('min_stock', 12, 2)->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->unique(['item_id', 'warehouse_id'], 'item_wh_price_unique');
        });

        // 4. BOM (Bill of Materials) for Bundles & Manufacturing
        Schema::create('boms', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade'); // The produced or bundle item
            $table->decimal('yield_quantity', 12, 4)->default(1);
            $table->decimal('overhead_cost', 15, 2)->default(0);
            $table->decimal('labor_cost', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('bom_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bom_id')->constrained('boms')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items')->onDelete('restrict'); // Material item
            $table->foreignId('unit_id')->nullable()->constrained('units')->onDelete('set null');
            $table->decimal('quantity', 14, 4)->default(1);
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->timestamps();
        });

        // 5. Manufacturing Routings & Steps (Multi-Step Production: A -> B -> C -> D -> E)
        Schema::create('routings', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('routing_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('routing_id')->constrained('routings')->onDelete('cascade');
            $table->integer('step_number')->default(1);
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_final_step')->default(false);
            $table->timestamps();
        });

        // 6. Work Orders (Surat Perintah Kerja Produksi)
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('wo_number')->unique();
            $table->foreignId('item_id')->constrained('items')->onDelete('restrict');
            $table->foreignId('bom_id')->constrained('boms')->onDelete('restrict');
            $table->foreignId('routing_id')->nullable()->constrained('routings')->onDelete('set null');
            $table->foreignId('current_step_id')->nullable()->constrained('routing_steps')->onDelete('set null');
            $table->decimal('target_quantity', 12, 4);
            $table->decimal('produced_quantity', 12, 4)->default(0);
            $table->foreignId('raw_material_warehouse_id')->constrained('warehouses')->onDelete('restrict');
            $table->foreignId('finished_good_warehouse_id')->constrained('warehouses')->onDelete('restrict');
            $table->date('start_date');
            $table->date('due_date')->nullable();
            $table->string('status', 30)->default('draft'); // draft, in_progress, completed, cancelled
            $table->foreignId('journal_id')->nullable()->constrained('journals')->onDelete('set null');
            $table->foreignId('created_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 7. Contact Deposits (Deposit Customer / Pelanggan & Supplier)
        Schema::create('contact_deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained('contacts')->onDelete('cascade');
            $table->string('type', 20)->default('customer'); // customer, supplier
            $table->decimal('amount', 15, 2);
            $table->string('transaction_type', 10); // in, out
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_no')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('journal_id')->nullable()->constrained('journals')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_deposits');
        Schema::dropIfExists('work_orders');
        Schema::dropIfExists('routing_steps');
        Schema::dropIfExists('routings');
        Schema::dropIfExists('bom_items');
        Schema::dropIfExists('boms');
        Schema::dropIfExists('item_warehouse_prices');
        Schema::dropIfExists('item_unit_conversions');
    }
};
