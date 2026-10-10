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
        if (! Schema::hasTable('pos_shifts')) {
            Schema::create('pos_shifts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('cash_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
                $table->string('shift_number')->unique();
                $table->dateTime('start_time');
                $table->dateTime('end_time')->nullable();
                $table->decimal('starting_cash', 15, 2)->default(0);
                $table->decimal('total_cash_sales', 15, 2)->default(0);
                $table->decimal('total_non_cash_sales', 15, 2)->default(0);
                $table->decimal('total_drops', 15, 2)->default(0);
                $table->decimal('closing_cash_expected', 15, 2)->default(0);
                $table->decimal('closing_cash_actual', 15, 2)->nullable();
                $table->decimal('cash_difference', 15, 2)->default(0);
                $table->enum('status', ['open', 'closed'])->default('open');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('pos_shift_drops')) {
            Schema::create('pos_shift_drops', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pos_shift_id')->constrained('pos_shifts')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('from_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
                $table->foreignId('to_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
                $table->decimal('amount', 15, 2);
                $table->dateTime('drop_time');
                $table->text('notes')->nullable();
                $table->foreignId('journal_id')->nullable()->constrained('journals')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('sales_invoices') && ! Schema::hasColumn('sales_invoices', 'pos_shift_id')) {
            Schema::table('sales_invoices', function (Blueprint $table) {
                $table->foreignId('pos_shift_id')->nullable()->after('branch_id')->constrained('pos_shifts')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sales_invoices') && Schema::hasColumn('sales_invoices', 'pos_shift_id')) {
            Schema::table('sales_invoices', function (Blueprint $table) {
                $table->dropForeign(['pos_shift_id']);
                $table->dropColumn('pos_shift_id');
            });
        }

        Schema::dropIfExists('pos_shift_drops');
        Schema::dropIfExists('pos_shifts');
    }
};
