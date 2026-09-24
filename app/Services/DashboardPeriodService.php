<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardPeriodService
{
    /**
     * Parse period and date parameters from request with fallback to current period.
     *
     * @param Request $request
     * @param string $periodKey
     * @param string $dateKey
     * @param string $defaultPeriod
     * @return array
     */
    public function parsePeriod(
        Request $request,
        string $periodKey = 'period',
        string $dateKey = 'date',
        string $defaultPeriod = 'monthly'
    ): array {
        $now = Carbon::now('Asia/Jakarta');
        $validPeriods = ['daily', 'weekly', 'monthly', 'yearly'];

        $period = $request->input($periodKey, $defaultPeriod);
        if (!in_array($period, $validPeriods, true)) {
            $period = $defaultPeriod;
        }

        $dateInput = $request->input($dateKey);

        if (empty($dateInput)) {
            if ($period === 'yearly') {
                $dateInput = $now->format('Y');
            } elseif ($period === 'monthly') {
                $dateInput = $now->format('Y-m');
            } else {
                $dateInput = $now->format('Y-m-d');
            }
        }

        try {
            if ($period === 'yearly') {
                $parsed = Carbon::createFromFormat('Y-m-d', $dateInput . '-01-01', 'Asia/Jakarta');
                $start = $parsed->copy()->startOfYear();
                $end = $parsed->copy()->endOfYear();
                $label = $start->format('Y');
            } elseif ($period === 'monthly') {
                $parsed = Carbon::createFromFormat('Y-m-d', $dateInput . '-01', 'Asia/Jakarta');
                $start = $parsed->copy()->startOfMonth();
                $end = $parsed->copy()->endOfMonth();
                $label = $start->translatedFormat('F Y');
            } else {
                $parsed = Carbon::createFromFormat('Y-m-d', $dateInput, 'Asia/Jakarta');
                if ($period === 'weekly') {
                    $start = $parsed->copy()->startOfWeek();
                    $end = $parsed->copy()->endOfWeek();
                    $label = $start->format('d M') . ' - ' . $end->format('d M Y');
                } else { // daily
                    $start = $parsed->copy()->startOfDay();
                    $end = $parsed->copy()->endOfDay();
                    $label = $start->format('d M Y');
                }
            }
        } catch (\Exception $e) {
            $period = 'monthly';
            $dateInput = $now->format('Y-m');
            $start = $now->copy()->startOfMonth();
            $end = $now->copy()->endOfMonth();
            $label = $start->translatedFormat('F Y');
        }

        return [
            'period'     => $period,
            'date_input' => $dateInput,
            'start_date' => $start,
            'end_date'   => $end,
            'label'      => $label,
        ];
    }
}
