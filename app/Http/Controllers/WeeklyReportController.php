<?php

namespace App\Http\Controllers;

use App\Models\Checklist;
use App\Models\Issue;
use App\Models\LocationName;
use App\Models\Period;
use Illuminate\Http\Request;

class WeeklyReportController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'week_start' => 'required|date',
        ]);

        $weekStart = $request->week_start;
        $weekEnd   = date('Y-m-d', strtotime($weekStart . ' +6 days'));

        $checklists = Checklist::with(['location.type', 'period'])
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->get();

        // Progress per hari
        $dailyProgress = [];
        for ($i = 0; $i < 7; $i++) {
            $day  = date('Y-m-d', strtotime($weekStart . " +{$i} days"));
            $dayC = $checklists->where('date', $day);
            $tot  = $dayC->count();
            $done = $dayC->where('status', 'done')->count();

            $dailyProgress[$day] = [
                'total' => $tot,
                'done'  => $done,
                'pct'   => $tot > 0
                    ? (int) round(($done / $tot) * 100)
                    : 0,
            ];
        }

        // Progress per periode
        $periods        = Period::where('is_active', true)->get();
        $periodProgress = $periods->map(function ($period) use ($checklists) {
            $pc   = $checklists->where('periode_id', $period->id);
            $tot  = $pc->count();
            $done = $pc->where('status', 'done')->count();

            return [
                'period_id'   => $period->id,
                'period_name' => $period->name,
                'time_start'  => $period->time_start,
                'time_end'    => $period->time_end,
                'total'       => $tot,
                'done'        => $done,
                'pct'         => $tot > 0
                    ? (int) round(($done / $tot) * 100)
                    : 0,
            ];
        });

        // Progress per lokasi
        $locations        = LocationName::with('type')
            ->where('is_active', true)
            ->get();
        $locationProgress = $locations->map(function ($loc) use ($checklists) {
            $lc   = $checklists->where('location_id', $loc->id);
            $tot  = $lc->count();
            $done = $lc->where('status', 'done')->count();
            $iss  = $lc->where('status', 'issue')->count();

            return [
                'location_id'   => $loc->id,
                'location_name' => $loc->name,
                'type'          => $loc->type->name,
                'total'         => $tot,
                'done'          => $done,
                'issue'         => $iss,
                'pct'           => $tot > 0
                    ? (int) round(($done / $tot) * 100)
                    : 0,
            ];
        })->filter(fn($l) => $l['total'] > 0)->values();

        // Issues minggu ini
        $issues = Issue::with(['location', 'user'])
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($i) => [
                'id'          => $i->id,
                'type'        => $i->type,
                'description' => $i->description,
                'location'    => $i->location?->name,
                'reported_by' => $i->user?->name,
                'date'        => $i->date?->toDateString(),
                'status'      => $i->status,
                'created_at'  => $i->created_at->format('Y-m-d H:i'),
            ]);

        // Summary total
        $totalItems = $checklists->count();
        $doneItems  = $checklists->where('status', 'done')->count();

        return response()->json([
            'week_start'        => $weekStart,
            'week_end'          => $weekEnd,
            'summary'           => [
                'total'   => $totalItems,
                'done'    => $doneItems,
                'pct'     => $totalItems > 0
                    ? (int) round(($doneItems / $totalItems) * 100)
                    : 0,
                'issues'  => $issues->count(),
            ],
            'daily_progress'    => $dailyProgress,
            'period_progress'   => $periodProgress,
            'location_progress' => $locationProgress,
            'issues'            => $issues,
        ]);
    }
}