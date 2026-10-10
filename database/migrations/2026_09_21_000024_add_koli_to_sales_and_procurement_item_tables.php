<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'purchase_order_items',
            'goods_receipt_items',
            'purchase_invoice_items',
            'sales_order_items',
            'delivery_order_items',
            'sales_invoice_items',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (! Schema::hasColumn($tableName, 'koli')) {
                    $table->string('koli')->nullable()->after('quantity');
                }
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'purchase_order_items',
            'goods_receipt_items',
            'purchase_invoice_items',
            'sales_order_items',
            'delivery_order_items',
            'sales_invoice_items',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'koli')) {
                    $table->dropColumn('koli');
                }
            });
        }
    }
};
