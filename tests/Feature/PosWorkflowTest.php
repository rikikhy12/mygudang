<?php

namespace Tests\Feature;

use App\Models\ChartOfAccount;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Warehouse $warehouse;

    protected Item $item;

    protected ChartOfAccount $cashAccount;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('email', 'admin@erp.local')->first();
        $this->warehouse = Warehouse::first();
        $this->cashAccount = ChartOfAccount::where('code', '1001')->first();

        $unit = Unit::firstOrCreate(['code' => 'PCS'], ['name' => 'Pcs', 'symbol' => 'pcs']);
        $cat = ItemCategory::first();

        $this->item = Item::create([
            'category_id' => $cat->id,
            'unit_id' => $unit->id,
            'code' => 'POS-PROD-01',
            'name' => 'Produk Kasir POS',
            'barcode' => '8991234567890',
            'item_type' => 'finished_good',
            'purchase_price' => 20000,
            'selling_price' => 35000,
            'current_stock' => 50,
            'min_stock' => 5,
            'is_active' => true,
        ]);
    }

    public function test_pos_screen_renders_successfully(): void
    {
        $response = $this->actingAs($this->admin)->get(route('pos.index'));
        $response->assertStatus(200);
        $response->assertSee('Terminal Kasir');
        $response->assertSee('Produk Kasir POS');
    }

    public function test_pos_checkout_creates_invoice_deducts_stock_and_creates_journal(): void
    {
        $payload = [
            'warehouse_id' => $this->warehouse->id,
            'payment_method' => 'cash',
            'account_id' => $this->cashAccount->id,
            'paid_amount' => 100000,
            'discount_amount' => 5000,
            'tax_amount' => 3300,
            'items' => [
                [
                    'item_id' => $this->item->id,
                    'quantity' => 2,
                    'unit_price' => 35000,
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->withHeaders(['Accept' => 'application/json'])
            ->postJson(route('pos.store'), $payload);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify stock deducted: 50 - 2 = 48
        $this->assertEquals(48, $this->item->fresh()->current_stock);

        // Verify invoice created and status is paid
        $this->assertDatabaseHas('sales_invoices', [
            'status' => 'paid',
            'subtotal' => 70000,
            'tax_amount' => 3300,
            'grand_total' => 68300, // 70000 - 5000 + 3300
        ]);

        // Verify stock ledger entry created
        $this->assertDatabaseHas('stock_ledgers', [
            'item_id' => $this->item->id,
            'warehouse_id' => $this->warehouse->id,
            'reference_type' => 'POS_SALE',
            'type' => 'out',
            'quantity' => 2,
        ]);

        // Verify receipt view renders cleanly
        $invoiceId = $response->json('invoice_id');
        $receiptResponse = $this->actingAs($this->admin)->get(route('pos.receipt', $invoiceId));
        $receiptResponse->assertStatus(200);
        $receiptResponse->assertSee('MYCRUD ERP STORE');
        $receiptResponse->assertSee('Produk Kasir POS');
    }
}
