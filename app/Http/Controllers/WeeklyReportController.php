<?php

namespace App\Http\Controllers;

use App\Models\Checklist;
use App\Models\Issue;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WeeklyReportController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $start = Carbon::parse(
            $request->start_date
        )->startOfDay();

        $end = Carbon::parse(
            $request->end_date
        )->endOfDay();

        /*
        |--------------------------------------------------------------------------
        | CHECKLIST
        |--------------------------------------------------------------------------
        */

        $checklists = Checklist::with([
            'location.type',
            'period',
            'user',
            'documentations',
        ])
        ->whereBetween('date', [
            $start->toDateString(),
            $end->toDateString(),
        ])
        ->orderByDesc('date')
        ->get();

        /*
        |--------------------------------------------------------------------------
        | ISSUE
        |--------------------------------------------------------------------------
        */

        $issues = Issue::with([
            'location',
            'user',
            'documentations',
        ])
        ->whereBetween('date', [
            $start->toDateString(),
            $end->toDateString(),
        ])
        ->orderByDesc('created_at')
        ->get()
        ->map(function ($issue) {

            return [

                'id' =>
                    $issue->id,

                'type' =>
                    $issue->type,

                'description' =>
                    $issue->description,

                'location' =>
                    $issue->location?->name,

                'reported_by' =>
                    $issue->user?->name,

                'date' =>
                    $issue->date?->toDateString(),

                'status' =>
                    $issue->status,

                'created_at' =>
                    $issue->created_at
                        ->format('Y-m-d H:i'),

                'photos' =>
                    $issue->documentations
                        ->map(function ($doc) {

                            return [

                                'id' =>
                                    $doc->id,

                                'image_url' =>
                                    asset(
                                        'storage/' .
                                        $doc->image
                                    ),

                                'note' =>
                                    $doc->note,
                            ];
                        })
                        ->values(),
            ];
        });

        /*
        |--------------------------------------------------------------------------
        | SUMMARY
        |--------------------------------------------------------------------------
        */

        $totalChecklist =
            $checklists->count();

        $doneChecklist =
            $checklists
                ->where('status', 'done')
                ->count();

        $issueCount =
            $issues->count();

        return response()->json([

            'start_date' =>
                $start->toDateString(),

            'end_date' =>
                $end->toDateString(),

            'summary' => [

                'total' =>
                    $totalChecklist,

                'done' =>
                    $doneChecklist,

                'pct' =>
                    $totalChecklist > 0
                        ? round(
                            ($doneChecklist / $totalChecklist) * 100,
                            2
                        )
                        : 0,

                'issues' =>
                    $issueCount,
            ],

            'checklists' =>

                $checklists->map(function ($c) {

                    return [

                        'id' =>
                            $c->id,

                        'date' =>
                            $c->date?->toDateString(),

                        'location' =>
                            $c->location?->name,

                        'location_type' =>
                            $c->location?->type?->name,

                        'period' =>
                            $c->period?->name,

                        'pic' =>
                            $c->user?->name,

                        'status' =>
                            $c->status,

                        'note' =>
                            $c->note,

                        'total_docs' =>
                            $c->documentations->count(),
                    ];
                }),

            'issues' =>
                $issues,
        ]);
    }
}