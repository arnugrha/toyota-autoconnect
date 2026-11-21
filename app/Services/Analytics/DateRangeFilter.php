<?php

namespace App\Services\Analytics;

use Carbon\Carbon;
use Illuminate\Http\Request;

class DateRangeFilter
{
    /**
     * Resolve date range and previous comparison period from request.
     *
     * @return array{
     *     period: string,
     *     start_date: Carbon,
     *     end_date: Carbon,
     *     prev_start_date: Carbon,
     *     prev_end_date: Carbon,
     *     label: string
     * }
     */
    public static function resolve(Request $request): array
    {
        $period = $request->input('period', 'last_30_days');
        $now = now();

        switch ($period) {
            case 'today':
                $start = $now->copy()->startOfDay();
                $end = $now->copy()->endOfDay();
                $prevStart = $now->copy()->subDay()->startOfDay();
                $prevEnd = $now->copy()->subDay()->endOfDay();
                $label = 'Hari Ini (' . $start->translatedFormat('d M Y') . ')';
                break;

            case 'last_7_days':
                $start = $now->copy()->subDays(6)->startOfDay();
                $end = $now->copy()->endOfDay();
                $prevStart = $start->copy()->subDays(7);
                $prevEnd = $start->copy()->subSecond();
                $label = '7 Hari Terakhir (' . $start->translatedFormat('d M') . ' - ' . $end->translatedFormat('d M Y') . ')';
                break;

            case 'this_month':
                $start = $now->copy()->startOfMonth()->startOfDay();
                $end = $now->copy()->endOfMonth()->endOfDay();
                $prevStart = $now->copy()->subMonthNoOverflow()->startOfMonth()->startOfDay();
                $prevEnd = $now->copy()->subMonthNoOverflow()->endOfMonth()->endOfDay();
                $label = 'Bulan Ini (' . $start->translatedFormat('F Y') . ')';
                break;

            case 'last_month':
                $start = $now->copy()->subMonthNoOverflow()->startOfMonth()->startOfDay();
                $end = $now->copy()->subMonthNoOverflow()->endOfMonth()->endOfDay();
                $prevStart = $start->copy()->subMonthNoOverflow()->startOfMonth()->startOfDay();
                $prevEnd = $start->copy()->subMonthNoOverflow()->endOfMonth()->endOfDay();
                $label = 'Bulan Lalu (' . $start->translatedFormat('F Y') . ')';
                break;

            case 'this_year':
                $start = $now->copy()->startOfYear()->startOfDay();
                $end = $now->copy()->endOfYear()->endOfDay();
                $prevStart = $now->copy()->subYear()->startOfYear()->startOfDay();
                $prevEnd = $now->copy()->subYear()->endOfYear()->endOfDay();
                $label = 'Tahun Ini (' . $start->format('Y') . ')';
                break;

            case 'custom':
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $start = Carbon::parse($request->input('start_date'))->startOfDay();
                    $end = Carbon::parse($request->input('end_date'))->endOfDay();
                } else {
                    $start = $now->copy()->subDays(29)->startOfDay();
                    $end = $now->copy()->endOfDay();
                }
                $daysDiff = max(1, $start->diffInDays($end) + 1);
                $prevStart = $start->copy()->subDays($daysDiff);
                $prevEnd = $start->copy()->subSecond();
                $label = $start->translatedFormat('d M Y') . ' - ' . $end->translatedFormat('d M Y');
                break;

            case 'last_30_days':
            default:
                $period = 'last_30_days';
                $start = $now->copy()->subDays(29)->startOfDay();
                $end = $now->copy()->endOfDay();
                $prevStart = $start->copy()->subDays(30);
                $prevEnd = $start->copy()->subSecond();
                $label = '30 Hari Terakhir (' . $start->translatedFormat('d M') . ' - ' . $end->translatedFormat('d M Y') . ')';
                break;
        }

        return [
            'period' => $period,
            'start_date' => $start,
            'end_date' => $end,
            'prev_start_date' => $prevStart,
            'prev_end_date' => $prevEnd,
            'label' => $label,
        ];
    }

    /**
     * Calculate percentage change safely handling division by zero.
     *
     * @return array{change: float, formatted: string, direction: 'up'|'down'|'same'}
     */
    public static function calcPercentageChange(float|int $current, float|int $previous): array
    {
        $current = (float) $current;
        $previous = (float) $previous;

        if ($previous == 0.0) {
            if ($current > 0.0) {
                return ['change' => 100.0, 'formatted' => '+100%', 'direction' => 'up'];
            }
            return ['change' => 0.0, 'formatted' => '0%', 'direction' => 'same'];
        }

        $change = (($current - $previous) / abs($previous)) * 100.0;
        $changeRound = round($change, 1);

        if ($changeRound > 0) {
            return ['change' => $changeRound, 'formatted' => "+{$changeRound}%", 'direction' => 'up'];
        } elseif ($changeRound < 0) {
            return ['change' => $changeRound, 'formatted' => "{$changeRound}%", 'direction' => 'down'];
        }

        return ['change' => 0.0, 'formatted' => '0%', 'direction' => 'same'];
    }
}

