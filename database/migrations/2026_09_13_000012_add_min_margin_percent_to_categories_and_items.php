<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_categories', function (Blueprint $table) {
            $table->decimal('min_margin_percent', 5, 2)->default(0)->after('description');
        });

        Schema::table('items', function (Blueprint $table) {
            $table->decimal('min_margin_percent', 5, 2)->nullable()->after('selling_price');
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('min_margin_percent');
        });

        Schema::table('item_categories', function (Blueprint $table) {
            $table->dropColumn('min_margin_percent');
        });
    }
};
