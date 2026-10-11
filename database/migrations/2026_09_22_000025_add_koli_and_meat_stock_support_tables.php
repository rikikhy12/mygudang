<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add koli to purchase_requisition_items
        if (Schema::hasTable('purchase_requisition_items') && ! Schema::hasColumn('purchase_requisition_items', 'koli')) {
            Schema::table('purchase_requisition_items', function (Blueprint $table) {
                $table->string('koli')->nullable()->after('quantity');
            });
        }

        // 2. Add koli to purchase_return_items
        if (Schema::hasTable('purchase_return_items') && ! Schema::hasColumn('purchase_return_items', 'koli')) {
            Schema::table('purchase_return_items', function (Blueprint $table) {
                $table->string('koli')->nullable()->after('quantity');
            });
        }

        // 3. Add koli to sales_return_items
        if (Schema::hasTable('sales_return_items') && ! Schema::hasColumn('sales_return_items', 'koli')) {
            Schema::table('sales_return_items', function (Blueprint $table) {
                $table->string('koli')->nullable()->after('quantity');
            });
        }

        // 4. Add koli and current_koli_after to stock_ledgers
        if (Schema::hasTable('stock_ledgers')) {
            Schema::table('stock_ledgers', function (Blueprint $table) {
                if (! Schema::hasColumn('stock_ledgers', 'koli')) {
                    $table->decimal('koli', 12, 2)->default(0)->after('quantity');
                }
                if (! Schema::hasColumn('stock_ledgers', 'current_koli_after')) {
                    $table->decimal('current_koli_after', 12, 2)->default(0)->after('current_stock_after');
                }
            });
        }

        // 5. Add current_koli to items
        if (Schema::hasTable('items') && ! Schema::hasColumn('items', 'current_koli')) {
            Schema::table('items', function (Blueprint $table) {
                $table->decimal('current_koli', 12, 2)->default(0)->after('current_stock');
            });
        }

        // 6. Ensure salesperson and commissions on sales_orders & sales_invoices
        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'commission_rate')) {
            Schema::table('users', function (Blueprint $table) {
                $table->decimal('commission_rate', 8, 2)->nullable()->after('email');
            });
        }

        if (Schema::hasTable('contacts') && ! Schema::hasColumn('contacts', 'min_margin_percent')) {
            Schema::table('contacts', function (Blueprint $table) {
                $table->decimal('min_margin_percent', 8, 2)->nullable()->after('type');
            });
        }

        if (Schema::hasTable('sales_orders')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                if (! Schema::hasColumn('sales_orders', 'salesperson_id')) {
                    $table->foreignId('salesperson_id')->nullable()->after('customer_id')->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn('sales_orders', 'commission_rate')) {
                    $table->decimal('commission_rate', 8, 2)->default(0)->after('grand_total');
                }
                if (! Schema::hasColumn('sales_orders', 'commission_amount')) {
                    $table->decimal('commission_amount', 15, 2)->default(0)->after('commission_rate');
                }
            });
        }

        if (Schema::hasTable('sales_invoices')) {
            Schema::table('sales_invoices', function (Blueprint $table) {
                if (! Schema::hasColumn('sales_invoices', 'salesperson_id')) {
                    $table->foreignId('salesperson_id')->nullable()->after('customer_id')->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn('sales_invoices', 'commission_rate')) {
                    $table->decimal('commission_rate', 8, 2)->default(0)->after('grand_total');
                }
                if (! Schema::hasColumn('sales_invoices', 'commission_amount')) {
                    $table->decimal('commission_amount', 15, 2)->default(0)->after('commission_rate');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('items') && Schema::hasColumn('items', 'current_koli')) {
            Schema::table('items', function (Blueprint $table) {
                $table->dropColumn('current_koli');
            });
        }

        if (Schema::hasTable('stock_ledgers')) {
            Schema::table('stock_ledgers', function (Blueprint $table) {
                if (Schema::hasColumn('stock_ledgers', 'current_koli_after')) {
                    $table->dropColumn('current_koli_after');
                }
                if (Schema::hasColumn('stock_ledgers', 'koli')) {
                    $table->dropColumn('koli');
                }
            });
        }

        if (Schema::hasTable('sales_return_items') && Schema::hasColumn('sales_return_items', 'koli')) {
            Schema::table('sales_return_items', function (Blueprint $table) {
                $table->dropColumn('koli');
            });
        }

        if (Schema::hasTable('purchase_return_items') && Schema::hasColumn('purchase_return_items', 'koli')) {
            Schema::table('purchase_return_items', function (Blueprint $table) {
                $table->dropColumn('koli');
            });
        }

        if (Schema::hasTable('purchase_requisition_items') && Schema::hasColumn('purchase_requisition_items', 'koli')) {
            Schema::table('purchase_requisition_items', function (Blueprint $table) {
                $table->dropColumn('koli');
            });
        }
    }
};
