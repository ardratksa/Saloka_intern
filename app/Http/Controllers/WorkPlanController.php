<?php

namespace App\Http\Controllers;

use App\Models\WorkPlan;
use Illuminate\Http\Request;

class WorkPlanController extends Controller
{
    public function index(Request $request)
    {
        $query = WorkPlan::with(['location', 'user']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        $plans = $query->orderByDesc('created_at')
            ->get()
            ->map(fn($p) => $this->formatPlan($p));

        return response()->json($plans);
    }

    public function store(Request $request)
    {
        $request->validate([
            'location_id'       => 'required|exists:location_names,id',
            'name'              => 'required|string|max:200',
            'type'              => 'required|in:plan,simple',
            'duration_estimate' => 'nullable|string|max:100',
            'planned_start'     => 'nullable|date',
            'notes'             => 'nullable|string',
        ]);

        $plan = WorkPlan::create([
            'user_id'           => $request->user()->id,
            'location_id'       => $request->location_id,
            'name'              => $request->name,
            'type'              => $request->type,
            'duration_estimate' => $request->duration_estimate,
            'planned_start'     => $request->planned_start,
            'notes'             => $request->notes,
            'status'            => 'pending',
        ]);

        return response()->json([
            'message' => $request->type === 'simple'
                ? 'Pekerjaan simple berhasil dicatat.'
                : 'Perencanaan berhasil disimpan.',
            'plan'    => $this->formatPlan(
                $plan->load(['location', 'user'])
            ),
        ], 201);
    }

    public function update(Request $request, WorkPlan $workPlan)
    {
        $request->validate([
            'name'              => 'sometimes|string|max:200',
            'duration_estimate' => 'sometimes|nullable|string|max:100',
            'planned_start'     => 'sometimes|nullable|date',
            'notes'             => 'sometimes|nullable|string',
            'status'            => 'sometimes|in:pending,in_progress,done',
        ]);

        $workPlan->update(
            $request->only([
                'name', 'duration_estimate',
                'planned_start', 'notes', 'status',
            ])
        );

        return response()->json([
            'message' => 'Work plan berhasil diperbarui.',
            'plan'    => $this->formatPlan(
                $workPlan->load(['location', 'user'])
            ),
        ]);
    }

    public function destroy(WorkPlan $workPlan)
    {
        $workPlan->delete();

        return response()->json([
            'message' => 'Work plan berhasil dihapus.',
        ]);
    }

    private function formatPlan(WorkPlan $plan): array
    {
        return [
            'id'                => $plan->id,
            'name'              => $plan->name,
            'type'              => $plan->type,
            'location_id'       => $plan->location_id,
            'location'          => $plan->location?->name,
            'created_by'        => $plan->user?->name,
            'duration_estimate' => $plan->duration_estimate,
            'planned_start'     => $plan->planned_start?->toDateString(),
            'notes'             => $plan->notes,
            'status'            => $plan->status,
            'created_at'        => $plan->created_at->format('Y-m-d H:i'),
        ];
    }
}