<?php

namespace App\Http\Controllers;

use App\Models\WorkProgram;
use Illuminate\Http\Request;

class WorkProgramReportController extends Controller
{
    public function index(Request $request)
    {
        $data = WorkProgram::with([
            'job',
            'evidences',
        ])
        ->latest()
        ->get()
        ->map(function ($item) {

            return [

                'id' => $item->id,

                'job_name' =>
                    $item->job?->job,

                'location_name' =>
                    $item->location_name,

                'sub_location' =>
                    $item->sub_location,

                'category' =>
                    $item->category,

                'plan' =>
                    $item->plan,

                'time_range' =>
                    $item->time_range,

                'status' =>
                    $item->status,

                'has_evidence' =>
                    $item->has_evidence,

                'evidences' =>
                    $item->evidences->map(function ($evidence) {

                        return [

                            'id' => $evidence->id,

                            'before_image' =>
                                $evidence->before_image
                                    ? asset('storage/' . $evidence->before_image)
                                    : null,

                            'after_image' =>
                                $evidence->after_image
                                    ? asset('storage/' . $evidence->after_image)
                                    : null,

                            'remark' =>
                                $evidence->remark,

                            'date' =>
                                $evidence->date,
                        ];
                    }),

                'created_at' =>
                    $item->created_at->format('Y-m-d'),

                'area_name' =>
                    $item->area_name,

                'checker' =>
                    $item->checker,

                'remark' =>
                    $item->remark,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}