<?php

namespace App\Http\Controllers;

use App\Models\MasterIssue;
use Illuminate\Http\Request;

class MasterIssueController extends Controller
{
    public function index()
    {
        return response()->json(
            MasterIssue::orderBy('name')->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        return MasterIssue::create([
            'name' => $request->name,
            'is_active' => $request->is_active ?? true,
        ]);
    }

    public function update(
        Request $request,
        MasterIssue $masterIssue
    ) {
        $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $masterIssue->update([
            'name' => $request->name,
            'is_active' => $request->is_active,
        ]);

        return $masterIssue;
    }

    public function destroy(
        MasterIssue $masterIssue
    ) {
        $masterIssue->delete();

        return response()->json([
            'message' => 'Issue berhasil dihapus',
        ]);
    }
}