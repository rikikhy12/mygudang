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
        if (Schema::hasTable('branches')) {
            Schema::table('branches', function (Blueprint $table) {
                if (! Schema::hasColumn('branches', 'cash_account_id')) {
                    $table->foreignId('cash_account_id')->nullable()->after('address')->constrained('chart_of_accounts')->nullOnDelete();
                }
                if (! Schema::hasColumn('branches', 'deposit_account_id')) {
                    $table->foreignId('deposit_account_id')->nullable()->after('cash_account_id')->constrained('chart_of_accounts')->nullOnDelete();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('branches')) {
            Schema::table('branches', function (Blueprint $table) {
                if (Schema::hasColumn('branches', 'deposit_account_id')) {
                    $table->dropConstrainedForeignId('deposit_account_id');
                }
                if (Schema::hasColumn('branches', 'cash_account_id')) {
                    $table->dropConstrainedForeignId('cash_account_id');
                }
            });
        }
    }
};
