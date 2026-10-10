<?php

namespace App\Providers;

use App\Models\Menu;
use Database\Seeders\MenuSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            if (Schema::hasTable('items') && ! Schema::hasColumn('items', 'image')) {
                Schema::table('items', function (Blueprint $table) {
                    $table->string('image')->nullable()->after('name');
                });
            }

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

            if (Schema::hasTable('branches')) {
                if (! Schema::hasColumn('branches', 'cash_account_id')) {
                    Schema::table('branches', function (Blueprint $table) {
                        $table->foreignId('cash_account_id')->nullable()->after('address')->constrained('chart_of_accounts')->nullOnDelete();
                    });
                }
                if (! Schema::hasColumn('branches', 'deposit_account_id')) {
                    Schema::table('branches', function (Blueprint $table) {
                        $table->foreignId('deposit_account_id')->nullable()->after('cash_account_id')->constrained('chart_of_accounts')->nullOnDelete();
                    });
                }
            }

            if (! Schema::hasTable('item_branch_prices')) {
                Schema::create('item_branch_prices', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
                    $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
                    $table->decimal('selling_price', 15, 2)->nullable();
                    $table->decimal('purchase_price', 15, 2)->nullable();
                    $table->decimal('min_margin_percent', 8, 2)->nullable();
                    $table->decimal('min_stock', 12, 2)->nullable();
                    $table->boolean('is_available')->default(true);
                    $table->timestamps();

                    $table->unique(['item_id', 'branch_id'], 'item_branch_price_unique');
                });
            }

            if (! Schema::hasTable('purchase_returns')) {
                Schema::create('purchase_returns', function (Blueprint $table) {
                    $table->id();
                    $table->string('return_number')->unique();
                    $table->foreignId('purchase_invoice_id')->nullable()->constrained('purchase_invoices')->nullOnDelete();
                    $table->foreignId('supplier_id')->constrained('contacts')->cascadeOnDelete();
                    $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
                    $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
                    $table->date('return_date');
                    $table->decimal('subtotal', 15, 2)->default(0);
                    $table->decimal('tax_amount', 15, 2)->default(0);
                    $table->decimal('grand_total', 15, 2)->default(0);
                    $table->enum('refund_method', ['potong_hutang', 'tunai_kas'])->default('potong_hutang');
                    $table->foreignId('cash_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
                    $table->enum('status', ['draft', 'approved', 'completed', 'cancelled'])->default('completed');
                    $table->text('notes')->nullable();
                    $table->foreignId('journal_id')->nullable()->constrained('journals')->nullOnDelete();
                    $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
                    $table->timestamps();
                });
            }

            if (! Schema::hasTable('purchase_return_items')) {
                Schema::create('purchase_return_items', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('purchase_return_id')->constrained('purchase_returns')->cascadeOnDelete();
                    $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
                    $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
                    $table->decimal('quantity', 15, 2);
                    $table->decimal('unit_price', 15, 2)->default(0);
                    $table->decimal('total_price', 15, 2)->default(0);
                    $table->string('reason')->nullable();
                    $table->timestamps();
                });
            }

            if (! Schema::hasTable('sales_returns')) {
                Schema::create('sales_returns', function (Blueprint $table) {
                    $table->id();
                    $table->string('return_number')->unique();
                    $table->foreignId('sales_invoice_id')->nullable()->constrained('sales_invoices')->nullOnDelete();
                    $table->foreignId('customer_id')->constrained('contacts')->cascadeOnDelete();
                    $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
                    $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
                    $table->date('return_date');
                    $table->decimal('subtotal', 15, 2)->default(0);
                    $table->decimal('tax_amount', 15, 2)->default(0);
                    $table->decimal('grand_total', 15, 2)->default(0);
                    $table->enum('refund_method', ['potong_piutang', 'tunai_kas'])->default('potong_piutang');
                    $table->foreignId('cash_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
                    $table->enum('status', ['draft', 'approved', 'completed', 'cancelled'])->default('completed');
                    $table->text('notes')->nullable();
                    $table->foreignId('journal_id')->nullable()->constrained('journals')->nullOnDelete();
                    $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
                    $table->timestamps();
                });
            }

            if (! Schema::hasTable('sales_return_items')) {
                Schema::create('sales_return_items', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('sales_return_id')->constrained('sales_returns')->cascadeOnDelete();
                    $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
                    $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
                    $table->decimal('quantity', 15, 2);
                    $table->decimal('unit_price', 15, 2)->default(0);
                    $table->decimal('total_price', 15, 2)->default(0);
                    $table->string('reason')->nullable();
                    $table->timestamps();
                });
            }
            if (Schema::hasTable('contacts') && ! Schema::hasColumn('contacts', 'min_margin_percent')) {
                Schema::table('contacts', function (Blueprint $table) {
                    $table->decimal('min_margin_percent', 8, 2)->nullable()->after('type');
                });
            }

            if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'commission_rate')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->decimal('commission_rate', 8, 2)->nullable()->after('email');
                });
            }

            if (Schema::hasTable('sales_orders')) {
                if (! Schema::hasColumn('sales_orders', 'salesperson_id')) {
                    Schema::table('sales_orders', function (Blueprint $table) {
                        $table->foreignId('salesperson_id')->nullable()->after('customer_id')->constrained('users')->nullOnDelete();
                    });
                }
                if (! Schema::hasColumn('sales_orders', 'commission_amount')) {
                    Schema::table('sales_orders', function (Blueprint $table) {
                        $table->decimal('commission_rate', 8, 2)->default(0)->after('grand_total');
                        $table->decimal('commission_amount', 15, 2)->default(0)->after('commission_rate');
                    });
                }
            }

            if (Schema::hasTable('sales_invoices')) {
                if (! Schema::hasColumn('sales_invoices', 'salesperson_id')) {
                    Schema::table('sales_invoices', function (Blueprint $table) {
                        $table->foreignId('salesperson_id')->nullable()->after('customer_id')->constrained('users')->nullOnDelete();
                    });
                }
                if (! Schema::hasColumn('sales_invoices', 'commission_amount')) {
                    Schema::table('sales_invoices', function (Blueprint $table) {
                        $table->decimal('commission_rate', 8, 2)->default(0)->after('grand_total');
                        $table->decimal('commission_amount', 15, 2)->default(0)->after('commission_rate');
                    });
                }
            }
        } catch (\Throwable $e) {
            // Ignore if in migration
        }

        View::composer('layouts.app', function ($view) {
            try {
                if (! Schema::hasTable('menus')) {
                    Schema::create('menus', function (Blueprint $table) {
                        $table->id();
                        $table->unsignedBigInteger('parent_id')->nullable();
                        $table->string('name');
                        $table->string('section')->nullable();
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

                    (new MenuSeeder)->run();
                } else {
                    // Auto-sync missing return menus if not yet in db
                    if (! Menu::where('route', 'procurement.returns.index')->exists()) {
                        Menu::create([
                            'section' => 'Procurement (Pembelian)',
                            'name' => 'Retur Pembelian',
                            'route' => 'procurement.returns.index',
                            'icon' => 'bi-box-arrow-left text-danger',
                            'order' => 24,
                            'is_active' => true,
                        ]);
                    }
                    if (! Menu::where('route', 'sales.returns.index')->exists()) {
                        Menu::create([
                            'section' => 'Sales (Penjualan)',
                            'name' => 'Retur Penjualan',
                            'route' => 'sales.returns.index',
                            'icon' => 'bi-arrow-return-left text-warning',
                            'order' => 34,
                            'is_active' => true,
                        ]);
                    }
                    if (! Menu::where('route', 'sales.reports.commissions')->exists()) {
                        Menu::create([
                            'section' => 'Sales (Penjualan)',
                            'name' => 'Laporan Komisi Sales',
                            'route' => 'sales.reports.commissions',
                            'icon' => 'bi-award-fill text-warning',
                            'order' => 36,
                            'is_active' => true,
                        ]);
                    }

                    // Auto deduplicate menus if any double entries exist
                    $duplicates = Menu::select('name', 'section', DB::raw('MIN(id) as keep_id'), DB::raw('COUNT(*) as total'))
                        ->groupBy('name', 'section')
                        ->having('total', '>', 1)
                        ->get();
                    foreach ($duplicates as $dup) {
                        Menu::where('name', $dup->name)
                            ->where('section', $dup->section)
                            ->where('id', '>', $dup->keep_id)
                            ->delete();
                    }
                }

                $menus = Menu::whereNull('parent_id')
                    ->where('is_active', true)
                    ->with('children')
                    ->orderBy('order')
                    ->get();

                $view->with('dbMenus', $menus);
            } catch (\Throwable $e) {
                $view->with('dbMenus', collect([]));
            }
        });
    }
}
