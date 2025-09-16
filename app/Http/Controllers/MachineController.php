<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Machine;

class MachineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Untuk AJAX version, kita tidak perlu pass data machines ke view
        return view('machine.index');
    }

    /**
     * Get all machines data for AJAX DataTables
     */
    public function getData()
    {
        $machines = Machine::all();
        
        return response()->json([
            'data' => $machines
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('machine.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'machine_code' => 'required|string|max:255|unique:machines,machine_code',
            'machine_name' => 'required|string|max:255',
            'section' => 'required|string|max:255',
            'status' => 'required|in:active,inactive,maintenance',
        ]);

        $machine = Machine::create($validated);

        // Check if request is AJAX
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Machine berhasil ditambahkan',
                'data' => $machine
            ], 201);
        }

        // Traditional redirect for non-AJAX requests
        return redirect()->route('machine.index')->with('success', 'Machine created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $machine = Machine::findOrFail($id);
        return view('machine.show', compact('machine'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $machine = Machine::findOrFail($id);

        // Check if request is AJAX
        if (request()->expectsJson()) {
            return response()->json([
                'data' => $machine
            ]);
        }

        // Traditional view for non-AJAX requests
        return view('machine.edit', compact('machine'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $machine = Machine::findOrFail($id);

        $validated = $request->validate([
            'machine_code' => 'required|string|max:255|unique:machines,machine_code,' . $id,
            'machine_name' => 'required|string|max:255',
            'section' => 'required|string|max:255',
            'status' => 'required|in:active,inactive,maintenance',
        ]);

        $machine->update($validated);

        // Check if request is AJAX
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Machine berhasil diupdate',
                'data' => $machine
            ]);
        }

        // Traditional redirect for non-AJAX requests
        return redirect()->route('machine.index')->with('success', 'Machine updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $machine = Machine::findOrFail($id);
        $machine->delete();

        // Check if request is AJAX
        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Machine berhasil dihapus'
            ]);
        }

        // Traditional redirect for non-AJAX requests
        return redirect()->route('machine.index')->with('success', 'Machine deleted successfully.');
    }
}