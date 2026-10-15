<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\GoodsReceipt;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\PurchaseInvoice;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirectGoodsReceiptWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_direct_goods_receipt_without_po_updates_dual_unit_stock_and_journal(): void
    {
        $admin = User::where('email', 'admin@erp.local')->first();
        $supplier = Contact::where('type', 'supplier')->first();
        $warehouse = Warehouse::where('is_active', true)->first();

        // 1. Meat Item with unit KG
        $kgUnit = Unit::firstOrCreate(['symbol' => 'Kg'], ['name' => 'Kilogram']);
        $cat = ItemCategory::first();

        $meatItem = Item::create([
            'code' => 'DAGING-DIRECT-01',
            'name' => 'Daging Sirloin Direct Fresh',
            'category_id' => $cat->id,
            'unit_id' => $kgUnit->id,
            'purchase_price' => 110000,
            'selling_price' => 150000,
            'current_stock' => 0,
            'current_koli' => 0,
            'is_active' => true,
        ]);

        // 2. Access GRN Create Page in Direct Mode (without po_id)
        $createPageResponse = $this->actingAs($admin)->get(route('procurement.grn.create'));
        $createPageResponse->assertStatus(200);
        $createPageResponse->assertSee('Mode Penerimaan Langsung (Direct GRN / Bypass tanpa PO)');

        // 3. Post Direct Goods Receipt without PO
        $grnResponse = $this->actingAs($admin)->post(route('procurement.grn.store'), [
            'purchase_order_id' => null,
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'receipt_date' => date('Y-m-d'),
            'delivery_note_number' => 'SJ-DIRECT-9901',
            'notes' => 'Penerimaan langsung daging impor dari supplier tanpa PO',
            'items' => [
                [
                    'item_id' => $meatItem->id,
                    'koli' => '3 Bal',
                    'quantity' => 75.50, // 75.50 Kg
                    'unit_cost' => 110000,
                ],
            ],
        ]);

        $grnResponse->assertRedirect(route('procurement.grn.index'));

        // 4. Verify Goods Receipt Record
        $grn = GoodsReceipt::latest()->first();
        $this->assertNotNull($grn);
        $this->assertNull($grn->purchase_order_id);
        $this->assertEquals($supplier->id, $grn->supplier_id);
        $this->assertEquals('posted', $grn->status);
        $this->assertEquals('SJ-DIRECT-9901', $grn->delivery_note_number);

        $grnItem = $grn->items->first();
        $this->assertNotNull($grnItem);
        $this->assertNull($grnItem->purchase_order_item_id);
        $this->assertEquals('3 Bal', $grnItem->koli);
        $this->assertEquals(75.50, (float) $grnItem->quantity);
        $this->assertEquals(110000, (float) $grnItem->unit_cost);
        $this->assertEquals(8305000, (float) $grnItem->total_cost); // 75.50 * 110.000 = 8.305.000

        // 5. Verify Dual-Unit Stock Balance (Kg & Koli)
        $meatItem->refresh();
        $this->assertEquals(75.50, (float) $meatItem->current_stock);
        $this->assertEquals(3.00, (float) $meatItem->current_koli);

        // 6. Verify Accounting Journal
        $this->assertNotNull($grn->journal_id);
        $this->assertEquals(8305000, (float) $grn->journal->total_debit);
        $this->assertEquals(8305000, (float) $grn->journal->total_credit);

        // 7. Verify Index and Show Pages Render Seamlessly
        $indexResponse = $this->actingAs($admin)->get(route('procurement.grn.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee($grn->grn_number);
        $indexResponse->assertSee('Direct (Tanpa PO)');

        $showResponse = $this->actingAs($admin)->get(route('procurement.grn.show', $grn->id));
        $showResponse->assertStatus(200);
        $showResponse->assertSee($grn->grn_number);
        $showResponse->assertSee('Direct (Tanpa PO)');
        $showResponse->assertSee('3 Bal');

        // 8. Verify Create AP Invoice from Direct GRN
        $invCreateResponse = $this->actingAs($admin)->get(route('procurement.invoices.create', ['grn_id' => $grn->id]));
        $invCreateResponse->assertStatus(200);
        $invCreateResponse->assertSee($grn->grn_number);

        $invPostResponse = $this->actingAs($admin)->post(route('procurement.invoices.store'), [
            'supplier_id' => $supplier->id,
            'goods_receipt_id' => $grn->id,
            'warehouse_id' => $warehouse->id,
            'invoice_date' => date('Y-m-d'),
            'due_date' => date('Y-m-d', strtotime('+14 days')),
            'supplier_invoice_number' => 'INV-SUP-DIRECT-001',
            'tax_rate' => 0,
            'items' => [
                [
                    'item_id' => $meatItem->id,
                    'koli' => '3 Bal',
                    'quantity' => 75.50,
                    'unit_price' => 110000,
                ],
            ],
        ]);
        $invPostResponse->assertRedirect(route('procurement.invoices.index'));

        $invoice = PurchaseInvoice::latest()->first();
        $this->assertNotNull($invoice);
        $this->assertEquals($grn->id, $invoice->goods_receipt_id);
        $this->assertEquals(8305000, (float) $invoice->grand_total);
    }
}
