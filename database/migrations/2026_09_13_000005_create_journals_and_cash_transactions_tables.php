<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journals', function (Blueprint $table) {
            $table->id();
            $table->string('journal_number')->unique();
            $table->date('transaction_date');
            $table->string('reference_type')->default('MANUAL'); // MANUAL, GRN, PURCHASE_INVOICE, DO, SALES_INVOICE, CASH_IN, CASH_OUT, ASSET_DEPRECIATION, ADJUSTMENT
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_no')->nullable();
            $table->text('description')->nullable();
            $table->decimal('total_debit', 15, 2)->default(0);
            $table->decimal('total_credit', 15, 2)->default(0);
            $table->boolean('is_balanced')->default(true);
            $table->enum('status', ['posted', 'void'])->default('posted');
            $table->foreignId('created_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_id')->constrained('journals')->onDelete('cascade');
            $table->foreignId('account_id')->constrained('chart_of_accounts')->onDelete('restrict');
            $table->enum('type', ['debit', 'credit']);
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('memo')->nullable();
            $table->timestamps();
        });

        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique();
            $table->enum('type', ['cash_in', 'cash_out', 'transfer']);
            $table->foreignId('account_id')->constrained('chart_of_accounts')->onDelete('restrict'); // Main Cash/Bank Account
            $table->foreignId('target_account_id')->nullable()->constrained('chart_of_accounts')->onDelete('restrict'); // For transfer
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->onDelete('set null');
            $table->date('transaction_date');
            $table->string('reference_no')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->enum('status', ['posted', 'void'])->default('posted');
            $table->foreignId('created_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('journal_id')->nullable()->constrained('journals')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('cash_transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_transaction_id')->constrained('cash_transactions')->onDelete('cascade');
            $table->foreignId('account_id')->constrained('chart_of_accounts')->onDelete('restrict'); // Contra Account
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_transaction_items');
        Schema::dropIfExists('cash_transactions');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('journals');
    }
};
