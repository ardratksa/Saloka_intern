<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use App\Models\IssueDocumentation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IssueController extends Controller
{
    // Ambil semua issue
    public function index(Request $request)
    {
        $query = Issue::with(['location', 'user', 'documentations']);

        // Staff hanya lihat issue miliknya
        if ($request->user()->isStaff()) {
            $query->where('user_id', $request->user()->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('date', [
                $request->date_from,
                $request->date_to,
            ]);
        }

        $issues = $query->orderByDesc('date')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($i) => $this->formatIssue($i));

        return response()->json($issues);
    }

    // Buat issue baru
    public function store(Request $request)
    {
        $request->validate([
            'checklist_id' => 'nullable|exists:checklists,id',
            'location_id'  => 'required|exists:location_names,id',
            'date'         => 'required|date',
            'type'         => 'required|string|max:100',
            'description'  => 'nullable|string',
            'images'       => 'nullable|array|max:5',
            'images.*'     => 'image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $issue = Issue::create([
            'checklist_id' => $request->checklist_id,
            'user_id'      => $request->user()->id,
            'location_id'  => $request->location_id,
            'date'         => $request->date,
            'type'         => $request->type,
            'description'  => $request->description,
            'status'       => 'open',
            'wa_sent'      => false,
        ]);

        // Upload foto dokumentasi
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('issue-docs', 'public');
                IssueDocumentation::create([
                    'issue_id' => $issue->id,
                    'image'    => $path,
                ]);
            }
        }

        // TODO: Kirim notifikasi WA ke leader (sprint berikutnya)
        // $this->sendWaNotification($issue);

        return response()->json([
            'message' => 'Issue berhasil dilaporkan.',
            'issue'   => $this->formatIssue(
                $issue->load(['location', 'user', 'documentations'])
            ),
        ], 201);
    }

    // Update status issue (admin only)
    public function updateStatus(Request $request, Issue $issue)
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,resolved',
        ]);

        $issue->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Status issue berhasil diperbarui.',
            'issue'   => $this->formatIssue(
                $issue->load(['location', 'user'])
            ),
        ]);
    }

    // Upload foto tambahan ke issue yang sudah ada
    public function uploadDoc(Request $request, Issue $issue)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'note'  => 'nullable|string',
        ]);

        $path = $request->file('image')->store('issue-docs', 'public');

        $doc = IssueDocumentation::create([
            'issue_id' => $issue->id,
            'image'    => $path,
            'note'     => $request->note,
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

    // Hapus foto issue
    public function deleteDoc(IssueDocumentation $issueDocumentation)
    {
        Storage::disk('public')->delete($issueDocumentation->image);
        $issueDocumentation->delete();

        return response()->json([
            'message' => 'Foto berhasil dihapus.',
        ]);
    }

    private function formatIssue(Issue $issue): array
    {
        return [
            'id'           => $issue->id,
            'checklist_id' => $issue->checklist_id,
            'type'         => $issue->type,
            'description'  => $issue->description,
            'location_id'  => $issue->location_id,
            'location'     => $issue->location?->name,
            'reported_by'  => $issue->user?->name,
            'date'         => $issue->date?->toDateString(),
            'status'       => $issue->status,
            'wa_sent'      => $issue->wa_sent,
            'created_at'   => $issue->created_at->format('Y-m-d H:i'),
            'photos'       => $issue->documentations->map(fn($d) => [
                'id'        => $d->id,
                'image_url' => asset('storage/' . $d->image),
                'note'      => $d->note,
            ]),
        ];
    }
}