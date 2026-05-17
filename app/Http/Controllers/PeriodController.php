<?php

namespace App\Http\Controllers;

use App\Models\Period;
use Illuminate\Http\Request;


class PeriodController extends Controller
{
    // Ambil semua periode
    public function index()
    {
    $periods = Period::orderBy('time_start')->get();

    return response()->json($periods);
    }

    // Tambah periode (admin only)
    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:100',
            'time_start' => 'required|date_format:H:i',
            'time_end'   => 'required|date_format:H:i|after:time_start',
            'is_active' => 'nullable|boolean',
        ]);

        $period = Period::create([
            'name'       => $request->name,
            'time_start' => $request->time_start . ':00',
            'time_end'   => $request->time_end . ':00',
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json([
            'message' => 'Periode berhasil ditambahkan.',
            'period'  => $period,
        ], 201);
    }

    // Update periode (admin only)
    public function update(Request $request, Period $period)
    {
        $request->validate([
            'name'       => 'sometimes|string|max:100',
            'time_start' => 'sometimes|date_format:H:i',
            'time_end'   => 'sometimes|date_format:H:i',
            'is_active'  => 'sometimes|boolean',
        ]);

        $data = $request->only('name', 'is_active');

        if ($request->filled('time_start')) {
            $data['time_start'] = $request->time_start . ':00';
        }
        if ($request->filled('time_end')) {
            $data['time_end'] = $request->time_end . ':00';
        }

        $period->update($data);

        return response()->json([
            'message' => 'Periode berhasil diperbarui.',
            'period'  => $period,
        ]);
    }

    // Hapus periode (admin only)
    public function destroy(Period $period)
    {
        $period->delete();

        return response()->json([
            'message' => 'Periode berhasil dihapus.',
        ]);
    }
}