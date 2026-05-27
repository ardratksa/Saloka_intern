<?php

namespace App\Http\Controllers;

use App\Models\LocationName;
use App\Models\LocationType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LocationController extends Controller
{
    // Ambil semua tipe lokasi
    public function types()
    {
        $types = LocationType::orderBy('name')->get();

        return response()->json($types);
    }

    // Ambil semua lokasi, bisa filter by type
    public function index(Request $request)
    {
        $query = LocationName::with('type');

        if ($request->filled('location_type_id')) {
            $query->where('location_type_id', $request->location_type_id);
        }

        $locations = $query->orderBy('name')->get()
            ->map(fn($l) => [
                'id'        => $l->id,
                'name'      => $l->name,
                'type_id'   => $l->location_type_id,
                'type_name' => $l->type?->name,
                'qr_code'   => $l->qr_code,
                'is_active' => $l->is_active,
            ]);

        return response()->json($locations);
    }

    // Detail satu lokasi
    public function show(LocationName $locationName)
    {
        $locationName->load('type');

        return response()->json([
            'id'        => $locationName->id,
            'name'      => $locationName->name,
            'type_id'   => $locationName->location_type_id,
            'type_name' => $locationName->type?->name,
            'qr_code'   => $locationName->qr_code,
            'is_active' => $locationName->is_active,
        ]);
    }

    // Tambah lokasi (admin only)
    public function store(Request $request)
    {
        $request->validate([
            'location_type_id' => 'required|exists:location_types,id',
            'name'             => 'required|string|max:100',
            'is_active' => 'nullable|boolean',
        ]);

        $date = now()->format('ymd');

        $lastId = (LocationName::max('id') ?? 0) + 1;

        $sequence = str_pad($lastId, 4, '0', STR_PAD_LEFT);

        $random = strtolower(Str::random(5));

        $qrCode = "{$request->location_type_id}-{$date}-{$sequence}-{$random}";

        $location = LocationName::create([
            'location_type_id' => $request->location_type_id,
            'name'             => $request->name,
            'qr_code'          => $qrCode,
            'is_active'        => $request->is_active ?? true,
        ]);

        return response()->json([
            'message'  => 'Lokasi berhasil ditambahkan.',
            'location' => $location->load('type'),
        ], 201);
    }

    // Update lokasi (admin only)
    public function update(Request $request, LocationName $locationName)
    {
        $request->validate([
            'name'      => 'sometimes|string|max:100',
            'is_active' => 'sometimes|boolean',
        ]);

        $locationName->update(
            $request->only('name', 'is_active')
        );

        return response()->json([
            'message'  => 'Lokasi berhasil diperbarui.',
            'location' => $locationName->load('type'),
        ]);
    }

    // Tambah tipe lokasi (admin only)
    public function storeType(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:location_types,name',
            'is_active' => 'nullable|boolean',
        ]);

        $type = LocationType::create([
            'name'      => $request->name,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json([
            'message' => 'Tipe lokasi berhasil ditambahkan.',
            'type'    => $type,
        ], 201);
    }

    // Update tipe lokasi (admin only)
    public function updateType(Request $request, LocationType $locationType)
    {
        $request->validate([
            'name'      => 'sometimes|string|max:100',
            'is_active' => 'sometimes|boolean',
        ]);

        $locationType->update(
            $request->only('name', 'is_active')
        );

        return response()->json([
            'message' => 'Tipe lokasi berhasil diperbarui.',
            'type'    => $locationType,
        ]);
    }
    
        // Hapus lokasi (admin only)
    public function destroy(LocationName $locationName)
    {
        $locationName->delete();

        return response()->json([
            'message' => 'Lokasi berhasil dihapus.',
        ]);
    }

    // Hapus tipe lokasi (admin only)
    public function destroyType(LocationType $locationType)
    {
        $locationType->delete();
        
        return response()->json([
            'message' => 'Tipe lokasi berhasil dihapus.',
        ]);
    }
}