<?php

namespace Tests\Feature;

use App\Models\Bom;
use App\Models\BomItem;
use App\Models\ChartOfAccount;
use App\Models\Contact;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemUnitConversion;
use App\Models\ItemWarehousePrice;
use App\Models\Routing;
use App\Models\RoutingStep;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WorkOrder;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManufacturingAndMultiUnitFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Warehouse $rawWh;

    protected Warehouse $finishWh;

    protected Unit $pcsUnit;

    protected Unit $boxUnit;

    protected ItemCategory $finishedCat;

    protected ItemCategory $rawCat;

    protected ChartOfAccount $cashAcc;

    protected ChartOfAccount $invAcc;

    protected ChartOfAccount $cogsAcc;

    protected ChartOfAccount $salesAcc;

    protected ChartOfAccount $depositAcc;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        // Warehouses
        $this->rawWh = Warehouse::create(['name' => 'Gudang Bahan Baku', 'code' => 'WH-RAW', 'is_active' => true]);
        $this->finishWh = Warehouse::create(['name' => 'Gudang Barang Jadi', 'code' => 'WH-FIN', 'is_active' => true]);

        // Units
        $this->pcsUnit = Unit::create(['name' => 'Pieces', 'symbol' => 'PCS']);
        $this->boxUnit = Unit::create(['name' => 'Box', 'symbol' => 'BOX']);

        // Accounts
        $this->cashAcc = ChartOfAccount::create(['code' => '1001', 'name' => 'Kas Toko', 'type' => 'asset', 'normal_balance' => 'debit', 'is_cash_or_bank' => true, 'is_active' => true]);
        $this->invAcc = ChartOfAccount::create(['code' => '1201', 'name' => 'Persediaan Barang', 'type' => 'asset', 'normal_balance' => 'debit', 'is_active' => true]);
        $this->cogsAcc = ChartOfAccount::create(['code' => '5001', 'name' => 'HPP Penjualan', 'type' => 'cogs', 'normal_balance' => 'debit', 'is_active' => true]);
        $this->salesAcc = ChartOfAccount::create(['code' => '4001', 'name' => 'Pendapatan Penjualan', 'type' => 'revenue', 'normal_balance' => 'credit', 'is_active' => true]);
        $this->depositAcc = ChartOfAccount::create(['code' => '2004', 'name' => 'Deposit Pelanggan', 'type' => 'liability', 'normal_balance' => 'credit', 'is_active' => true]);

        // Categories
        $this->finishedCat = ItemCategory::create([
            'name' => 'Barang Jadi',
            'code' => 'CAT-FIN',
            'type' => 'finished_good',
            'inventory_account_id' => $this->invAcc->id,
            'cogs_account_id' => $this->cogsAcc->id,
            'sales_account_id' => $this->salesAcc->id,
        ]);

        $this->rawCat = ItemCategory::create([
            'name' => 'Bahan Mentah',
            'code' => 'CAT-RAW',
            'type' => 'raw_material',
            'inventory_account_id' => $this->invAcc->id,
            'cogs_account_id' => $this->cogsAcc->id,
            'sales_account_id' => $this->salesAcc->id,
        ]);
    }

    public function test_multi_unit_conversion_and_warehouse_price_overrides()
    {
        $item = Item::create([
            'code' => 'SNACK-01',
            'name' => 'Snack Keripik',
            'category_id' => $this->finishedCat->id,
            'unit_id' => $this->pcsUnit->id,
            'item_type' => 'finished_good',
            'purchase_price' => 1000,
            'selling_price' => 2000,
            'current_stock' => 100,
        ]);

        // Conversion: 1 BOX = 10 PCS, Selling Price Override = 18.000
        ItemUnitConversion::create([
            'item_id' => $item->id,
            'unit_id' => $this->boxUnit->id,
            'conversion_factor' => 10,
            'selling_price_override' => 18000,
            'purchase_price_override' => 9500,
        ]);

        // Warehouse Price Override for finishWh
        ItemWarehousePrice::create([
            'item_id' => $item->id,
            'warehouse_id' => $this->finishWh->id,
            'selling_price' => 2500,
            'purchase_price' => 1200,
        ]);

        // Check base price
        $this->assertEquals(2000, $item->getPriceForWarehouse());

        // Check warehouse price override
        $this->assertEquals(2500, $item->getPriceForWarehouse($this->finishWh->id));

        // Check unit conversion override
        $this->assertEquals(18000, $item->getPriceForWarehouse(null, $this->boxUnit->id));
        $this->assertEquals(9500, $item->getPurchasePriceForWarehouse(null, $this->boxUnit->id));
    }

    public function test_raw_material_selling_restriction()
    {
        $rawItem = Item::create([
            'code' => 'RAW-TEPUNG',
            'name' => 'Tepung Terigu Mentah',
            'category_id' => $this->rawCat->id,
            'unit_id' => $this->pcsUnit->id,
            'item_type' => 'raw_material',
            'purchase_price' => 5000,
            'selling_price' => 8000,
            'current_stock' => 50,
        ]);

        $superItem = Item::create([
            'code' => 'SUP-TELUR',
            'name' => 'Telur Ayam Super',
            'category_id' => $this->rawCat->id,
            'unit_id' => $this->pcsUnit->id,
            'item_type' => 'super_product',
            'purchase_price' => 2000,
            'selling_price' => 3000,
            'current_stock' => 50,
        ]);

        $this->assertFalse($rawItem->canBeSold());
        $this->assertTrue($superItem->canBeSold());
    }

    public function test_bundle_item_bom_auto_deduction_in_pos()
    {
        $inventoryService = app(InventoryService::class);

        // Create Ingredients
        $kopi = Item::create([
            'code' => 'MAT-KOPI',
            'name' => 'Bubuk Kopi Espresso',
            'category_id' => $this->rawCat->id,
            'unit_id' => $this->pcsUnit->id,
            'item_type' => 'raw_material',
            'purchase_price' => 3000,
            'selling_price' => 0,
            'current_stock' => 0,
        ]);
        $susu = Item::create([
            'code' => 'MAT-SUSU',
            'name' => 'Susu UHT Fresh',
            'category_id' => $this->rawCat->id,
            'unit_id' => $this->pcsUnit->id,
            'item_type' => 'raw_material',
            'purchase_price' => 4000,
            'selling_price' => 0,
            'current_stock' => 0,
        ]);

        // Stock initial materials
        $inventoryService->recordStockMovement($kopi->id, $this->rawWh->id, 'INIT', 1, 'INIT-1', 'in', 100, 3000);
        $inventoryService->recordStockMovement($susu->id, $this->rawWh->id, 'INIT', 1, 'INIT-1', 'in', 100, 4000);

        // Create Bundle Item: Kopi Latte
        $latteBundle = Item::create([
            'code' => 'BND-LATTE',
            'name' => 'Kopi Latte Aren (Paket)',
            'category_id' => $this->finishedCat->id,
            'unit_id' => $this->pcsUnit->id,
            'item_type' => 'bundle',
            'purchase_price' => 7000,
            'selling_price' => 20000,
            'current_stock' => 0,
        ]);

        // Create BOM for Bundle
        $bom = Bom::create([
            'code' => 'BOM-LATTE',
            'name' => 'Resep Kopi Latte Aren',
            'item_id' => $latteBundle->id,
            'yield_quantity' => 1,
            'is_active' => true,
        ]);
        BomItem::create(['bom_id' => $bom->id, 'item_id' => $kopi->id, 'quantity' => 1, 'unit_cost' => 3000]);
        BomItem::create(['bom_id' => $bom->id, 'item_id' => $susu->id, 'quantity' => 1, 'unit_cost' => 4000]);

        $customer = Contact::create(['code' => 'CUST-001', 'name' => 'Budi Santoso', 'type' => 'customer']);

        // POS Checkout 2 cups of Kopi Latte
        $response = $this->postJson(route('pos.store'), [
            'warehouse_id' => $this->rawWh->id,
            'customer_id' => $customer->id,
            'payment_method' => 'cash',
            'account_id' => $this->cashAcc->id,
            'paid_amount' => 50000,
            'items' => [
                [
                    'item_id' => $latteBundle->id,
                    'quantity' => 2,
                    'unit_price' => 20000,
                ],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Check ingredients deducted by 2 units each!
        $this->assertEquals(98, $kopi->fresh()->current_stock);
        $this->assertEquals(98, $susu->fresh()->current_stock);
    }

    public function test_manufacturing_work_order_with_routing_steps_workflow()
    {
        $inventoryService = app(InventoryService::class);

        // Ingredients
        $tepung = Item::create([
            'code' => 'ING-TEPUNG',
            'name' => 'Tepung Terigu Roti',
            'category_id' => $this->rawCat->id,
            'unit_id' => $this->pcsUnit->id,
            'item_type' => 'raw_material',
            'purchase_price' => 5000,
            'selling_price' => 0,
            'current_stock' => 0,
        ]);
        $butter = Item::create([
            'code' => 'ING-BUTTER',
            'name' => 'Butter Premium',
            'category_id' => $this->rawCat->id,
            'unit_id' => $this->pcsUnit->id,
            'item_type' => 'raw_material',
            'purchase_price' => 10000,
            'selling_price' => 0,
            'current_stock' => 0,
        ]);

        // Initial Raw Stock in rawWh
        $inventoryService->recordStockMovement($tepung->id, $this->rawWh->id, 'INIT', 1, 'INIT-1', 'in', 500, 5000);
        $inventoryService->recordStockMovement($butter->id, $this->rawWh->id, 'INIT', 1, 'INIT-1', 'in', 200, 10000);

        // Manufactured Product: Roti O
        $rotiO = Item::create([
            'code' => 'MFG-ROTIO',
            'name' => 'Roti O Spesial',
            'category_id' => $this->finishedCat->id,
            'unit_id' => $this->pcsUnit->id,
            'item_type' => 'manufactured_good',
            'purchase_price' => 15000,
            'selling_price' => 25000,
            'current_stock' => 0,
        ]);

        // BOM: 1 Roti O requires 2 Tepung + 1 Butter
        $bom = Bom::create([
            'code' => 'BOM-ROTIO',
            'name' => 'Resep Roti O Butter',
            'item_id' => $rotiO->id,
            'yield_quantity' => 1,
            'overhead_cost' => 1000,
            'labor_cost' => 2000,
            'is_active' => true,
        ]);
        BomItem::create(['bom_id' => $bom->id, 'item_id' => $tepung->id, 'quantity' => 2, 'unit_cost' => 5000]);
        BomItem::create(['bom_id' => $bom->id, 'item_id' => $butter->id, 'quantity' => 1, 'unit_cost' => 10000]);

        // Routing with 5 steps: A -> B -> C -> D -> E
        $routing = Routing::create([
            'code' => 'RTG-ROTI',
            'name' => 'Jalur Pembuatan Roti',
            'is_active' => true,
        ]);
        $stepA = RoutingStep::create(['routing_id' => $routing->id, 'step_number' => 1, 'name' => 'Step A: Timbang']);
        $stepB = RoutingStep::create(['routing_id' => $routing->id, 'step_number' => 2, 'name' => 'Step B: Oven']);
        $stepC = RoutingStep::create(['routing_id' => $routing->id, 'step_number' => 3, 'name' => 'Step C: QC']);
        $stepD = RoutingStep::create(['routing_id' => $routing->id, 'step_number' => 4, 'name' => 'Step D: Packaging']);
        $stepE = RoutingStep::create(['routing_id' => $routing->id, 'step_number' => 5, 'name' => 'Step E: Masuk Gudang', 'is_final_step' => true]);

        // Create Work Order for 10 units
        $wo = WorkOrder::create([
            'wo_number' => 'WO-TEST-001',
            'item_id' => $rotiO->id,
            'bom_id' => $bom->id,
            'routing_id' => $routing->id,
            'current_step_id' => $stepA->id,
            'target_quantity' => 10,
            'produced_quantity' => 0,
            'raw_material_warehouse_id' => $this->rawWh->id,
            'finished_good_warehouse_id' => $this->finishWh->id,
            'start_date' => date('Y-m-d'),
            'status' => 'in_progress',
        ]);

        // Step through the routing
        $this->post(route('manufacturing.work_orders.advance_step', $wo->id));
        $this->assertEquals($stepB->id, $wo->fresh()->current_step_id);

        $this->post(route('manufacturing.work_orders.advance_step', $wo->id));
        $this->assertEquals($stepC->id, $wo->fresh()->current_step_id);

        $this->post(route('manufacturing.work_orders.advance_step', $wo->id));
        $this->assertEquals($stepD->id, $wo->fresh()->current_step_id);

        $this->post(route('manufacturing.work_orders.advance_step', $wo->id));
        $this->assertEquals($stepE->id, $wo->fresh()->current_step_id);

        // Advance final step E -> Triggers completion & stock addition!
        $this->post(route('manufacturing.work_orders.advance_step', $wo->id));
        $wo->refresh();

        $this->assertEquals('completed', $wo->status);
        $this->assertEquals(10, $wo->produced_quantity);

        // Check Finished Goods stock added in finishWh
        $this->assertEquals(10, $rotiO->fresh()->current_stock);

        // Check Raw Materials deducted from rawWh (10 * 2 = 20 Tepung, 10 * 1 = 10 Butter)
        $this->assertEquals(480, $tepung->fresh()->current_stock);
        $this->assertEquals(190, $butter->fresh()->current_stock);

        // Check Journal generated
        $this->assertNotNull($wo->journal_id);
    }

    public function test_customer_deposit_topup_and_pos_deposit_payment()
    {
        $customer = Contact::create(['code' => 'CUST-DEP-01', 'name' => 'Siti Aminah', 'type' => 'customer']);

        // 1. Topup Deposit Rp 100.000 via DepositController
        $this->post(route('finance.deposits.store'), [
            'contact_id' => $customer->id,
            'type' => 'customer',
            'transaction_type' => 'in',
            'amount' => 100000,
            'account_id' => $this->cashAcc->id,
            'notes' => 'Topup uang muka customer',
        ]);

        $this->assertEquals(100000, $customer->getDepositBalance());

        // Create item to buy in POS
        $item = Item::create([
            'code' => 'BRG-01',
            'name' => 'Produk A',
            'category_id' => $this->finishedCat->id,
            'unit_id' => $this->pcsUnit->id,
            'item_type' => 'finished_good',
            'purchase_price' => 10000,
            'selling_price' => 15000,
            'current_stock' => 50,
        ]);

        // 2. Buy in POS using 'deposit' payment method (Total = 30.000)
        $response = $this->postJson(route('pos.store'), [
            'warehouse_id' => $this->finishWh->id,
            'customer_id' => $customer->id,
            'payment_method' => 'deposit',
            'account_id' => $this->cashAcc->id,
            'paid_amount' => 30000,
            'items' => [
                [
                    'item_id' => $item->id,
                    'quantity' => 2,
                    'unit_price' => 15000,
                ],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Check remaining customer deposit balance = 100.000 - 30.000 = 70.000
        $this->assertEquals(70000, $customer->getDepositBalance());
    }
}
