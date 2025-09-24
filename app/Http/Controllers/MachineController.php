<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Machine;
use App\Models\MachineCategory;

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
     * Show machines by category (view)
     */
    public function byCategory($id)
    {
        $category = MachineCategory::findOrFail($id);
        return view('machine.index', compact('category'));
    }

    /**
     * Full page create form within a category
     */
    public function createInCategory($id)
    {
        $category = MachineCategory::findOrFail($id);
        return view('machine.create', compact('category'));
    }

    /**
     * Get machines by category (AJAX JSON)
     */
    public function getByCategory($id)
    {
        $machines = Machine::where('category_id', $id)->get();
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
            'category_id' => 'required|exists:machine_categories,id',
            'kode' => 'required|string|max:50|unique:machines,kode',
            'description' => 'nullable|string',
            'kapasitas' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'tahun_pembuatan' => 'nullable|string|max:10',
            'nomor_seri' => 'nullable|string|max:100',
            'power' => 'nullable|string|max:100',
            'tgl_instal' => 'nullable|string|max:100',
            'keterangan' => 'nullable|string',
            'capacity_kn' => 'nullable|string|max:100',
            'slide_stroke' => 'nullable|string|max:100',
            'stroke_per_minute' => 'nullable|string|max:100',
            'die_height' => 'nullable|string|max:100',
            'slide_adjustment' => 'nullable|string|max:100',
            'slide_area' => 'nullable|string|max:100',
            'bolster_area' => 'nullable|string|max:100',
            'main_motor' => 'nullable|string|max:100',
            'req_air_pressure' => 'nullable|string|max:100',
            'max_upper_die_weight' => 'nullable|string|max:100',
            'power_source' => 'nullable|string|max:100',
            'braking_time' => 'nullable|string|max:100',
            'lane' => 'nullable|string|max:100',
            'status' => 'required|in:active,inactive',
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
            'category_id' => 'required|exists:machine_categories,id',
            'kode' => 'required|string|max:50|unique:machines,kode,' . $id,
            'description' => 'nullable|string',
            'kapasitas' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'tahun_pembuatan' => 'nullable|string|max:10',
            'nomor_seri' => 'nullable|string|max:100',
            'power' => 'nullable|string|max:100',
            'tgl_instal' => 'nullable|string|max:100',
            'keterangan' => 'nullable|string',
            'capacity_kn' => 'nullable|string|max:100',
            'slide_stroke' => 'nullable|string|max:100',
            'stroke_per_minute' => 'nullable|string|max:100',
            'die_height' => 'nullable|string|max:100',
            'slide_adjustment' => 'nullable|string|max:100',
            'slide_area' => 'nullable|string|max:100',
            'bolster_area' => 'nullable|string|max:100',
            'main_motor' => 'nullable|string|max:100',
            'req_air_pressure' => 'nullable|string|max:100',
            'max_upper_die_weight' => 'nullable|string|max:100',
            'power_source' => 'nullable|string|max:100',
            'braking_time' => 'nullable|string|max:100',
            'lane' => 'nullable|string|max:100',
            'status' => 'required|in:active,inactive',
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