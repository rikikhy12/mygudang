<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\FixedAsset;
use App\Models\Item;
use App\Models\Journal;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Services\FinancialReportService;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(FinancialReportService $reportService)
    {
        $today = date('Y-m-d');
        $startOfMonth = date('Y-m-01');

        // Cash & Bank balances
        $cashAccounts = ChartOfAccount::where('is_cash_or_bank', true)->where('is_active', true)->get();
        $totalCash = 0;
        foreach ($cashAccounts as $acc) {
            $totalCash += $acc->calculateBalance(null, $today);
        }

        // Accounts Receivable (1101) & Accounts Payable (2001)
        $arAcc = ChartOfAccount::where('code', '1101')->first();
        $totalAR = $arAcc ? $arAcc->calculateBalance(null, $today) : 0;

        $apAcc = ChartOfAccount::where('code', '2001')->first();
        $totalAP = $apAcc ? $apAcc->calculateBalance(null, $today) : 0;

        // Inventory Value
        $invTrading = ChartOfAccount::where('code', '1201')->first();
        $invManuf = ChartOfAccount::where('code', '1202')->first();
        $totalInventory = ($invTrading ? $invTrading->calculateBalance(null, $today) : 0) +
                          ($invManuf ? $invManuf->calculateBalance(null, $today) : 0);

        // Fixed Assets Net Book Value
        $totalFixedAssets = FixedAsset::where('status', 'active')->sum('current_book_value');

        // Financial Profit & Loss for this month
        $pnl = $reportService->getProfitAndLoss($startOfMonth, $today);
        $monthSales = $pnl['total_revenue'];
        $monthExpenses = $pnl['total_expense'] ?? 0;
        $monthNetProfit = $pnl['net_profit'];
        $monthPurchases = PurchaseOrder::where('order_date', '>=', $startOfMonth)->sum('grand_total');

        // Recent Transactions
        $recentJournals = Journal::with('creator')->latest('transaction_date')->take(6)->get();
        $recentSales = SalesOrder::with('customer')->latest()->take(5)->get();
        $recentPurchases = PurchaseOrder::with('supplier')->latest()->take(5)->get();

        // Low stock alerts
        $lowStockItems = Item::with('category', 'unit')
            ->where('is_active', true)
            ->where('item_type', '!=', 'service')
            ->whereColumn('current_stock', '<=', 'min_stock')
            ->get();

        // 6-Month Trend Data for ApexCharts
        $chartMonths = [];
        $chartSales = [];
        $chartPurchases = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthStart = $date->copy()->startOfMonth()->toDateString();
            $monthEnd = $date->copy()->endOfMonth()->toDateString();
            $chartMonths[] = $date->translatedFormat('M Y');

            $salesSum = SalesOrder::whereBetween('order_date', [$monthStart, $monthEnd])->sum('grand_total');
            $purchasesSum = PurchaseOrder::whereBetween('order_date', [$monthStart, $monthEnd])->sum('grand_total');

            $chartSales[] = (float) $salesSum;
            $chartPurchases[] = (float) $purchasesSum;
        }

        return view('dashboard.index', compact(
            'totalCash', 'totalAR', 'totalAP', 'totalInventory', 'totalFixedAssets',
            'monthSales', 'monthPurchases', 'monthExpenses', 'monthNetProfit', 'recentJournals', 'recentSales',
            'recentPurchases', 'lowStockItems',
            'chartMonths', 'chartSales', 'chartPurchases'
        ));
    }
}
