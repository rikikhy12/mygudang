<?php

namespace Tests\Feature;

use App\Models\ChartOfAccount;
use App\Models\Contact;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Journal;
use App\Models\PurchaseOrder;
use App\Models\Role;
use App\Models\SalesOrder;
use App\Models\StockLedger;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create([
            'name' => 'admin',
            'display_name' => 'Administrator',
            'description' => 'Super User with full access',
        ]);

        $this->adminUser = User::factory()->create([
            'email' => 'admin@nexus.test',
        ]);
        $this->adminUser->roles()->attach($adminRole);
    }

    public function test_maintenance_page_can_be_viewed()
    {
        $response = $this->actingAs($this->adminUser)->get(route('maintenance.index'));
        $response->assertStatus(200);
        $response->assertSee('Backup Database');
        $response->assertSee('Reset Data Transaksi Saja');
    }

    public function test_backup_sql_can_be_downloaded()
    {
        $response = $this->actingAs($this->adminUser)->get(route('maintenance.backup', ['format' => 'sql']));
        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-disposition'), 'mycrud_erp_database_backup_'));
    }

    public function test_reset_transaction_fails_without_proper_confirmation()
    {
        $response = $this->actingAs($this->adminUser)->post(route('maintenance.reset-transactions'), [
            'confirmation' => 'salah ketik',
        ]);

        $response->assertSessionHas('error');
    }

    public function test_reset_transaction_clears_transactions_and_preserves_master_data()
    {
        // 1. Create Master Data
        $category = ItemCategory::create([
            'code' => 'CAT01',
            'name' => 'Elektronik',
            'type' => 'finished_good',
        ]);

        $unit = Unit::create(['name' => 'Unit', 'symbol' => 'pcs']);

        $item = Item::create([
            'code' => 'ITM-001',
            'name' => 'Komputer Rakitan',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'item_type' => 'finished_good',
            'purchase_price' => 5000000,
            'selling_price' => 7000000,
            'current_stock' => 15,
            'min_stock' => 2,
            'is_active' => true,
        ]);

        $customer = Contact::create([
            'type' => 'customer',
            'code' => 'CUS-001',
            'name' => 'PT Pelanggan Sejahtera',
        ]);

        $supplier = Contact::create([
            'type' => 'supplier',
            'code' => 'SUP-001',
            'name' => 'PT Supplier Utama',
        ]);

        $warehouse = Warehouse::create([
            'code' => 'GDG-01',
            'name' => 'Gudang Pusat',
            'address' => 'Jakarta',
            'is_default' => true,
        ]);

        $account = ChartOfAccount::create([
            'code' => '1001',
            'name' => 'Kas Operasional',
            'type' => 'asset',
            'normal_balance' => 'debit',
            'is_active' => true,
        ]);

        // 2. Create Transaction Data
        $so = SalesOrder::create([
            'so_number' => 'SO-TEST-001',
            'customer_id' => $customer->id,
            'order_date' => now(),
            'status' => 'draft',
            'subtotal' => 7000000,
            'grand_total' => 7000000,
            'created_by_id' => $this->adminUser->id,
        ]);

        $po = PurchaseOrder::create([
            'po_number' => 'PO-TEST-001',
            'supplier_id' => $supplier->id,
            'order_date' => now(),
            'status' => 'draft',
            'subtotal' => 5000000,
            'grand_total' => 5000000,
            'created_by_id' => $this->adminUser->id,
        ]);

        $stockLedger = StockLedger::create([
            'item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'transaction_date' => now(),
            'reference_type' => 'OPENING_BALANCE',
            'type' => 'in',
            'quantity' => 15,
            'unit_cost' => 5000000,
            'total_cost' => 75000000,
            'current_stock_after' => 15,
        ]);

        $journal = Journal::create([
            'journal_number' => 'JRN-TEST-001',
            'transaction_date' => now(),
            'reference_type' => 'MANUAL',
            'total_debit' => 5000000,
            'total_credit' => 5000000,
            'is_balanced' => true,
            'status' => 'posted',
            'created_by_id' => $this->adminUser->id,
        ]);

        // Verify initial counts
        $this->assertEquals(1, SalesOrder::count());
        $this->assertEquals(1, PurchaseOrder::count());
        $this->assertEquals(1, StockLedger::count());
        $this->assertEquals(1, Journal::count());
        $this->assertEquals(15, Item::first()->current_stock);

        // 3. Execute Reset Transactions
        $response = $this->actingAs($this->adminUser)->post(route('maintenance.reset-transactions'), [
            'confirmation' => 'RESET TRANSAKSI',
        ]);

        $response->assertRedirect(route('maintenance.index'));
        $response->assertSessionHas('success');

        // 4. Assert all transactions are cleared
        $this->assertEquals(0, SalesOrder::count());
        $this->assertEquals(0, PurchaseOrder::count());
        $this->assertEquals(0, StockLedger::count());
        $this->assertEquals(0, Journal::count());

        // 5. Assert Item stock was reset to 0
        $this->assertEquals(0, Item::first()->current_stock);

        // 6. Assert all Master Data is PRESERVED
        $this->assertDatabaseHas('items', ['id' => $item->id, 'code' => 'ITM-001', 'name' => 'Komputer Rakitan']);
        $this->assertDatabaseHas('item_categories', ['id' => $category->id, 'code' => 'CAT01']);
        $this->assertDatabaseHas('units', ['id' => $unit->id, 'symbol' => 'pcs']);
        $this->assertDatabaseHas('contacts', ['id' => $customer->id, 'name' => 'PT Pelanggan Sejahtera']);
        $this->assertDatabaseHas('contacts', ['id' => $supplier->id, 'name' => 'PT Supplier Utama']);
        $this->assertDatabaseHas('warehouses', ['id' => $warehouse->id, 'code' => 'GDG-01']);
        $this->assertDatabaseHas('chart_of_accounts', ['id' => $account->id, 'code' => '1001']);
        $this->assertDatabaseHas('users', ['id' => $this->adminUser->id, 'email' => 'admin@nexus.test']);
    }
}
