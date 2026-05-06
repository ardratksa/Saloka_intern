<?php

namespace App\Http\Controllers;

use App\Models\ScReport;
use App\Models\ScReportPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ScReportController extends Controller
{
    // Ambil SC Report by minggu
    public function index(Request $request)
    {
        $weekStart = $request->week_start
            ?? now()->startOfWeek()->toDateString();

        $reports = ScReport::where('week_start', $weekStart)
            ->with(['photos', 'picUser'])
            ->get()
            ->map(fn($r) => $this->formatReport($r));

        return response()->json($reports);
    }

    // Buat SC Report baru (admin only)
    public function store(Request $request)
    {
        $request->validate([
            'task_name'  => 'required|string|max:200',
            'week_label' => 'required|string|max:100',
            'week_start' => 'required|date',
        ]);

        $report = ScReport::create([
            'task_name'  => $request->task_name,
            'week_label' => $request->week_label,
            'week_start' => $request->week_start,
            'status'     => 'pending',
        ]);

        return response()->json([
            'message' => 'SC Report berhasil dibuat.',
            'report'  => $this->formatReport($report),
        ], 201);
    }

    // Update PIC dan catatan
    public function update(Request $request, ScReport $scReport)
    {
        $request->validate([
            'pic_name'    => 'nullable|string|max:100',
            'pic_user_id' => 'nullable|exists:users,id',
            'notes'       => 'nullable|string',
        ]);

        $scReport->update(
            $request->only('pic_name', 'pic_user_id', 'notes')
        );

        return response()->json([
            'message' => 'SC Report berhasil diperbarui.',
            'report'  => $this->formatReport(
                $scReport->load(['photos', 'picUser'])
            ),
        ]);
    }

    // Upload foto before / progress / after
    public function uploadPhoto(Request $request, ScReport $scReport)
    {
        $request->validate([
            'phase' => 'required|in:before,progress,after',
            'photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        // Hapus foto lama untuk phase yang sama
        $existing = $scReport->photos()
            ->where('phase', $request->phase)
            ->first();

        if ($existing) {
            Storage::disk('public')->delete($existing->photo_path);
            $existing->delete();
        }

        $path = $request->file('photo')->store(
            'sc-report-photos', 'public'
        );

        $scReport->photos()->create([
            'phase'      => $request->phase,
            'photo_path' => $path,
        ]);

        // Update status report
        $phases = $scReport->fresh()->photos->pluck('phase')->toArray();

        $status = 'pending';
        if (count($phases) > 0) {
            $status = 'in_progress';
        }
        if (
            in_array('before', $phases) &&
            in_array('progress', $phases) &&
            in_array('after', $phases)
        ) {
            $status = 'completed';
        }

        $scReport->update(['status' => $status]);

        return response()->json([
            'message'   => 'Foto berhasil diupload.',
            'phase'     => $request->phase,
            'photo_url' => asset('storage/' . $path),
            'status'    => $status,
        ]);
    }

    // Hapus SC Report (admin only)
    public function destroy(ScReport $scReport)
    {
        foreach ($scReport->photos as $photo) {
            Storage::disk('public')->delete($photo->photo_path);
        }

        $scReport->delete();

        return response()->json([
            'message' => 'SC Report berhasil dihapus.',
        ]);
    }

    private function formatReport(ScReport $report): array
    {
        $photos = [];
        foreach ($report->photos as $p) {
            $photos[$p->phase] = asset('storage/' . $p->photo_path);
        }

        $phaseCount = count($photos);

        return [
            'id'         => $report->id,
            'task_name'  => $report->task_name,
            'week_label' => $report->week_label,
            'week_start' => $report->week_start?->toDateString(),
            'pic_name'   => $report->pic_name,
            'pic_user'   => $report->picUser?->name,
            'notes'      => $report->notes,
            'status'     => $report->status,
            'progress'   => (int) round(($phaseCount / 3) * 100),
            'photos'     => $photos,
        ];
    }
}