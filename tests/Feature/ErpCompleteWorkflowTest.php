<?php

namespace Tests\Feature;

use App\Models\ChartOfAccount;
use App\Models\Contact;
use App\Models\DeliveryOrder;
use App\Models\GoodsReceipt;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\FinancialReportService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErpCompleteWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_full_erp_business_workflow(): void
    {
        $admin = User::where('email', 'admin@erp.local')->first();
        $this->assertNotNull($admin);

        // 1. Test Login
        $loginResponse = $this->post('/login', [
            'email' => 'admin@erp.local',
            'password' => 'password',
        ]);
        $loginResponse->assertRedirect('/');

        // 2. Test Dashboard Access
        $dashboardResponse = $this->actingAs($admin)->get('/');
        $dashboardResponse->assertStatus(200);

        // 3. Test Master Data (Categories with GL & Items)
        $coaInv = ChartOfAccount::where('code', '1201')->first();
        $coaCogs = ChartOfAccount::where('code', '5001')->first();
        $coaPur = ChartOfAccount::where('code', '1301')->first();
        $coaSales = ChartOfAccount::where('code', '4001')->first();

        $catResponse = $this->actingAs($admin)->post('/categories', [
            'code' => 'CAT-TEST-FG',
            'name' => 'Kategori Pengujian Barang Jadi',
            'type' => 'finished_good',
            'inventory_account_id' => $coaInv->id,
            'cogs_account_id' => $coaCogs->id,
            'purchase_account_id' => $coaPur->id,
            'sales_account_id' => $coaSales->id,
        ]);
        $catResponse->assertRedirect('/categories');

        $createdCategory = ItemCategory::where('code', 'CAT-TEST-FG')->first();
        $this->assertNotNull($createdCategory);

        // 4. Test Procurement Workflow (PO -> GRN -> Invoice)
        $supplier = Contact::where('type', 'supplier')->first();
        $warehouse = Warehouse::first();
        $item = Item::where('code', 'LAP-001')->first();

        $initialStock = $item->current_stock;

        // Create PO
        $poResponse = $this->actingAs($admin)->post('/procurement/po', [
            'supplier_id' => $supplier->id,
            'order_date' => date('Y-m-d'),
            'expected_date' => date('Y-m-d', strtotime('+3 days')),
            'items' => [
                [
                    'item_id' => $item->id,
                    'quantity' => 10,
                    'unit_price' => 12500000,
                ],
            ],
        ]);
        $poResponse->assertRedirect('/procurement/po');

        $po = PurchaseOrder::latest('id')->first();
        $this->assertNotNull($po);
        $this->assertEquals(125000000, $po->grand_total);

        // Receive Goods (GRN)
        $grnResponse = $this->actingAs($admin)->post('/procurement/grn', [
            'purchase_order_id' => $po->id,
            'warehouse_id' => $warehouse->id,
            'receipt_date' => date('Y-m-d'),
            'delivery_note_number' => 'SJ-TEST-001',
            'items' => [
                [
                    'item_id' => $item->id,
                    'quantity' => 10,
                ],
            ],
        ]);
        $grnResponse->assertRedirect('/procurement/grn');

        $item->refresh();
        $this->assertEquals($initialStock + 10, $item->current_stock);

        $grn = GoodsReceipt::latest('id')->first();
        $this->assertNotNull($grn->journal_id);

        // 5. Test Sales Workflow (SO -> DO -> Invoice)
        $customer = Contact::where('type', 'customer')->first();

        // Create SO
        $soResponse = $this->actingAs($admin)->post('/sales/so', [
            'customer_id' => $customer->id,
            'order_date' => date('Y-m-d'),
            'items' => [
                [
                    'item_id' => $item->id,
                    'quantity' => 5,
                    'unit_price' => 15200000,
                ],
            ],
        ]);
        $soResponse->assertRedirect('/sales/so');

        $so = SalesOrder::latest('id')->first();
        $this->assertNotNull($so);
        $this->assertEquals(76000000, $so->grand_total);

        // Delivery Order (DO)
        $doResponse = $this->actingAs($admin)->post('/sales/do', [
            'sales_order_id' => $so->id,
            'warehouse_id' => $warehouse->id,
            'delivery_date' => date('Y-m-d'),
            'driver_name' => 'Bambang',
            'vehicle_number' => 'B 9999 XYZ',
            'items' => [
                [
                    'item_id' => $item->id,
                    'quantity' => 5,
                ],
            ],
        ]);
        $doResponse->assertRedirect('/sales/do');

        $item->refresh();
        $this->assertEquals($initialStock + 5, $item->current_stock); // 20 + 10 - 5 = 25

        $do = DeliveryOrder::latest('id')->first();
        $this->assertNotNull($do->journal_id);

        // Sales Invoice (AR)
        $sinvResponse = $this->actingAs($admin)->post('/sales/invoices', [
            'sales_order_id' => $so->id,
            'delivery_order_id' => $do->id,
            'customer_id' => $customer->id,
            'invoice_date' => date('Y-m-d'),
            'due_date' => date('Y-m-d', strtotime('+14 days')),
            'items' => [
                [
                    'item_id' => $item->id,
                    'quantity' => 5,
                    'unit_price' => 15200000,
                ],
            ],
        ]);
        $sinvResponse->assertRedirect('/sales/invoices');

        // 6. Test Fixed Asset Monthly Depreciation
        $deprResponse = $this->actingAs($admin)->post(route('assets.depreciation.process'), [
            'period' => date('Y-m'),
            'depreciation_date' => date('Y-m-d'),
        ]);
        $deprResponse->assertRedirect(route('assets.depreciation'));

        // 7. Verify Financial Statements (Balance Sheet Must Be Balanced!)
        $reportService = app(FinancialReportService::class);
        $bs = $reportService->getBalanceSheet(date('Y-m-d'));

        $this->assertTrue($bs['is_balanced'], "Balance sheet must stay balanced! Assets: {$bs['total_assets']}, L+E: {$bs['total_liabilities_and_equity']}");
        $this->assertEquals($bs['total_assets'], $bs['total_liabilities_and_equity']);

        // Check P&L
        $pnl = $reportService->getProfitAndLoss(date('Y-01-01'), date('Y-m-d'));
        $this->assertGreaterThan(0, $pnl['total_revenue']);
        $this->assertGreaterThan(0, $pnl['total_cogs']);
    }
}
