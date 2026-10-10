<?php

namespace Tests\Feature;

use App\Models\ChartOfAccount;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryHierarchyAndItemGlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_category_parent_child_hierarchy(): void
    {
        $admin = User::first();

        // 1. Create Parent Category
        $parent = ItemCategory::create([
            'name' => 'Elektronik & Gadget',
            'code' => 'CAT-ELEC',
            'type' => 'finished_good',
            'sales_account_id' => ChartOfAccount::where('code', '4001')->first()->id,
        ]);

        // 2. Create Sub-category with parent_id
        $sub = ItemCategory::create([
            'parent_id' => $parent->id,
            'name' => 'Laptop & Komputer',
            'code' => 'CAT-LAPTOP',
            'type' => 'finished_good',
            'sales_account_id' => ChartOfAccount::where('code', '4001')->first()->id,
        ]);

        $this->assertEquals($parent->id, $sub->parent_id);
        $this->assertEquals('Elektronik & Gadget', $sub->parent->name);
        $this->assertEquals('Elektronik & Gadget > Laptop & Komputer', $sub->hierarchy_name);
        $this->assertTrue($parent->children->contains($sub));

        // 3. Test HTTP Controller index and create
        $this->actingAs($admin)->get(route('categories.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('categories.create'))->assertStatus(200);
        $this->actingAs($admin)->get(route('categories.edit', $sub->id))->assertStatus(200);
    }

    public function test_item_specific_gl_account_override_and_fallback(): void
    {
        $admin = User::first();
        $unit = Unit::first();

        $coaCatSales = ChartOfAccount::where('code', '4001')->first();
        $coaCustomSales = ChartOfAccount::where('code', '4002')->first();
        $coaCatInv = ChartOfAccount::where('code', '1201')->first();
        $coaCustomInv = ChartOfAccount::where('code', '1202')->first();

        // Category with default GL
        $cat = ItemCategory::create([
            'name' => 'Hardware Komputer',
            'code' => 'CAT-HW',
            'type' => 'finished_good',
            'inventory_account_id' => $coaCatInv->id,
            'sales_account_id' => $coaCatSales->id,
        ]);

        // Item 1: Uses Category Default (null item GL)
        $itemDefault = Item::create([
            'code' => 'BRG-DEF-01',
            'name' => 'Standard Keyboard',
            'category_id' => $cat->id,
            'unit_id' => $unit->id,
            'item_type' => 'finished_good',
            'purchase_price' => 50000,
            'selling_price' => 80000,
        ]);

        $this->assertEquals($coaCatSales->id, $itemDefault->getEffectiveSalesAccountId());
        $this->assertEquals($coaCatInv->id, $itemDefault->getEffectiveInventoryAccountId());

        // Item 2: Uses Custom Specific GL
        $itemCustom = Item::create([
            'code' => 'BRG-CUST-01',
            'name' => 'Custom Gaming Laptop',
            'category_id' => $cat->id,
            'unit_id' => $unit->id,
            'item_type' => 'finished_good',
            'purchase_price' => 10000000,
            'selling_price' => 15000000,
            'inventory_account_id' => $coaCustomInv->id,
            'sales_account_id' => $coaCustomSales->id,
        ]);

        $this->assertEquals($coaCustomSales->id, $itemCustom->getEffectiveSalesAccountId());
        $this->assertEquals($coaCustomInv->id, $itemCustom->getEffectiveInventoryAccountId());

        // 4. Test HTTP Item create and edit views
        $this->actingAs($admin)->get(route('items.create'))->assertStatus(200);
        $this->actingAs($admin)->get(route('items.edit', $itemCustom->id))->assertStatus(200);
    }
}
