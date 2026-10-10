<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\ItemCategory;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->toDateString());
        $branchId = $request->get('branch_id'); // null / '' means all branches
        $categoryId = $request->get('category_id'); // null / '' means all categories

        $branches = Branch::where('is_active', true)->orderByDesc('is_main')->orderBy('name')->get();
        $categories = ItemCategory::orderBy('name')->get();

        // 1. Sales Invoice Query for summary metrics
        $invoicesQuery = SalesInvoice::with(['branch', 'customer'])
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled');

        if ($branchId) {
            $invoicesQuery->where('branch_id', $branchId);
        }

        $invoices = (clone $invoicesQuery)->orderByDesc('invoice_date')->paginate(20)->withQueryString();

        $totalRevenue = (clone $invoicesQuery)->sum('grand_total');
        $totalPaid = (clone $invoicesQuery)->sum('paid_amount');
        $totalUnpaid = max(0, $totalRevenue - $totalPaid);
        $totalTransactions = (clone $invoicesQuery)->count();

        // 2. Sales Item Level Query for Category & Item Breakdown
        $itemsQuery = SalesInvoiceItem::query()
            ->join('sales_invoices', 'sales_invoice_items.sales_invoice_id', '=', 'sales_invoices.id')
            ->join('items', 'sales_invoice_items.item_id', '=', 'items.id')
            ->leftJoin('item_categories', 'items.category_id', '=', 'item_categories.id')
            ->leftJoin('branches', 'sales_invoices.branch_id', '=', 'branches.id')
            ->whereBetween('sales_invoices.invoice_date', [$startDate, $endDate])
            ->where('sales_invoices.status', '!=', 'cancelled');

        if ($branchId) {
            $itemsQuery->where('sales_invoices.branch_id', $branchId);
        }

        if ($categoryId) {
            $itemsQuery->where('items.category_id', $categoryId);
        }

        // Category Breakdown
        $categoryBreakdown = (clone $itemsQuery)
            ->select(
                DB::raw('COALESCE(item_categories.name, "Tanpa Kategori") as category_name'),
                DB::raw('SUM(sales_invoice_items.quantity) as total_qty'),
                DB::raw('SUM(sales_invoice_items.subtotal) as total_sales')
            )
            ->groupBy('item_categories.name')
            ->orderByDesc('total_sales')
            ->get();

        // Branch Breakdown
        $branchBreakdown = (clone $itemsQuery)
            ->select(
                DB::raw('COALESCE(branches.name, "Cabang Utama") as branch_name'),
                DB::raw('COALESCE(branches.code, "CBG") as branch_code'),
                DB::raw('SUM(sales_invoice_items.quantity) as total_qty'),
                DB::raw('SUM(sales_invoice_items.subtotal) as total_sales'),
                DB::raw('COUNT(DISTINCT sales_invoices.id) as total_invoices')
            )
            ->groupBy('branches.name', 'branches.code')
            ->orderByDesc('total_sales')
            ->get();

        // Top Sold Products
        $topProducts = (clone $itemsQuery)
            ->select(
                'items.id as item_id',
                'items.name as item_name',
                'items.code as item_code',
                DB::raw('COALESCE(item_categories.name, "Tanpa Kategori") as category_name'),
                DB::raw('SUM(sales_invoice_items.quantity) as total_qty'),
                DB::raw('SUM(sales_invoice_items.subtotal) as total_sales')
            )
            ->groupBy('items.id', 'items.name', 'items.code', 'item_categories.name')
            ->orderByDesc('total_sales')
            ->limit(10)
            ->get();

        return view('sales.reports.index', compact(
            'invoices',
            'branches',
            'categories',
            'startDate',
            'endDate',
            'branchId',
            'categoryId',
            'totalRevenue',
            'totalPaid',
            'totalUnpaid',
            'totalTransactions',
            'categoryBreakdown',
            'branchBreakdown',
            'topProducts'
        ));
    }

    /**
     * Sales Commission Report
     */
    public function commissions(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->toDateString());
        $salespersonId = $request->get('salesperson_id');
        $branchId = $request->get('branch_id');

        $salespersons = User::orderBy('name')->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        $query = SalesInvoice::with(['salesperson', 'customer', 'branch'])
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('salesperson_id');

        if ($salespersonId) {
            $query->where('salesperson_id', $salespersonId);
        }

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $invoices = (clone $query)->orderByDesc('invoice_date')->paginate(25)->withQueryString();

        $totalSales = (clone $query)->sum('subtotal');
        $totalCommission = (clone $query)->sum('commission_amount');
        $totalInvoicesCount = (clone $query)->count();

        // Summary per Salesperson
        $salesSummary = (clone $query)
            ->select(
                'salesperson_id',
                DB::raw('COUNT(id) as total_invoices'),
                DB::raw('SUM(subtotal) as total_subtotal'),
                DB::raw('SUM(commission_amount) as total_commission'),
                DB::raw('AVG(commission_rate) as avg_rate')
            )
            ->groupBy('salesperson_id')
            ->with('salesperson')
            ->get();

        return view('sales.reports.commissions', compact(
            'invoices',
            'salespersons',
            'branches',
            'salesSummary',
            'startDate',
            'endDate',
            'salespersonId',
            'branchId',
            'totalSales',
            'totalCommission',
            'totalInvoicesCount'
        ));
    }
}
