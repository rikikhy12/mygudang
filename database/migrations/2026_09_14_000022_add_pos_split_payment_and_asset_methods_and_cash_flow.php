<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add cash_flow_category to chart_of_accounts
        if (!Schema::hasColumn('chart_of_accounts', 'cash_flow_category')) {
            Schema::table('chart_of_accounts', function (Blueprint $table) {
                $table->string('cash_flow_category')->nullable()->default('operating')->after('is_cash_or_bank');
            });
        }

        // 2. Add payment_details to sales_invoices for multi-payment split breakdown
        if (!Schema::hasColumn('sales_invoices', 'payment_details')) {
            Schema::table('sales_invoices', function (Blueprint $table) {
                $table->json('payment_details')->nullable()->after('notes');
            });
        }

        // 3. Ensure depreciation_method in fixed_assets supports 'straight_line', 'declining_balance', 'none'
        if (Schema::hasTable('fixed_assets')) {
            Schema::table('fixed_assets', function (Blueprint $table) {
                $table->string('depreciation_method', 50)->default('straight_line')->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('chart_of_accounts', 'cash_flow_category')) {
            Schema::table('chart_of_accounts', function (Blueprint $table) {
                $table->dropColumn('cash_flow_category');
            });
        }

        if (Schema::hasColumn('sales_invoices', 'payment_details')) {
            Schema::table('sales_invoices', function (Blueprint $table) {
                $table->dropColumn('payment_details');
            });
        }
    }
};
