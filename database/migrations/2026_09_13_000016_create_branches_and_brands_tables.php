<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create branches (Cabang) table
        if (!Schema::hasTable('branches')) {
            Schema::create('branches', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('name');
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->text('address')->nullable();
                $table->boolean('is_main')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 2. Create brands (Merk) table
        if (!Schema::hasTable('brands')) {
            Schema::create('brands', function (Blueprint $table) {
                $table->id();
                $table->string('code')->nullable()->unique();
                $table->string('name');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 3. Add branch_id to warehouses
        if (Schema::hasTable('warehouses') && !Schema::hasColumn('warehouses', 'branch_id')) {
            Schema::table('warehouses', function (Blueprint $table) {
                $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->nullOnDelete();
            });
        }

        // 4. Add brand_id to items
        if (Schema::hasTable('items') && !Schema::hasColumn('items', 'brand_id')) {
            Schema::table('items', function (Blueprint $table) {
                $table->foreignId('brand_id')->nullable()->after('category_id')->constrained('brands')->nullOnDelete();
            });
        }

        // 5. Add branch_id to sales and procurement
        if (Schema::hasTable('sales_orders') && !Schema::hasColumn('sales_orders', 'branch_id')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->nullOnDelete();
            });
        }

        if (Schema::hasTable('sales_invoices') && !Schema::hasColumn('sales_invoices', 'branch_id')) {
            Schema::table('sales_invoices', function (Blueprint $table) {
                $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->nullOnDelete();
            });
        }

        if (Schema::hasTable('purchase_orders') && !Schema::hasColumn('purchase_orders', 'branch_id')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->nullOnDelete();
            });
        }

        // 6. Seed initial main branch & sample brand
        $mainBranchId = DB::table('branches')->insertGetId([
            'code' => 'CBG-001',
            'name' => 'Cabang Utama (Pusat)',
            'phone' => '021-5550100',
            'email' => 'pusat@myerp.local',
            'address' => 'Gedung Pusat Niaga Lt. 1-3, Jakarta',
            'is_main' => true,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('branches')->insert([
            [
                'code' => 'CBG-002',
                'name' => 'Cabang Surabaya',
                'phone' => '031-8880200',
                'email' => 'surabaya@myerp.local',
                'address' => 'Jl. Pemuda No. 45, Surabaya',
                'is_main' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'CBG-003',
                'name' => 'Cabang Bandung',
                'phone' => '022-7770300',
                'email' => 'bandung@myerp.local',
                'address' => 'Jl. Asia Afrika No. 12, Bandung',
                'is_main' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // Connect existing warehouses and sales orders to main branch
        DB::table('warehouses')->whereNull('branch_id')->update(['branch_id' => $mainBranchId]);
        DB::table('sales_orders')->whereNull('branch_id')->update(['branch_id' => $mainBranchId]);
        DB::table('sales_invoices')->whereNull('branch_id')->update(['branch_id' => $mainBranchId]);
        DB::table('purchase_orders')->whereNull('branch_id')->update(['branch_id' => $mainBranchId]);

        // Sample initial brands
        DB::table('brands')->insert([
            ['code' => 'BRD-GEN', 'name' => 'Generic / Tanpa Merk', 'description' => 'Produk umum tanpa merk dagang khusus', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'BRD-PREM', 'name' => 'MYERP Premium Signature', 'description' => 'Produk manufaktur internal berkualitas tinggi', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'BRD-ORIG', 'name' => 'Original Import', 'description' => 'Komponen dan bahan baku original import', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        if (Schema::hasTable('warehouses') && Schema::hasColumn('warehouses', 'branch_id')) {
            Schema::table('warehouses', function (Blueprint $table) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            });
        }
        if (Schema::hasTable('items') && Schema::hasColumn('items', 'brand_id')) {
            Schema::table('items', function (Blueprint $table) {
                $table->dropForeign(['brand_id']);
                $table->dropColumn('brand_id');
            });
        }
        if (Schema::hasTable('sales_orders') && Schema::hasColumn('sales_orders', 'branch_id')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            });
        }
        if (Schema::hasTable('sales_invoices') && Schema::hasColumn('sales_invoices', 'branch_id')) {
            Schema::table('sales_invoices', function (Blueprint $table) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            });
        }
        if (Schema::hasTable('purchase_orders') && Schema::hasColumn('purchase_orders', 'branch_id')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            });
        }

        Schema::dropIfExists('brands');
        Schema::dropIfExists('branches');
    }
};
