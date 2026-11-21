<?php

namespace App\Services\Analytics;

use App\Models\Branch;
use App\Models\Lead;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CrmAnalyticsService
{
    /**
     * Compute CRM & Lead analytics dataset.
     */
    public function getCrmAnalytics(User $user, Request $request, array $dateFilter): array
    {
        $branchId = ScopeFilter::getAuthorizedBranchId($user, $request->input('branch_id'));
        $start = $dateFilter['start_date'];
        $end = $dateFilter['end_date'];

        $baseQuery = Lead::whereBetween('created_at', [$start, $end]);

        if ($branchId) {
            $baseQuery->whereHas('assignedSales', fn ($sq) => $sq->where('branch_id', $branchId));
        }

        if ($user->hasRole('Sales Executive') && !$user->can('leads.view-team')) {
            $baseQuery->where('assigned_to', $user->id);
        }

        $totalLeads = (clone $baseQuery)->count();

        // 1. Funnel Stages Breakdown
        $stages = [
            'new' => 'Prospek Baru',
            'contacted' => 'Sudah Dihubungi',
            'interested' => 'Tertarik / Follow-up',
            'test_drive' => 'Test Drive',
            'negotiation' => 'Negosiasi Penawaran',
            'deal' => 'Deal / Booking SPK',
            'lost' => 'Lost / Tidak Tertarik',
        ];

        $funnel = [];
        foreach ($stages as $statusKey => $label) {
            $cnt = (clone $baseQuery)->where('status', $statusKey)->count();
            $funnel[$statusKey] = [
                'key' => $statusKey,
                'label' => $label,
                'count' => $cnt,
                'percentage' => $totalLeads > 0 ? round(($cnt / $totalLeads) * 100, 1) : 0,
            ];
        }

        $dealCount = $funnel['deal']['count'];
        $lostCount = $funnel['lost']['count'];
        $overallConversionRate = $totalLeads > 0 ? round(($dealCount / $totalLeads) * 100, 1) : 0.0;

        // 2. Conversion Rate by Dealer Branch
        $conversionByDealer = DB::table('branches')
            ->where('is_active', true)
            ->when($branchId, fn ($q) => $q->where('id', $branchId))
            ->get()
            ->map(function ($dealer) use ($start, $end) {
                $total = Lead::whereHas('assignedSales', fn ($sq) => $sq->where('branch_id', $dealer->id))->whereBetween('created_at', [$start, $end])->count();
                $deals = Lead::whereHas('assignedSales', fn ($sq) => $sq->where('branch_id', $dealer->id))->whereBetween('created_at', [$start, $end])->where('status', 'deal')->count();

                return [
                    'id' => $dealer->id,
                    'name' => $dealer->name,
                    'city' => $dealer->city,
                    'total_leads' => $total,
                    'deals' => $deals,
                    'conversion_rate' => $total > 0 ? round(($deals / $total) * 100, 1) : 0.0,
                ];
            })
            ->sortByDesc('total_leads')
            ->values();

        // 3. Conversion Rate by Sales Executive
        $conversionByExecutive = (clone $baseQuery)
            ->whereNotNull('assigned_to')
            ->select('assigned_to', DB::raw('count(*) as total_leads'), DB::raw('sum(case when status = "deal" then 1 else 0 end) as deals'))
            ->groupBy('assigned_to')
            ->orderByDesc('total_leads')
            ->take(10)
            ->get()
            ->map(function ($row) {
                $salesUser = User::find($row->assigned_to);
                $total = (int) $row->total_leads;
                $deals = (int) $row->deals;

                return [
                    'name' => $salesUser ? $salesUser->name : 'Staff #' . $row->assigned_to,
                    'total_leads' => $total,
                    'deals' => $deals,
                    'conversion_rate' => $total > 0 ? round(($deals / $total) * 100, 1) : 0.0,
                ];
            });

        // 4. Lead Sources Breakdown
        $sources = (clone $baseQuery)
            ->select(DB::raw('COALESCE(source, "Website") as source_name'), DB::raw('count(*) as count'))
            ->groupBy('source_name')
            ->orderByDesc('count')
            ->get()
            ->map(function ($item) use ($totalLeads) {
                return [
                    'name' => ucfirst(str_replace('_', ' ', $item->source_name)),
                    'count' => $item->count,
                    'percentage' => $totalLeads > 0 ? round(($item->count / $totalLeads) * 100, 1) : 0,
                ];
            });

        // 5. Average Resolution Time (in days for converted deals)
        $convertedDeals = (clone $baseQuery)->where('status', 'deal')->get(['created_at', 'updated_at']);
        if ($convertedDeals->isNotEmpty()) {
            $totalDays = $convertedDeals->sum(fn ($l) => (float) $l->created_at->diffInDays($l->updated_at));
            $avgDaysToDeal = round($totalDays / $convertedDeals->count(), 1);
        } else {
            $avgDaysToDeal = null;
        }

        // 6. Paginated Leads List
        $leadsQuery = Lead::with(['customer', 'vehicle', 'assignedSales.branch'])
            ->whereBetween('created_at', [$start, $end]);

        if ($branchId) {
            $leadsQuery->whereHas('assignedSales', fn ($sq) => $sq->where('branch_id', $branchId));
        }

        if ($user->hasRole('Sales Executive') && !$user->can('leads.view-team')) {
            $leadsQuery->where('assigned_to', $user->id);
        }

        if ($status = $request->input('status')) {
            $leadsQuery->where('status', $status);
        }

        if ($source = $request->input('source')) {
            $leadsQuery->where('source', $source);
        }

        $leads = $leadsQuery->latest()->paginate(15)->withQueryString();

        return [
            'kpis' => [
                'total_leads' => $totalLeads,
                'deals' => $dealCount,
                'lost' => $lostCount,
                'conversion_rate' => $overallConversionRate,
                'avg_days_to_deal' => $avgDaysToDeal,
            ],
            'funnel' => $funnel,
            'by_dealer' => $conversionByDealer,
            'by_executive' => $conversionByExecutive,
            'sources' => $sources,
            'leads' => $leads,
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
        ];
    }
}
