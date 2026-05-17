<?php

namespace App\Http\Controllers;

use App\Models\MasterJob;
use Illuminate\Http\Request;

class MasterJobController extends Controller
{
    // Ambil semua job, bisa filter by location_type_id
    public function index(Request $request)
    {
        $query = MasterJob::with('locationType');

        if ($request->filled('location_type_id')) {
            $query->where('location_type_id', $request->location_type_id);
        }

        $jobs = $query->orderBy('order')->get()
            ->map(fn($j) => [
                'id'               => $j->id,
                'job'              => $j->job,
                'order'            => $j->order,
                'is_active'        => $j->is_active,
                'location_type_id' => $j->location_type_id,
                'location_type'    => $j->locationType->name,
            ]);

        return response()->json($jobs);
    }

    // Tambah job baru (admin only)
    public function store(Request $request)
    {
        $request->validate([
            'location_type_id' => 'required|exists:location_types,id',
            'job'              => 'required|string|max:255',
            'order'            => 'nullable|integer',
            'is_active'        => 'nullable|boolean',
        ]);

        // Auto order kalau tidak diisi
        $maxOrder = MasterJob::where('location_type_id', $request->location_type_id)
            ->max('order') ?? 0;

        $job = MasterJob::create([
            'location_type_id' => $request->location_type_id,
            'job'              => $request->job,
            'order'            => $request->order ?? $maxOrder + 1,
            'is_active'        => $request->is_active ?? true,
        ]);

        return response()->json([
            'message' => 'Job berhasil ditambahkan.',
            'job'     => $job->load('locationType'),
        ], 201);
    }

    // Update job (admin only)
    public function update(Request $request, MasterJob $masterJob)
    {
        $request->validate([
            'job'       => 'sometimes|string|max:255',
            'order'     => 'sometimes|integer',
            'is_active' => 'sometimes|boolean',
        ]);

        $masterJob->update(
            $request->only('job', 'order', 'is_active')
        );

        return response()->json([
            'message' => 'Job berhasil diperbarui.',
            'job'     => $masterJob->load('locationType'),
        ]);
    }

    // Hapus job (admin only)
    public function destroy(MasterJob $masterJob)
    {
        $masterJob->delete();

        return response()->json([
            'message' => 'Job berhasil dihapus.',
        ]);
    }
}