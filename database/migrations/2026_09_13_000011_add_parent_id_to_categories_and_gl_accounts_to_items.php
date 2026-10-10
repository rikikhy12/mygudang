<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_categories', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('item_categories')->onDelete('set null');
        });

        Schema::table('items', function (Blueprint $table) {
            $table->foreignId('inventory_account_id')->nullable()->after('item_type')->constrained('chart_of_accounts')->onDelete('set null');
            $table->foreignId('cogs_account_id')->nullable()->after('inventory_account_id')->constrained('chart_of_accounts')->onDelete('set null');
            $table->foreignId('sales_account_id')->nullable()->after('cogs_account_id')->constrained('chart_of_accounts')->onDelete('set null');
            $table->foreignId('purchase_account_id')->nullable()->after('sales_account_id')->constrained('chart_of_accounts')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['inventory_account_id']);
            $table->dropForeign(['cogs_account_id']);
            $table->dropForeign(['sales_account_id']);
            $table->dropForeign(['purchase_account_id']);
            $table->dropColumn(['inventory_account_id', 'cogs_account_id', 'sales_account_id', 'purchase_account_id']);
        });

        Schema::table('item_categories', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
        });
    }
};
