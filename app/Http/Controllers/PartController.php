<?php

namespace App\Http\Controllers;

use App\Models\Part;
use App\Models\Machine;
use Illuminate\Http\Request;

class PartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Pass machines data ke view untuk dropdown
        $machines = Machine::all();
        return view('part.index', compact('machines'));
    }

    /**
     * Get all parts data for AJAX DataTables
     */
    public function getData()
    {
        $parts = Part::with('machine')->get();
        
        return response()->json([
            'data' => $parts
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $machines = Machine::all();
        return view('part.create', compact('machines'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'part_code' => 'required|string|max:255|unique:parts,part_code',
            'part_name' => 'required|string|max:255',
            'part_type' => 'required|string|max:255',
            'machine_id' => 'required|exists:machines,id',
            'model' => 'required|string|max:255',
            'process' => 'required|string|max:255',
            'customer' => 'required|string|max:255',
            'status' => 'required|in:active,inactive,obsolete',
        ]);

        $part = Part::create($validated);

        // Check if request is AJAX
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Part berhasil ditambahkan',
                'data' => $part->load('machine')
            ], 201);
        }

        // Traditional redirect for non-AJAX requests
        return redirect()->route('part.index')->with('success', 'Part created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $part = Part::with('machine')->findOrFail($id);
        return view('part.show', compact('part'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $part = Part::with('machine')->findOrFail($id);

        // Check if request is AJAX
        if (request()->expectsJson()) {
            return response()->json([
                'data' => $part,
                'machines' => Machine::all()
            ]);
        }

        // Traditional view for non-AJAX requests
        $machines = Machine::all();
        return view('part.edit', compact('part', 'machines'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $part = Part::findOrFail($id);

        $validated = $request->validate([
            'part_code' => 'required|string|max:255|unique:parts,part_code,' . $id,
            'part_name' => 'required|string|max:255',
            'part_type' => 'required|string|max:255',
            'machine_id' => 'required|exists:machines,id',
            'model' => 'required|string|max:255',
            'process' => 'required|string|max:255',
            'customer' => 'required|string|max:255',
            'status' => 'required|in:active,inactive,obsolete',
        ]);

        $part->update($validated);

        // Check if request is AJAX
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Part berhasil diupdate',
                'data' => $part->load('machine')
            ]);
        }

        // Traditional redirect for non-AJAX requests
        return redirect()->route('part.index')->with('success', 'Part updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $part = Part::findOrFail($id);
        $part->delete();

        // Check if request is AJAX
        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Part berhasil dihapus'
            ]);
        }

        // Traditional redirect for non-AJAX requests
        return redirect()->route('part.index')->with('success', 'Part deleted successfully.');
    }
}