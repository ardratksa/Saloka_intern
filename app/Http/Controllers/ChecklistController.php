<?php

namespace App\Http\Controllers;

use App\Models\Checklist;
use App\Models\ChecklistDocumentation;
use App\Models\LocationName;
use App\Models\MasterJob;
use App\Models\Period;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ChecklistController extends Controller
{
    // Ambil checklist berdasarkan lokasi + periode + tanggal
    public function index(Request $request)
    {
        $request->validate([
            'location_id' => 'required|exists:location_names,id',
            'periode_id'  => 'required|exists:periods,id',
            'date'        => 'required|date',
        ]);

        $location = LocationName::findOrFail($request->location_id);

        // Ambil semua job aktif untuk tipe lokasi ini
        $jobs = MasterJob::where('location_type_id', $location->location_type_id)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        // Ambil checklist yang sudah ada untuk kombinasi ini
        $existingChecklists = Checklist::with(['documentations'])
            ->where('location_id', $request->location_id)
            ->where('periode_id', $request->periode_id)
            ->where('date', $request->date)
            ->get()
            ->keyBy('job_id');

        // Format response: gabungkan job dengan status checklist
        $items = $jobs->map(function ($job) use (
            $existingChecklists,
            $request,
            $location
        ) {
            $checklist = $existingChecklists->get($job->id);

            return [
                'job_id'         => $job->id,
                'job'            => $job->job,
                'order'          => $job->order,
                'checklist_id'   => $checklist?->id,
                'status'         => $checklist?->status ?? 'pending',
                'note'           => $checklist?->note,
                'pic'            => $checklist?->pic,
                'documentations' => $checklist
                    ? $checklist->documentations->map(fn($d) => [
                        'id'        => $d->id,
                        'image_url' => asset('storage/' . $d->image),
                        'note'      => $d->note,
                    ])
                    : [],
            ];
        });

        $period  = Period::find($request->periode_id);
        $total   = $items->count();
        $done    = $items->where('status', 'done')->count();
        $issues  = $items->where('status', 'issue')->count();

        return response()->json([
            'date'     => $request->date,
            'location' => [
                'id'        => $location->id,
                'name'      => $location->name,
                'type_id'   => $location->location_type_id,
                'type_name' => $location->type->name,
            ],
            'period'   => [
                'id'         => $period->id,
                'name'       => $period->name,
                'time_start' => $period->time_start,
                'time_end'   => $period->time_end,
            ],
            'summary'  => [
                'total'    => $total,
                'done'     => $done,
                'pending'  => $total - $done - $issues,
                'issue'    => $issues,
                'progress' => $total > 0
                    ? (int) round(($done / $total) * 100)
                    : 0,
            ],
            'items'    => $items,
        ]);
    }

    // Update status satu checklist item
    public function update(Request $request)
    {
        $request->validate([
            'location_id' => 'required|exists:location_names,id',
            'job_id'      => 'required|exists:master_jobs,id',
            'periode_id'  => 'required|exists:periods,id',
            'date'        => 'required|date',
            'status'      => 'required|in:pending,done,issue',
            'note'        => 'nullable|string',
            'pic'         => 'nullable|string|max:100',
        ]);

        $location = LocationName::findOrFail($request->location_id);

        $checklist = Checklist::updateOrCreate(
            [
                'location_id' => $request->location_id,
                'job_id'      => $request->job_id,
                'periode_id'  => $request->periode_id,
                'date'        => $request->date,
            ],
            [
                'tipe_id'  => $location->location_type_id,
                'user_id'  => $request->user()->id,
                'status'   => $request->status,
                'note'     => $request->note,
                'pic'      => $request->pic,
            ]
        );

        return response()->json([
            'message'   => 'Checklist berhasil diperbarui.',
            'checklist' => [
                'id'     => $checklist->id,
                'status' => $checklist->status,
                'note'   => $checklist->note,
                'pic'    => $checklist->pic,
            ],
        ]);
    }

    // Upload dokumentasi foto untuk checklist
    public function uploadDoc(Request $request)
    {
        $request->validate([
            'checklist_id' => 'required|exists:checklists,id',
            'image'        => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'note'         => 'nullable|string',
        ]);

        $path = $request->file('image')->store(
            'checklist-docs', 'public'
        );

        $doc = ChecklistDocumentation::create([
            'checklist_id' => $request->checklist_id,
            'image'        => $path,
            'note'         => $request->note,
        ]);

        return response()->json([
            'message' => 'Foto berhasil diupload.',
            'doc'     => [
                'id'        => $doc->id,
                'image_url' => asset('storage/' . $path),
                'note'      => $doc->note,
            ],
        ], 201);
    }

    // Hapus dokumentasi foto
    public function deleteDoc(ChecklistDocumentation $checklistDocumentation)
    {
        Storage::disk('public')->delete($checklistDocumentation->image);
        $checklistDocumentation->delete();

        return response()->json([
            'message' => 'Foto berhasil dihapus.',
        ]);
    }

    // Ambil summary harian semua lokasi (admin)
    public function dailySummary(Request $request)
    {
        $date = $request->date ?? now()->toDateString();

        $checklists = Checklist::with(['location', 'period', 'locationType'])
            ->where('date', $date)
            ->get();

        // Group by location
        $grouped = $checklists->groupBy('location_id')
            ->map(function ($items) {
                $location = $items->first()->location;
                $total    = $items->count();
                $done     = $items->where('status', 'done')->count();
                $issue    = $items->where('status', 'issue')->count();

                return [
                    'location_id'   => $location->id,
                    'location_name' => $location->name,
                    'type'          => $location->type->name,
                    'total'         => $total,
                    'done'          => $done,
                    'issue'         => $issue,
                    'pending'       => $total - $done - $issue,
                    'progress'      => $total > 0
                        ? (int) round(($done / $total) * 100)
                        : 0,
                ];
            })
            ->values();

        return response()->json([
            'date'      => $date,
            'locations' => $grouped,
        ]);
    }
}