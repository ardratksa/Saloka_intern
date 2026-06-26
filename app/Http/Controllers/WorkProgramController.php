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
            'job'
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

        WorkProgram::where(
            'status',
            'pending'
        )
        ->get()
        ->each(function ($item) {

            if (!$item->time_range) {
                return;
            }

            $parts =
                explode(
                    '-',
                    $item->time_range
                );

            if (count($parts) !== 2) {
                return;
            }

            $deadline =
                trim($parts[1]);

            if (
                now()->format('H:i')
                > $deadline
            ) {

                $item->update([
                    'status' => 'late'
                ]);
            }
        });

        $programs = $query
            ->latest()
            ->get();

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

            'status' => 'nullable|in:pending,done,late',

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

    public function uploadEvidence(
        Request $request,
        WorkProgram $workProgram
    ) {
        $request->validate([
            'before_image' => 'nullable|image|max:5120',
            'after_image' => 'required|image|max:5120',
            'remark' => 'nullable|string',
        ]);

        $beforePath = null;

        if ($request->hasFile('before_image')) {
            $beforePath = $request
                ->file('before_image')
                ->store(
                    'work-programs/before',
                    'public'
                );
        }

        $afterPath = $request
            ->file('after_image')
            ->store(
                'work-programs/after',
                'public'
            );

        $status = 'done';

        if ($workProgram->time_range) {

            $parts = explode(
                '-',
                $workProgram->time_range
            );

            if (count($parts) === 2) {

                $endTime = trim($parts[1]);

                $deadline = now()
                    ->setTimeFromTimeString(
                        $endTime
                    )
                    ->addMinutes(30);

                if (now()->gt($deadline)) {
                    $status = 'late';
                }
            }
        }

        WorkProgramEvidence::create([
            'work_program_id' => $workProgram->id,

            'before_image' => $beforePath,

            'after_image' => $afterPath,

            'remark' => $request->remark,

            'date' => now(),
        ]);

        $workProgram->update([
            'status' => $status,
            'has_evidence' => true,
            'completed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Bukti pekerjaan berhasil disimpan',
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