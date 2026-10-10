<?php

use Database\Seeders\MenuSeeder;
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
        if (! Schema::hasTable('menus')) {
            Schema::create('menus', function (Blueprint $table) {
                $table->id();
                $table->foreignId('parent_id')->nullable()->constrained('menus')->onDelete('cascade');
                $table->string('name');
                $table->string('section')->nullable(); // e.g. Menu Utama, Master Data, Sales, etc.
                $table->string('route')->nullable();
                $table->string('url')->nullable();
                $table->string('icon')->nullable();
                $table->string('badge')->nullable();
                $table->string('badge_color')->nullable();
                $table->string('permission_name')->nullable();
                $table->integer('order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            // Auto-seed initial menus
            (new MenuSeeder)->run();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
