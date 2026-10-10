<?php

namespace Tests\Feature;

use App\Models\ChartOfAccount;
use App\Models\Contact;
use App\Models\FixedAsset;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Role;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DedicatedCrudViewsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_all_dedicated_create_and_edit_views_render_successfully(): void
    {
        $admin = User::where('email', 'admin@erp.local')->first();
        $this->actingAs($admin);

        // 1. Users
        $this->get(route('users.index'))->assertStatus(200);
        $this->get(route('users.create'))->assertStatus(200);
        $this->get(route('users.edit', $admin->id))->assertStatus(200);

        // 2. Roles
        $role = Role::first();
        $this->get(route('roles.index'))->assertStatus(200);
        $this->get(route('roles.create'))->assertStatus(200);
        $this->get(route('roles.edit', $role->id))->assertStatus(200);

        // 3. COA
        $coa = ChartOfAccount::first();
        $this->get(route('coa.index'))->assertStatus(200);
        $this->get(route('coa.create'))->assertStatus(200);
        $this->get(route('coa.edit', $coa->id))->assertStatus(200);

        // 4. Categories
        $category = ItemCategory::first();
        $this->get(route('categories.index'))->assertStatus(200);
        $this->get(route('categories.create'))->assertStatus(200);
        $this->get(route('categories.edit', $category->id))->assertStatus(200);

        // 5. Items
        $item = Item::first();
        $this->get(route('items.index'))->assertStatus(200);
        $this->get(route('items.create'))->assertStatus(200);
        $this->get(route('items.edit', $item->id))->assertStatus(200);
        $this->get(route('items.stock-card', $item->id))->assertStatus(200);

        // 6. Contacts
        $contact = Contact::first();
        $this->get(route('contacts.index'))->assertStatus(200);
        $this->get(route('contacts.create'))->assertStatus(200);
        $this->get(route('contacts.edit', $contact->id))->assertStatus(200);

        // 7. Warehouses
        $warehouse = Warehouse::first();
        $this->get(route('warehouses.index'))->assertStatus(200);
        $this->get(route('warehouses.create'))->assertStatus(200);
        $this->get(route('warehouses.edit', $warehouse->id))->assertStatus(200);

        // 8. Fixed Assets
        $asset = FixedAsset::first();
        $this->get(route('assets.index'))->assertStatus(200);
        $this->get(route('assets.create'))->assertStatus(200);
        $this->get(route('assets.edit', $asset->id))->assertStatus(200);
        $this->get(route('assets.show', $asset->id))->assertStatus(200);
        $this->get(route('assets.depreciation'))->assertStatus(200);

        // 9. Finance
        $this->get(route('finance.index'))->assertStatus(200);
        $this->get(route('finance.cash-in.create'))->assertStatus(200);
        $this->get(route('finance.cash-out.create'))->assertStatus(200);
        $this->get(route('finance.transfer.create'))->assertStatus(200);

        // 10. Dashboard & Accounting Reports
        $this->get(route('dashboard'))->assertStatus(200);
        $this->get(route('accounting.reports.balance-sheet'))->assertStatus(200);
        $this->get(route('accounting.reports.profit-loss'))->assertStatus(200);
        $this->get(route('accounting.reports.trial-balance'))->assertStatus(200);
        $this->get(route('accounting.reports.general-ledger'))->assertStatus(200);
    }
}
