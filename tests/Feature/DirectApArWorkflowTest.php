<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Item;
use App\Models\PurchaseInvoice;
use App\Models\SalesInvoice;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirectApArWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_direct_ap_purchase_invoice_without_grn_or_po(): void
    {
        $admin = User::where('email', 'admin@erp.local')->first();
        $supplier = Contact::where('type', 'supplier')->first();
        $warehouse = Warehouse::where('is_active', true)->first();
        $item = Item::where('code', 'LAP-001')->first();

        $initialStock = (float) $item->current_stock;
        $purchaseQty = 5;
        $unitPrice = 12000000;

        // View create page should work smoothly and return 200
        $viewResponse = $this->actingAs($admin)->get(route('procurement.invoices.create'));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Input Tagihan Langsung (Tanpa GRN/PO)');

        // Direct Store Tagihan AP
        $response = $this->actingAs($admin)->post(route('procurement.invoices.store'), [
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'invoice_date' => date('Y-m-d'),
            'due_date' => date('Y-m-d', strtotime('+30 days')),
            'supplier_invoice_number' => 'INV-DIRECT-SUP-01',
            'tax_rate' => 11,
            'items' => [
                [
                    'item_id' => $item->id,
                    'quantity' => $purchaseQty,
                    'unit_price' => $unitPrice,
                ],
            ],
        ]);

        $response->assertRedirect(route('procurement.invoices.index'));

        // Stock should increase directly
        $item->refresh();
        $this->assertEquals($initialStock + $purchaseQty, (float) $item->current_stock);

        $pinv = PurchaseInvoice::latest('id')->first();
        $this->assertNotNull($pinv);
        $this->assertNull($pinv->goods_receipt_id);
        $this->assertNull($pinv->purchase_order_id);
        $this->assertEquals(60000000, $pinv->subtotal);
        $this->assertEquals(6600000, $pinv->tax_amount);
        $this->assertEquals(66600000, $pinv->grand_total);
        $this->assertNotNull($pinv->journal_id);
    }

    public function test_direct_ar_sales_invoice_without_do_or_so(): void
    {
        $admin = User::where('email', 'admin@erp.local')->first();
        $customer = Contact::where('type', 'customer')->first();
        $warehouse = Warehouse::where('is_active', true)->first();
        $item = Item::where('code', 'LAP-001')->first();

        $initialStock = (float) $item->current_stock;
        $sellQty = 3;
        $unitPrice = 15500000;

        // View create page should return 200
        $viewResponse = $this->actingAs($admin)->get(route('sales.invoices.create'));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Input Faktur AR Langsung (Tanpa DO/SO)');

        // Direct Store Faktur Penjualan (AR)
        $response = $this->actingAs($admin)->post(route('sales.invoices.store'), [
            'customer_id' => $customer->id,
            'warehouse_id' => $warehouse->id,
            'invoice_date' => date('Y-m-d'),
            'due_date' => date('Y-m-d', strtotime('+14 days')),
            'tax_rate' => 11,
            'items' => [
                [
                    'item_id' => $item->id,
                    'quantity' => $sellQty,
                    'unit_price' => $unitPrice,
                ],
            ],
        ]);

        $response->assertRedirect(route('sales.invoices.index'));

        // Stock should decrease directly
        $item->refresh();
        $this->assertEquals($initialStock - $sellQty, (float) $item->current_stock);

        $sinv = SalesInvoice::latest('id')->first();
        $this->assertNotNull($sinv);
        $this->assertNull($sinv->delivery_order_id);
        $this->assertNull($sinv->sales_order_id);
        $this->assertEquals(46500000, $sinv->subtotal);
        $this->assertEquals(5115000, $sinv->tax_amount);
        $this->assertEquals(51615000, $sinv->grand_total);
        $this->assertNotNull($sinv->journal_id);
    }
}
