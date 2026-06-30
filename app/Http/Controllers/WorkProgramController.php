<?php

namespace App\Http\Controllers;

use App\Models\WorkProgram;
use App\Models\WorkProgramEvidence;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class WorkProgramController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LIST
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query = WorkProgram::with([
             'job',
             'evidences',
        ]);;

        /*
        |--------------------------------------------------------------------------
        | FILTER
        |--------------------------------------------------------------------------
        */

        if ($request->filled('plan')) {
            $query->where('plan', $request->plan);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        /*
        |--------------------------------------------------------------------------
        | GET
        |--------------------------------------------------------------------------
        */

        $programs = $query
            ->latest()
            ->get()
            ->map(function ($program) {

                $program->evidences->transform(function ($evidence) {

                    if ($evidence->before_image) {
                        $evidence->before_image =
                            asset('storage/' . $evidence->before_image);
                    }

                    if ($evidence->after_image) {
                        $evidence->after_image =
                            asset('storage/' . $evidence->after_image);
                    }

                    return $evidence;
                });

                return $program;
            });
        return response()->json([
            'success' => true,
            'data' => $programs
        ]);

        
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validated = $request->validate([

            'location_type_id' => 'required|exists:location_types,id',

            'area_id' => 'nullable',

            'area_name' => 'nullable|string',

            'location_name' => 'nullable|string',

            'sub_location' => 'nullable|string',

            'job_id' => 'required|exists:master_jobs,id',

            'category' => 'required|in:plan,out_plan',

            'plan' => 'required|in:weekly,monthly',

            'how_to_do' => 'nullable|string',

            'time_range' => 'nullable|string',

            'pic' => 'nullable|string',

            'month' => 'required|integer',

            'year' => 'required|integer',

            'scheduled_dates' => 'nullable|array',

            'checker' => 'nullable|string',

            'remark' => 'nullable|string',
        ]);

        $validated['user_id'] = auth()->id();

        $program = WorkProgram::create($validated);

        return response()->json([
            'message' => 'Program kerja berhasil dibuat',
            'data' => $program,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, WorkProgram $workProgram)
    {
        $validated = $request->validate([

            'location_type_id' => 'sometimes|required|exists:location_types,id',

            'area_id' => 'nullable',

            'area_name' => 'nullable|string',

            'location_name' => 'nullable|string',

            'sub_location' => 'nullable|string',

            'job_id' => 'sometimes|required|exists:master_jobs,id',

            'category' => 'sometimes|required|in:plan,out_plan',

            'plan' => 'sometimes|required|in:weekly,monthly',

            'how_to_do' => 'nullable|string',

            'time_range' => 'nullable|string',

            'pic' => 'nullable|string',

            'month' => 'sometimes|required|integer',

            'year' => 'sometimes|required|integer',

            'scheduled_dates' => 'nullable|array',

            'status' => 'nullable|in:pending,progress,done,late',

            'checker' => 'nullable|string',

            'remark' => 'nullable|string',
        ]);

        /*
        |--------------------------------------------------------------------------
        | AUTO COMPLETE
        |--------------------------------------------------------------------------
        */

        if (
            isset($validated['status']) &&
            $validated['status'] === 'done'
        ) {
            $validated['completed_at'] = now();
        }

        $workProgram->update($validated);

        return response()->json([
            'message' => 'Program kerja berhasil diupdate',
            'data' => $workProgram,
        ]);
    }

    public function uploadBefore(
        Request $request,
        WorkProgram $workProgram
    )
    {
        $request->validate([

            'image' => 'required|image|max:5120',

            'remark' => 'required|string',

        ]);

        $path = $request
            ->file('image')
            ->store(
                'work-programs/before',
                'public'
            );

        WorkProgramEvidence::create([

            'work_program_id' => $workProgram->id,

            'before_image' => $path,

            'before_remark' => $request->remark,

            'date' => now(),

        ]);

        $workProgram->update([

            'status' => 'progress',

            'has_evidence' => true,

        ]);

        return response()->json([

            'message' => 'Bukti awal berhasil disimpan'

        ]);
    }

    public function uploadAfter(
        Request $request,
        WorkProgram $workProgram
    )
    {
        $request->validate([

            'image'=>'required|image|max:5120',

            'remark'=>'required|string',

            'evidence_id'=>'required|exists:work_program_evidences,id',

        ]);

        $evidence =
            WorkProgramEvidence::findOrFail(
                $request->evidence_id
            );

        $path =
            $request
                ->file('image')
                ->store(
                    'work-programs/after',
                    'public'
                );

        $evidence->update([

            'after_image'=>$path,

            'after_remark'=>$request->remark,

        ]);

        $status='done';

        if($workProgram->time_range){

            $parts=explode(
                '-',
                $workProgram->time_range
            );

            if(count($parts)==2){

                $deadline=
                    now()
                    ->setTimeFromTimeString(
                        trim($parts[1])
                    );

                if(now()->gt($deadline)){

                    $status='late';

                }

            }

        }

        $workProgram->update([

            'status'=>$status,

            'completed_at'=>now(),

        ]);

        return response()->json([

            'message'=>'Pekerjaan selesai'

        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    public function destroy(WorkProgram $workProgram)
    {
        $workProgram->delete();

        return response()->json([
            'message' => 'Program kerja berhasil dihapus',
        ]);
    }
}