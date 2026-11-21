<?php

namespace App\Services\Analytics;

use App\Models\Branch;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\ServiceBooking;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleBooking;
use App\Models\VehicleInventory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportService
{
    /**
     * Generate dataset for preview or export for any of the 9 report types.
     */
    public function generateReportData(string $type, User $user, Request $request, array $dateFilter): array
    {
        $branchId = ScopeFilter::getAuthorizedBranchId($user, $request->input('branch_id'));
        $start = $dateFilter['start_date'];
        $end = $dateFilter['end_date'];
        $branch = $branchId ? Branch::find($branchId) : null;

        $data = match ($type) {
            'revenue' => $this->buildRevenueReport($start, $end, $branchId, $branch),
            'bookings' => $this->buildBookingsReport($start, $end, $branchId, $branch),
            'crm_leads' => $this->buildCrmLeadsReport($start, $end, $branchId, $branch),
            'dealers' => $this->buildDealersReport($start, $end, $branchId),
            'inventory' => $this->buildInventoryReport($branchId, $branch),
            'payments' => $this->buildPaymentsReport($start, $end, $branchId, $branch),
            'services' => $this->buildServicesReport($start, $end, $branchId, $branch),
            'customers' => $this->buildCustomersReport($start, $end),
            default => $this->buildSalesReport($start, $end, $branchId, $branch),
        };

        $data['generated_at'] = now()->format('d/m/Y H:i:s');
        $data['period'] = $data['period_label'] ?? '-';

        return $data;
    }

    /**
     * 1. Laporan Penjualan Kendaraan
     */
    protected function buildSalesReport(Carbon $start, Carbon $end, ?int $branchId, ?Branch $branch): array
    {
        $query = VehicleBooking::with(['vehicle', 'customer', 'branch', 'salesOpportunity.assignedSales'])
            ->whereBetween('created_at', [$start, $end])
            ->where(function ($q) {
                $q->whereIn('status', ['confirmed', 'completed'])
                  ->where('payment_status', 'paid')
                  ->orWhere('status', 'completed');
            });

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $records = $query->latest()->get();
        $totalRev = $records->sum('vehicle_price');

        $rows = $records->map(function ($item, $idx) {
            return [
                $idx + 1,
                $item->code,
                $item->created_at->format('d/m/Y H:i'),
                $item->customer ? $item->customer->name : 'N/A',
                $item->vehicle ? $item->vehicle->name : 'N/A',
                $item->branch ? $item->branch->name : 'N/A',
                $item->salesOpportunity?->assignedSales?->name ?? 'Tim Sales',
                number_format($item->vehicle_price, 0, ',', '.'),
                ucfirst($item->status),
                ucfirst($item->payment_status),
            ];
        })->toArray();

        return [
            'type' => 'sales',
            'title' => 'Laporan Penjualan Kendaraan Resmi (SPK Lunas/Selesai)',
            'branch_name' => $branch ? $branch->name : 'Seluruh Cabang Dealer',
            'period_label' => $start->format('d M Y') . ' s/d ' . $end->format('d M Y'),
            'headers' => ['No', 'Kode SPK', 'Tanggal', 'Customer', 'Model Kendaraan', 'Cabang', 'Sales Consultant', 'Nilai Bersih (Rp)', 'Status Unit', 'Pembayaran'],
            'rows' => $rows,
            'summary' => [
                'Total Kendaraan Terjual' => count($rows) . ' Unit',
                'Total Nilai Penjualan' => 'Rp ' . number_format($totalRev, 0, ',', '.'),
            ],
        ];
    }

    /**
     * 2. Laporan Pendapatan
     */
    protected function buildRevenueReport(Carbon $start, Carbon $end, ?int $branchId, ?Branch $branch): array
    {
        $payments = Payment::with(['vehicleBooking.vehicle', 'vehicleBooking.branch', 'user'])
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$start, $end])
            ->when($branchId, fn ($q) => $q->whereHas('vehicleBooking', fn ($bq) => $bq->where('branch_id', $branchId)))
            ->latest('paid_at')
            ->get();

        $totalPaid = $payments->sum('amount');

        $rows = $payments->map(function ($p, $idx) {
            return [
                $idx + 1,
                $p->order_id,
                $p->paid_at ? $p->paid_at->format('d/m/Y H:i') : '-',
                $p->user ? $p->user->name : 'N/A',
                $p->vehicleBooking?->code ?? '-',
                $p->vehicleBooking?->branch?->name ?? 'Pusat',
                ucfirst($p->payment_method ?? 'Midtrans Snap'),
                number_format($p->amount, 0, ',', '.'),
                'Lunas',
            ];
        })->toArray();

        return [
            'type' => 'revenue',
            'title' => 'Laporan Pendapatan & Penerimaan Pembayaran',
            'branch_name' => $branch ? $branch->name : 'Seluruh Cabang Dealer',
            'period_label' => $start->format('d M Y') . ' s/d ' . $end->format('d M Y'),
            'headers' => ['No', 'Order ID', 'Waktu Pembayaran', 'Customer', 'No. Booking SPK', 'Cabang', 'Metode Pembayaran', 'Nominal (Rp)', 'Status'],
            'rows' => $rows,
            'summary' => [
                'Total Transaksi Terverifikasi' => count($rows) . ' Transaksi',
                'Total Pendapatan Diterima' => 'Rp ' . number_format($totalPaid, 0, ',', '.'),
            ],
        ];
    }

    /**
     * 3. Laporan Booking Kendaraan (SPK)
     */
    protected function buildBookingsReport(Carbon $start, Carbon $end, ?int $branchId, ?Branch $branch): array
    {
        $bookings = VehicleBooking::with(['vehicle', 'customer', 'branch'])
            ->whereBetween('created_at', [$start, $end])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->latest()
            ->get();

        $rows = $bookings->map(function ($b, $idx) {
            return [
                $idx + 1,
                $b->code,
                $b->created_at->format('d/m/Y H:i'),
                $b->customer?->name ?? 'N/A',
                $b->vehicle?->name ?? 'N/A',
                $b->branch?->name ?? 'N/A',
                number_format($b->booking_fee, 0, ',', '.'),
                number_format($b->vehicle_price, 0, ',', '.'),
                ucfirst($b->payment_method),
                ucfirst($b->status),
                ucfirst($b->payment_status),
            ];
        })->toArray();

        return [
            'type' => 'bookings',
            'title' => 'Laporan Pemesanan Kendaraan Baru (SPK)',
            'branch_name' => $branch ? $branch->name : 'Seluruh Cabang Dealer',
            'period_label' => $start->format('d M Y') . ' s/d ' . $end->format('d M Y'),
            'headers' => ['No', 'Kode SPK', 'Tanggal', 'Customer', 'Model Kendaraan', 'Cabang', 'Tanda Jadi (Rp)', 'Total Unit (Rp)', 'Metode', 'Status Booking', 'Status Bayar'],
            'rows' => $rows,
            'summary' => [
                'Total SPK Diajukan' => count($rows) . ' Berkas',
                'Total Tanda Jadi' => 'Rp ' . number_format($bookings->sum('booking_fee'), 0, ',', '.'),
            ],
        ];
    }

    /**
     * 4. Laporan CRM & Leads
     */
    protected function buildCrmLeadsReport(Carbon $start, Carbon $end, ?int $branchId, ?Branch $branch): array
    {
        $leads = Lead::with(['customer', 'vehicle', 'assignedSales.branch'])
            ->whereBetween('created_at', [$start, $end])
            ->when($branchId, fn ($q) => $q->whereHas('assignedSales', fn ($sq) => $sq->where('branch_id', $branchId)))
            ->latest()
            ->get();

        $deals = $leads->where('status', 'deal')->count();
        $rate = count($leads) > 0 ? round(($deals / count($leads)) * 100, 1) : 0;

        $rows = $leads->map(function ($l, $idx) {
            return [
                $idx + 1,
                $l->created_at->format('d/m/Y'),
                $l->customer?->name ?? 'N/A',
                $l->customer?->phone ?? '-',
                $l->vehicle?->name ?? 'N/A',
                $l->assignedSales?->branch?->name ?? 'N/A',
                $l->assignedSales?->name ?? 'Belum Ditugaskan',
                ucfirst(str_replace('_', ' ', $l->source ?? 'Website')),
                ucfirst($l->status),
            ];
        })->toArray();

        return [
            'type' => 'crm_leads',
            'title' => 'Laporan Prospek Pelanggan (CRM & Leads)',
            'branch_name' => $branch ? $branch->name : 'Seluruh Cabang Dealer',
            'period_label' => $start->format('d M Y') . ' s/d ' . $end->format('d M Y'),
            'headers' => ['No', 'Tanggal Masuk', 'Nama Prospek', 'No. Telepon', 'Minat Model', 'Cabang', 'Sales Consultant', 'Sumber Lead', 'Tahap / Status'],
            'rows' => $rows,
            'summary' => [
                'Total Prospek Masuk' => count($rows) . ' Leads',
                'Berhasil Deal' => $deals . ' Leads',
                'Rasio Konversi' => $rate . '%',
            ],
        ];
    }

    /**
     * 5. Laporan Performa Dealer
     */
    protected function buildDealersReport(Carbon $start, Carbon $end, ?int $branchId): array
    {
        $dealers = Branch::where('is_active', true)
            ->when($branchId, fn ($q) => $q->where('id', $branchId))
            ->get()
            ->map(function ($d) use ($start, $end) {
                $sales = VehicleBooking::where('branch_id', $d->id)
                    ->whereBetween('created_at', [$start, $end])
                    ->whereIn('status', ['confirmed', 'completed'])
                    ->selectRaw('count(*) as count, sum(vehicle_price) as rev')
                    ->first();

                $leads = Lead::whereHas('assignedSales', fn ($sq) => $sq->where('branch_id', $d->id))->whereBetween('created_at', [$start, $end])->count();
                $deals = Lead::whereHas('assignedSales', fn ($sq) => $sq->where('branch_id', $d->id))->whereBetween('created_at', [$start, $end])->where('status', 'deal')->count();
                $stock = (int) VehicleInventory::where('branch_id', $d->id)->selectRaw('sum(stock_quantity - reserved_quantity) as avail')->value('avail');
                $services = ServiceBooking::where('branch_id', $d->id)
                    ->whereDate('booking_date', '>=', $start->toDateString())
                    ->whereDate('booking_date', '<=', $end->toDateString())
                    ->count();

                return [
                    $d->code,
                    $d->name,
                    $d->city,
                    (int) ($sales->count ?? 0),
                    number_format((float) ($sales->rev ?? 0), 0, ',', '.'),
                    $leads,
                    $leads > 0 ? round(($deals / $leads) * 100, 1) . '%' : '0%',
                    max(0, $stock),
                    $services,
                ];
            })->toArray();

        return [
            'type' => 'dealers',
            'title' => 'Laporan Komparasi Performa Jaringan Dealer',
            'branch_name' => 'Semua Cabang Dealer',
            'period_label' => $start->format('d M Y') . ' s/d ' . $end->format('d M Y'),
            'headers' => ['Kode', 'Nama Dealer', 'Kota', 'Unit Terjual', 'Pendapatan (Rp)', 'Total Leads', 'Konversi', 'Stok Tersedia', 'Servis Booking'],
            'rows' => $dealers,
            'summary' => [
                'Total Dealer Terdata' => count($dealers) . ' Cabang',
            ],
        ];
    }

    /**
     * 6. Laporan Inventory Kendaraan
     */
    protected function buildInventoryReport(?int $branchId, ?Branch $branch): array
    {
        $inv = VehicleInventory::with(['vehicle.categoryRelation', 'branch'])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->get();

        $rows = $inv->map(function ($item, $idx) {
            $avail = max(0, $item->stock_quantity - $item->reserved_quantity);
            $status = $avail <= $item->minimum_stock_alert ? 'Stok Rendah' : 'Tersedia';

            return [
                $idx + 1,
                $item->branch?->name ?? 'N/A',
                $item->vehicle?->name ?? 'N/A',
                $item->vehicle?->model ?? '-',
                $item->vehicle?->categoryRelation?->name ?? $item->vehicle?->category ?? 'Umum',
                number_format($item->vehicle?->price ?? 0, 0, ',', '.'),
                $item->stock_quantity,
                $item->reserved_quantity,
                $avail,
                $status,
            ];
        })->toArray();

        $totalPhysical = $inv->sum('stock_quantity');
        $totalReserved = $inv->sum('reserved_quantity');

        return [
            'type' => 'inventory',
            'title' => 'Laporan Posisi Stok & Inventaris Kendaraan',
            'branch_name' => $branch ? $branch->name : 'Seluruh Cabang Dealer',
            'period_label' => 'Kondisi Real-Time (' . now()->format('d M Y H:i') . ' WIB)',
            'headers' => ['No', 'Cabang Dealer', 'Nama Kendaraan', 'Model', 'Kategori', 'Harga OTR (Rp)', 'Fisik', 'Direservasi', 'Tersedia', 'Status Stok'],
            'rows' => $rows,
            'summary' => [
                'Total Stok Fisik' => $totalPhysical . ' Unit',
                'Total Direservasi' => $totalReserved . ' Unit',
                'Stok Siap Jual' => max(0, $totalPhysical - $totalReserved) . ' Unit',
            ],
        ];
    }

    /**
     * 7. Laporan Pembayaran
     */
    protected function buildPaymentsReport(Carbon $start, Carbon $end, ?int $branchId, ?Branch $branch): array
    {
        $payments = Payment::with(['vehicleBooking.branch', 'user', 'vehicleBooking.vehicle'])
            ->whereBetween('created_at', [$start, $end])
            ->when($branchId, fn ($q) => $q->whereHas('vehicleBooking', fn ($bq) => $bq->where('branch_id', $branchId)))
            ->latest()
            ->get();

        $rows = $payments->map(function ($p, $idx) {
            return [
                $idx + 1,
                $p->order_id,
                $p->created_at->format('d/m/Y H:i'),
                $p->user?->name ?? 'N/A',
                $p->vehicleBooking?->code ?? '-',
                $p->vehicleBooking?->vehicle?->name ?? '-',
                $p->vehicleBooking?->branch?->name ?? 'Pusat',
                number_format($p->amount, 0, ',', '.'),
                ucfirst($p->payment_method ?? 'Midtrans Snap'),
                ucfirst($p->status),
                $p->paid_at ? $p->paid_at->format('d/m/Y H:i') : '-',
            ];
        })->toArray();

        return [
            'type' => 'payments',
            'title' => 'Laporan Seluruh Transaksi Gateway Pembayaran',
            'branch_name' => $branch ? $branch->name : 'Seluruh Cabang Dealer',
            'period_label' => $start->format('d M Y') . ' s/d ' . $end->format('d M Y'),
            'headers' => ['No', 'Order ID', 'Tanggal Transaksi', 'Customer', 'No. Booking SPK', 'Kendaraan', 'Cabang', 'Nominal (Rp)', 'Metode', 'Status Transaksi', 'Waktu Lunas'],
            'rows' => $rows,
            'summary' => [
                'Total Catatan Transaksi' => count($rows) . ' Record',
                'Total Pembayaran Berhasil' => 'Rp ' . number_format($payments->where('status', 'paid')->sum('amount'), 0, ',', '.'),
            ],
        ];
    }

    /**
     * 8. Laporan Servis & After-Sales
     */
    protected function buildServicesReport(Carbon $start, Carbon $end, ?int $branchId, ?Branch $branch): array
    {
        $orders = ServiceOrder::with(['branch', 'user', 'serviceType', 'serviceAdvisor'])
            ->whereBetween('created_at', [$start, $end])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->latest()
            ->get();

        $rows = $orders->map(function ($o, $idx) {
            return [
                $idx + 1,
                $o->order_number,
                $o->created_at->format('d/m/Y H:i'),
                $o->user?->name ?? 'N/A',
                $o->vehicle_name,
                $o->license_plate,
                $o->branch?->name ?? 'N/A',
                $o->serviceType?->name ?? 'Perbaikan Umum',
                $o->serviceAdvisor?->name ?? 'Advisor',
                number_format($o->total_cost, 0, ',', '.'),
                ucfirst(str_replace('_', ' ', $o->status)),
            ];
        })->toArray();

        return [
            'type' => 'services',
            'title' => 'Laporan Layanan Servis Bengkel & Perintah Kerja (WO)',
            'branch_name' => $branch ? $branch->name : 'Seluruh Cabang Dealer',
            'period_label' => $start->format('d M Y') . ' s/d ' . $end->format('d M Y'),
            'headers' => ['No', 'No. Work Order', 'Tanggal Terbit', 'Customer', 'Kendaraan', 'Plat Nomor', 'Bengkel Cabang', 'Jenis Servis', 'Service Advisor', 'Total Biaya (Rp)', 'Status'],
            'rows' => $rows,
            'summary' => [
                'Total Service Order' => count($rows) . ' Unit Pengerjaan',
                'Total Biaya Servis Selesai' => 'Rp ' . number_format($orders->whereIn('status', ['completed', 'delivered'])->sum('total_cost'), 0, ',', '.'),
            ],
        ];
    }

    /**
     * 9. Laporan Pelanggan (Customer)
     */
    protected function buildCustomersReport(Carbon $start, Carbon $end): array
    {
        $customers = User::role('Customer')
            ->whereBetween('created_at', [$start, $end])
            ->withCount(['vehicleBookings', 'serviceBookings', 'testDriveBookings'])
            ->latest()
            ->get();

        $rows = $customers->map(function ($c, $idx) {
            return [
                $idx + 1,
                $c->name,
                $c->email,
                $c->phone ?? '-',
                $c->created_at->format('d/m/Y'),
                $c->vehicle_bookings_count,
                $c->test_drive_bookings_count,
                $c->service_bookings_count,
                ($c->vehicle_bookings_count > 0 || $c->service_bookings_count > 0) ? 'Aktif Bertransaksi' : 'Terdaftar',
            ];
        })->toArray();

        return [
            'type' => 'customers',
            'title' => 'Laporan Pelanggan Terdaftar & Ringkasan Aktivitas',
            'branch_name' => 'Nasional',
            'period_label' => $start->format('d M Y') . ' s/d ' . $end->format('d M Y'),
            'headers' => ['No', 'Nama Pelanggan', 'Email', 'No. Telepon', 'Tanggal Registrasi', 'Booking SPK', 'Test Drive', 'Servis', 'Status Aktivitas'],
            'rows' => $rows,
            'summary' => [
                'Pelanggan Baru Periode Ini' => count($rows) . ' Orang',
            ],
        ];
    }

    /**
     * Stream RFC 4180 compliant CSV export with UTF-8 BOM for Microsoft Excel.
     */
    public function streamCsv(string $type, User $user, Request $request, array $dateFilter): StreamedResponse
    {
        $data = $this->generateReportData($type, $user, $request, $dateFilter);
        $filename = 'laporan-' . strtolower(str_replace('_', '-', $type)) . '-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');

            // 1. Output UTF-8 BOM for Excel compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            // 2. Metadata / Report Header
            fputcsv($handle, [$data['title']]);
            fputcsv($handle, ['Jaringan Dealer:', $data['branch_name']]);
            fputcsv($handle, ['Periode Laporan:', $data['period_label']]);
            fputcsv($handle, ['Waktu Pembuatan:', now()->format('d/m/Y H:i:s') . ' WIB']);
            fputcsv($handle, []); // empty separator line

            // 3. Table Column Headers
            fputcsv($handle, $data['headers']);

            // 4. Rows
            foreach ($data['rows'] as $row) {
                fputcsv($handle, $row);
            }

            // 5. Summary Footer
            if (!empty($data['summary'])) {
                fputcsv($handle, []);
                fputcsv($handle, ['--- RINGKASAN METRIK LAPORAN ---']);
                foreach ($data['summary'] as $label => $val) {
                    fputcsv($handle, [$label, $val]);
                }
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
