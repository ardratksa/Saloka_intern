<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Checklist;
use App\Models\MasterJob;
use App\Models\LocationName;
use App\Models\Issue;

class ChecklistController extends Controller
{
    /**
     * GET /checklist
     */
    public function index(Request $request)
    {
        $request->validate([
            'location_id' => 'required|integer',
            'periode_id'  => 'required|integer',
            'date'        => 'required|date',
        ]);

        $location = LocationName::findOrFail(
            $request->location_id
        );

        $jobs = MasterJob::where(
            'location_type_id',
            $location->location_type_id
        )
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        $items = $jobs->map(function ($job) use ($request) {

            $checklist = Checklist::where([
                'job_id'      => $job->id,
                'location_id' => $request->location_id,
                'periode_id'  => $request->periode_id,
                'date'        => $request->date,
            ])->first();

            $hasIssue = false;
            $issueId = null;

            if ($checklist) {

                $issue = Issue::where(
                    'checklist_id',
                    $checklist->id
                )
                ->where('status', '!=', 'resolved')
                ->latest()
                ->first();

                if ($issue) {
                    $hasIssue = true;
                    $issueId = $issue->id;
                }
            }

            return [
                'job_id' => $job->id,
                'name'   => $job->job,

                'status' => $checklist?->status ?? 'pending',

                'note' => $checklist?->note,

                'checklist_id' => $checklist?->id,

                'has_issue' => $hasIssue,

                'issue_id' => $issueId,
            ];
        });

        return response()->json([
            'location_id' => $location->id,
            'location'    => $location->name,
            'items'       => $items,
        ]);
    }

    /**
     * POST /checklist/update
     */
    public function update(Request $request)
    {
        $request->validate([
            'location_id' => 'required|integer',
            'job_id'      => 'required|integer',
            'periode_id'  => 'required|integer',
            'date'        => 'required|date',

            'status' => 'required|in:pending,done,issue',

            'note' => 'nullable|string',
            'pic'  => 'nullable|string',
        ]);

        $location = LocationName::findOrFail(
            $request->location_id
        );

        $checklist = Checklist::updateOrCreate(
            [
                'job_id'      => $request->job_id,
                'location_id' => $request->location_id,
                'periode_id'  => $request->periode_id,
                'date'        => $request->date,
            ],
            [
                'user_id' => auth()->id(),

                'status' => $request->status,
                'note'   => $request->note,

                'pic' => $request->pic,

                'tipe_id' => $location->location_type_id,
            ]
        );

        return response()->json([
            'message' => 'Checklist berhasil disimpan',
            'data'    => $checklist,
        ]);
    }

    /**
     * Dashboard Summary
     */
    public function dailySummary(Request $request)
    {
        $date = $request->date ?? now()->toDateString();

        $checklists = Checklist::with([
            'location.type',
            'period',
            'user'
        ])
        ->whereDate('date', $date)
        ->get();

        $sessions = $checklists
            ->groupBy(function ($c) {
                return $c->location_id . '-' .
                    $c->periode_id . '-' .
                    $c->user_id;
            })
            ->map(function ($items) use ($date) {

                $first = $items->first();

                $total = $items->count();

                $done = $items
                    ->where('status', 'done')
                    ->count();

                $issue = $items
                    ->where('status', 'issue')
                    ->count();

                $score = $total > 0
                    ? round(($done / $total) * 100)
                    : 0;

                $status = 'Tidak OK';

                if ($score >= 80) {
                    $status = 'OK';
                } elseif ($score >= 60) {
                    $status = 'Perlu Perbaikan';
                }

                return [
                    'id' => $first->id,

                    'date' => $date,

                    'time' => optional($first->created_at)
                        ->format('H:i'),

                    'location_id' => $first->location_id,

                    'location' => optional($first->location)
                        ->name,

                    'location_type' => optional($first->location?->type)
                        ->name,

                    'periode_id' => $first->periode_id,

                    'shift' => optional($first->period)
                        ->name,

                    'shift_time' => optional($first->period)
                        ? substr($first->period->time_start, 0, 5)
                        : null,

                    'pic' => $first->pic
                        ?? optional($first->user)->name,

                    'total' => $total,

                    'done' => $done,

                    'issue' => $issue,

                    'score' => $score,

                    'status' => $status,
                ];
            })
            ->values();

        $totalSessions = $sessions->count();

        $okCount = $sessions
            ->where('status', 'OK')
            ->count();

        $perbaikan = $sessions
            ->where('status', 'Perlu Perbaikan')
            ->count();

        $tidakOk = $sessions
            ->where('status', 'Tidak OK')
            ->count();

        $avgScore = $totalSessions > 0
            ? round($sessions->avg('score'))
            : 0;

        return response()->json([

            'date' => $date,

            'summary' => [
                'total' => $totalSessions,
                'ok' => $okCount,
                'perbaikan' => $perbaikan,
                'tidak_ok' => $tidakOk,
                'avg_score' => $avgScore,
            ],

            'sessions' => $sessions,

            'locations' => $checklists
                ->groupBy('location_id')
                ->map(function ($items) {

                    $total = $items->count();

                    $done = $items
                        ->where('status', 'done')
                        ->count();

                    $issue = $items
                        ->where('status', 'issue')
                        ->count();

                    $progress = $total > 0
                        ? round(($done / $total) * 100)
                        : 0;

                    $first = $items->first();

                    return [
                        'location_id' => $first->location_id,

                        'location_name' => optional($first->location)
                            ->name,

                        'type' => optional($first->location?->type)
                            ->name,

                        'total' => $total,

                        'done' => $done,

                        'issue' => $issue,

                        'progress' => $progress,
                    ];
                })
                ->values(),
        ]);
    }
}