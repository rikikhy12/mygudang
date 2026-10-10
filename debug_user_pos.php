<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Branch;
use App\Models\Warehouse;
use App\Models\Item;
use App\Models\StockLedger;

echo "=== USERS & BRANCHES ===\n";
$users = User::all();
foreach ($users as $u) {
    echo "User: {$u->name} (ID {$u->id}) | branch_id: " . ($u->branch_id ?? 'NULL') . "\n";
}

echo "\n=== WAREHOUSES & BRANCHES ===\n";
$warehouses = Warehouse::all();
foreach ($warehouses as $w) {
    echo "Warehouse ID: {$w->id} | Name: {$w->name} | branch_id: " . ($w->branch_id ?? 'NULL') . "\n";
}

echo "\n=== STOCK LEDGERS SUM BY WAREHOUSE ===\n";
$items = Item::whereIn('code', ['DSP-SL01', 'DSP-BT01', 'DSP-GL01'])->get();
foreach ($items as $item) {
    echo "Item: {$item->code} ({$item->name})\n";
    foreach ($warehouses as $w) {
        $stk = $item->calculateStock($w->id);
        echo "  - Warehouse ID {$w->id} ({$w->name}): {$stk}\n";
    }
    echo "  - Total calculateStock(null): " . $item->calculateStock() . "\n";
}
