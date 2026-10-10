<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixed_asset_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('asset_account_id')->references('id')->on('chart_of_accounts')->onDelete('restrict');
            $table->foreignId('depreciation_expense_account_id')->references('id')->on('chart_of_accounts')->onDelete('restrict');
            $table->unsignedBigInteger('accumulated_depreciation_account_id');
            $table->foreign('accumulated_depreciation_account_id', 'fa_cat_accum_deprec_acc_fk')->references('id')->on('chart_of_accounts')->onDelete('restrict');
            $table->integer('useful_life_years')->default(4);
            $table->timestamps();
        });

        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code')->unique();
            $table->string('name');
            $table->foreignId('category_id')->constrained('fixed_asset_categories')->onDelete('restrict');
            $table->date('acquisition_date');
            $table->decimal('acquisition_cost', 15, 2);
            $table->decimal('salvage_value', 15, 2)->default(0);
            $table->integer('useful_life_months')->default(48);
            $table->enum('depreciation_method', ['straight_line'])->default('straight_line');
            $table->decimal('accumulated_depreciation', 15, 2)->default(0);
            $table->decimal('current_book_value', 15, 2);
            $table->enum('status', ['active', 'disposed'])->default('active');
            $table->foreignId('journal_id')->nullable()->constrained('journals')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('asset_depreciations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixed_asset_id')->constrained('fixed_assets')->onDelete('cascade');
            $table->date('depreciation_date');
            $table->string('period', 7); // e.g. 2026-09
            $table->decimal('amount', 15, 2);
            $table->foreignId('journal_id')->nullable()->constrained('journals')->onDelete('set null');
            $table->foreignId('created_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_depreciations');
        Schema::dropIfExists('fixed_assets');
        Schema::dropIfExists('fixed_asset_categories');
    }
};
