<?php

namespace Tests\Feature;

use App\Services\FinancialReportService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_balance_sheet_is_balanced(): void
    {
        $this->seed(DatabaseSeeder::class);

        $reportService = app(FinancialReportService::class);
        $balanceSheet = $reportService->getBalanceSheet(date('Y-m-d'));

        $this->assertTrue($balanceSheet['is_balanced'], "Balance sheet should be balanced! Assets: {$balanceSheet['total_assets']}, L+E: {$balanceSheet['total_liabilities_and_equity']}");
        $this->assertEquals($balanceSheet['total_assets'], $balanceSheet['total_liabilities_and_equity']);
    }
}
