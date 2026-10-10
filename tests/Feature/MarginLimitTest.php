<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarginLimitTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Warehouse $warehouse;

    protected Contact $customer;

    protected Unit $unit;

    protected ItemCategory $physicalCategory;

    protected ItemCategory $serviceCategory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->user = User::first();
        $this->warehouse = Warehouse::first();
        $this->customer = Contact::customers()->first();
        $this->unit = Unit::first();

        // Create Physical Category with 10% min margin
        $this->physicalCategory = ItemCategory::create([
            'name' => 'Elektronik Konsumen',
            'code' => 'ELK-01',
            'type' => 'finished_good',
            'min_margin_percent' => 10.0,
            'description' => 'Barang fisik dengan batas margin 10%',
        ]);

        // Create Service Category (No margin restriction)
        $this->serviceCategory = ItemCategory::create([
            'name' => 'Layanan Instalasi IT',
            'code' => 'JSA-IT',
            'type' => 'service',
            'min_margin_percent' => 0,
            'description' => 'Jasa non-stok fisik',
        ]);
    }

    public function test_physical_item_inherits_category_margin_and_blocks_below_margin_sales_order(): void
    {
        // Purchase price: 100,000 -> Min selling price at 10% margin is 110,000
        $item = Item::create([
            'code' => 'BRG-ELK-01',
            'name' => 'Speaker Bluetooth',
            'category_id' => $this->physicalCategory->id,
            'unit_id' => $this->unit->id,
            'item_type' => 'finished_good',
            'purchase_price' => 100000,
            'selling_price' => 125000,
            'current_stock' => 50,
            'is_active' => true,
        ]);

        $this->assertEquals(10.0, $item->getEffectiveMinMarginPercent());
        $this->assertEquals(110000, $item->getMinimumSellingPrice());

        // Attempting to sell at 105,000 (Margin is 5% < 10%) should fail validation
        $response = $this->actingAs($this->user)->post(route('sales.so.store'), [
            'customer_id' => $this->customer->id,
            'order_date' => date('Y-m-d'),
            'items' => [
                [
                    'item_id' => $item->id,
                    'quantity' => 1,
                    'unit_price' => 105000,
                ],
            ],
        ]);

        $response->assertSessionHasErrors(['items']);

        // Selling at 110,000 (Exact 10% margin) or above should succeed
        $validResponse = $this->actingAs($this->user)->post(route('sales.so.store'), [
            'customer_id' => $this->customer->id,
            'order_date' => date('Y-m-d'),
            'items' => [
                [
                    'item_id' => $item->id,
                    'quantity' => 1,
                    'unit_price' => 110000,
                ],
            ],
        ]);

        $validResponse->assertRedirect(route('sales.so.index'));
        $validResponse->assertSessionHas('success');
    }

    public function test_item_can_override_category_margin(): void
    {
        // Category margin is 10%, but item overrides to 20%
        // Purchase price: 100,000 -> Min selling price becomes 120,000
        $item = Item::create([
            'code' => 'BRG-ELK-VIP',
            'name' => 'Headset Premium',
            'category_id' => $this->physicalCategory->id,
            'unit_id' => $this->unit->id,
            'item_type' => 'finished_good',
            'purchase_price' => 100000,
            'selling_price' => 150000,
            'min_margin_percent' => 20.0,
            'current_stock' => 50,
            'is_active' => true,
        ]);

        $this->assertEquals(20.0, $item->getEffectiveMinMarginPercent());
        $this->assertEquals(120000, $item->getMinimumSellingPrice());

        // Selling at 115,000 (Valid for category 10%, but invalid for item 20%) should fail
        $response = $this->actingAs($this->user)->post(route('sales.so.store'), [
            'customer_id' => $this->customer->id,
            'order_date' => date('Y-m-d'),
            'items' => [
                [
                    'item_id' => $item->id,
                    'quantity' => 1,
                    'unit_price' => 115000,
                ],
            ],
        ]);

        $response->assertSessionHasErrors(['items']);
    }

    public function test_service_items_are_exempt_from_margin_and_stock_limits(): void
    {
        // Service item with 0 purchase price
        $service = Item::create([
            'code' => 'JSA-001',
            'name' => 'Jasa Pemasangan Jaringan',
            'category_id' => $this->serviceCategory->id,
            'unit_id' => $this->unit->id,
            'item_type' => 'service',
            'purchase_price' => 0,
            'selling_price' => 50000,
            'current_stock' => 0,
            'is_active' => true,
        ]);

        $this->assertFalse($service->isPhysical());
        $this->assertEquals(0.0, $service->getMinimumSellingPrice());

        // Selling service at any price should succeed without margin error
        $response = $this->actingAs($this->user)->post(route('sales.so.store'), [
            'customer_id' => $this->customer->id,
            'order_date' => date('Y-m-d'),
            'items' => [
                [
                    'item_id' => $service->id,
                    'quantity' => 2,
                    'unit_price' => 25000,
                ],
            ],
        ]);

        $response->assertRedirect(route('sales.so.index'));
        $response->assertSessionHas('success');
    }
}
