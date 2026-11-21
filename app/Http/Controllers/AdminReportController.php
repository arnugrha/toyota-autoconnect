<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Services\Analytics\DateRangeFilter;
use App\Services\Analytics\ReportExportService;
use App\Services\Analytics\ScopeFilter;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminReportController extends Controller
{
    public function __construct(
        protected ReportExportService $reportExportService
    ) {
    }

    /**
     * Display Reporting Center with interactive report preview and filter controls.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $type = $request->input('type', 'sales');
        $dateFilter = DateRangeFilter::resolve($request);

        $reportData = $this->reportExportService->generateReportData($type, $user, $request, $dateFilter);
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        $reportTypes = [
            'sales' => '1. Laporan Penjualan Kendaraan',
            'revenue' => '2. Laporan Pendapatan Transaksi',
            'bookings' => '3. Laporan Booking Kendaraan (SPK)',
            'crm_leads' => '4. Laporan CRM & Prospek Leads',
            'dealers' => '5. Laporan Performa Cabang Dealer',
            'inventory' => '6. Laporan Inventaris & Stok Unit',
            'payments' => '7. Laporan Gateway Pembayaran',
            'services' => '8. Laporan Layanan Servis Bengkel',
            'customers' => '9. Laporan Pelanggan & Aktivitas',
        ];

        return view('admin.reports.index', [
            'reportData' => $reportData,
            'currentType' => $type,
            'reportTypes' => $reportTypes,
            'dateFilter' => $dateFilter,
            'branches' => $branches,
            'selectedBranchId' => ScopeFilter::getAuthorizedBranchId($user, $request->input('branch_id')),
        ]);
    }

    /**
     * Stream CSV download for the selected report and filters.
     */
    public function export(Request $request): StreamedResponse
    {
        $user = $request->user();
        $type = $request->input('type', 'sales');
        $dateFilter = DateRangeFilter::resolve($request);

        return $this->reportExportService->streamCsv($type, $user, $request, $dateFilter);
    }
}

