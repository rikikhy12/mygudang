<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\GoodsReceipt;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequisition;
use App\Models\SalesOrder;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeatCatchWeightWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_complete_meat_workflow_from_pr_to_returns_and_dual_unit_stock(): void
    {
        $admin = User::where('email', 'admin@erp.local')->first();
        $supplier = Contact::where('type', 'supplier')->first();
        $customer = Contact::where('type', 'customer')->first();
        $warehouse = Warehouse::where('is_active', true)->first();

        // 1. Create Meat Product with Unit KG
        $kgUnit = Unit::firstOrCreate(['symbol' => 'Kg'], ['name' => 'Kilogram']);
        $cat = ItemCategory::first();

        $meatItem = Item::create([
            'code' => 'DAGING-SAPI-01',
            'name' => 'Daging Sapi Tenderloin Import',
            'category_id' => $cat->id,
            'unit_id' => $kgUnit->id,
            'purchase_price' => 100000, // Rp 100.000 / Kg
            'selling_price' => 135000,  // Rp 135.000 / Kg
            'current_stock' => 0,
            'current_koli' => 0,
            'is_active' => true,
        ]);

        // 2. PR (Purchase Requisition) with Koli and KG
        $prResponse = $this->actingAs($admin)->post(route('procurement.pr.store'), [
            'request_date' => date('Y-m-d'),
            'department' => 'Kitchen & Butchery',
            'notes' => 'Pengadaan daging sapi impor',
            'items' => [
                [
                    'item_id' => $meatItem->id,
                    'koli' => '2 Bal',
                    'quantity' => 60.00, // 60 Kg total
                    'estimated_unit_price' => 100000,
                ],
            ],
        ]);
        $prResponse->assertRedirect(route('procurement.pr.index'));

        $pr = PurchaseRequisition::latest()->first();
        $this->assertEquals('pending_approval', $pr->status);
        $this->assertEquals('2 Bal', $pr->items->first()->koli);
        $this->assertEquals(60.00, (float) $pr->items->first()->quantity);

        // Approve PR
        $this->actingAs($admin)->post(route('procurement.pr.approve', $pr->id));
        $pr->refresh();
        $this->assertEquals('approved', $pr->status);

        // 3. Convert PR to PO (Purchase Order)
        $poResponse = $this->actingAs($admin)->post(route('procurement.po.store'), [
            'supplier_id' => $supplier->id,
            'purchase_requisition_id' => $pr->id,
            'order_date' => date('Y-m-d'),
            'tax_rate' => 0,
            'discount_amount' => 0,
            'items' => [
                [
                    'item_id' => $meatItem->id,
                    'koli' => '2 Bal',
                    'quantity' => 60.00, // 60 Kg
                    'unit_price' => 100000, // Rp 100.000 per Kg
                ],
            ],
        ]);
        $poResponse->assertRedirect(route('procurement.po.index'));

        $po = PurchaseOrder::latest()->first();
        $this->assertEquals('2 Bal', $po->items->first()->koli);
        $this->assertEquals(60.00, (float) $po->items->first()->quantity);
        $this->assertEquals(6000000, (float) $po->grand_total); // 60 kg * 100.000

        // 4. GRN (Goods Receipt) - Receive 2 Bal = 60 Kg
        $grnResponse = $this->actingAs($admin)->post(route('procurement.grn.store'), [
            'purchase_order_id' => $po->id,
            'warehouse_id' => $warehouse->id,
            'receipt_date' => date('Y-m-d'),
            'delivery_note_number' => 'DN-SUP-001',
            'items' => [
                [
                    'item_id' => $meatItem->id,
                    'koli' => '2 Bal',
                    'quantity' => 60.00,
                ],
            ],
        ]);
        $grnResponse->assertRedirect(route('procurement.grn.index'));

        $meatItem->refresh();
        $this->assertEquals(60.00, (float) $meatItem->current_stock); // 60 Kg
        $this->assertEquals(2.00, (float) $meatItem->current_koli);   // 2 Bal

        // 5. Purchase Invoice from GRN
        $grn = GoodsReceipt::latest()->first();
        $invResponse = $this->actingAs($admin)->post(route('procurement.invoices.store'), [
            'supplier_id' => $supplier->id,
            'goods_receipt_id' => $grn->id,
            'invoice_date' => date('Y-m-d'),
            'due_date' => date('Y-m-d', strtotime('+14 days')),
            'supplier_invoice_number' => 'INV-SUP-99',
            'tax_rate' => 0,
            'items' => [
                [
                    'item_id' => $meatItem->id,
                    'koli' => '2 Bal',
                    'quantity' => 60.00,
                    'unit_price' => 100000,
                ],
            ],
        ]);
        $invResponse->assertRedirect(route('procurement.invoices.index'));

        // 6. Sales Order (SO): Sell 1 Bal (25 Kg) @ Rp 135.000 / Kg
        $soResponse = $this->actingAs($admin)->post(route('sales.so.store'), [
            'customer_id' => $customer->id,
            'order_date' => date('Y-m-d'),
            'tax_rate' => 0,
            'discount_amount' => 0,
            'items' => [
                [
                    'item_id' => $meatItem->id,
                    'koli' => '1 Bal',
                    'quantity' => 25.00, // 25 Kg
                    'unit_price' => 135000,
                ],
            ],
        ]);
        $soResponse->assertRedirect(route('sales.so.index'));

        $so = SalesOrder::latest()->first();
        $this->assertEquals('1 Bal', $so->items->first()->koli);
        $this->assertEquals(25.00, (float) $so->items->first()->quantity);
        $this->assertEquals(3375000, (float) $so->grand_total); // 25 Kg * 135.000

        // 7. Delivery Order (DO) - Deliver 1 Bal = 25 Kg
        $doResponse = $this->actingAs($admin)->post(route('sales.do.store'), [
            'sales_order_id' => $so->id,
            'warehouse_id' => $warehouse->id,
            'delivery_date' => date('Y-m-d'),
            'driver_name' => 'Pak Supir',
            'items' => [
                [
                    'item_id' => $meatItem->id,
                    'koli' => '1 Bal',
                    'quantity' => 25.00,
                ],
            ],
        ]);
        $doResponse->assertRedirect(route('sales.do.index'));

        // Verify stock deducted: 2 Bal - 1 Bal = 1 Bal, 60 Kg - 25 Kg = 35 Kg
        $meatItem->refresh();
        $this->assertEquals(35.00, (float) $meatItem->current_stock); // 35 Kg remaining
        $this->assertEquals(1.00, (float) $meatItem->current_koli);   // 1 Bal remaining

        // 8. Purchase Return (Retur Pembelian): Retur 0.5 Bal (10 Kg) ke supplier
        $prRetResponse = $this->actingAs($admin)->post(route('procurement.returns.store'), [
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'return_date' => date('Y-m-d'),
            'refund_method' => 'potong_hutang',
            'tax_rate' => 0,
            'notes' => 'Retur potongan lemak berlebih',
            'items' => [
                [
                    'item_id' => $meatItem->id,
                    'koli' => '0.5 Bal',
                    'quantity' => 10.00,
                    'unit_price' => 100000,
                    'reason' => 'Fat content too high',
                ],
            ],
        ]);
        $prRetResponse->assertRedirect();

        // Verify stock deducted: 1 Bal - 0.5 Bal = 0.5 Bal, 35 Kg - 10 Kg = 25 Kg
        $meatItem->refresh();
        $this->assertEquals(25.00, (float) $meatItem->current_stock);
        $this->assertEquals(0.50, (float) $meatItem->current_koli);

        // 9. Sales Return (Retur Penjualan): Customer retur 0.2 Bal (5 Kg)
        $srRetResponse = $this->actingAs($admin)->post(route('sales.returns.store'), [
            'customer_id' => $customer->id,
            'warehouse_id' => $warehouse->id,
            'return_date' => date('Y-m-d'),
            'refund_method' => 'potong_piutang',
            'tax_rate' => 0,
            'notes' => 'Customer retur 5 Kg',
            'items' => [
                [
                    'item_id' => $meatItem->id,
                    'koli' => '0.2 Bal',
                    'quantity' => 5.00,
                    'unit_price' => 135000,
                    'reason' => 'Salah potong',
                ],
            ],
        ]);
        $srRetResponse->assertRedirect();

        // Verify stock restored: 0.5 Bal + 0.2 Bal = 0.7 Bal, 25 Kg + 5 Kg = 30 Kg
        $meatItem->refresh();
        $this->assertEquals(30.00, (float) $meatItem->current_stock);
        $this->assertEquals(0.70, (float) $meatItem->current_koli);

        // 10. Verify Stock Overview & Movements Views
        $overviewResponse = $this->actingAs($admin)->get(route('inventory.overview'));
        $overviewResponse->assertStatus(200);
        $overviewResponse->assertSee('Daging Sapi Tenderloin Import');

        $movementsResponse = $this->actingAs($admin)->get(route('inventory.movements', ['item_id' => $meatItem->id]));
        $movementsResponse->assertStatus(200);
        $movementsResponse->assertSee('GRN');
        $movementsResponse->assertSee('DELIVERY_ORDER');
        $movementsResponse->assertSee('PURCHASE_RETURN');
        $movementsResponse->assertSee('SALES_RETURN');

        $stockCardResponse = $this->actingAs($admin)->get(route('items.stock-card', $meatItem->id));
        $stockCardResponse->assertStatus(200);
        $stockCardResponse->assertSee('Daging Sapi Tenderloin Import');
    }
}
