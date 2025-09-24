<?php

namespace App\Http\Controllers;

use App\Models\MachineCategory;
use Illuminate\Http\Request;

class MachineCategoryController extends Controller
{
    public function index()
    {
        // Untuk AJAX version, kita tidak perlu pass data machines ke view
        return view('machine_category.index');
    }

    public function getData()
    {
        $machines_category = MachineCategory::all();
        
        return response()->json([
            'data' => $machines_category
        ]);
    }

    public function create()
    {
        return view('machine_category.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $machine_category = MachineCategory::create($validated);

        // Check if request is AJAX
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Category berhasil ditambahkan',
                'data' => $machine_category
            ], 201);
        }

        // Traditional redirect for non-AJAX requests
        return redirect()->route('machine_category.index')->with('success', 'Category created successfully.');
    }

    public function edit($id)
    {
        $machine_category = MachineCategory::findOrFail($id);

        // Check if request is AJAX
        if (request()->expectsJson()) {
            return response()->json([
                'data' => $machine_category
            ]);
        }

        // Traditional view for non-AJAX requests
        return view('machine_category.edit', compact('machine_category'));
    }

    public function update(Request $request, $id)
    {
        $machine_category = MachineCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $machine_category->update($validated);

        // Check if request is AJAX
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Category berhasil diupdate',
                'data' => $machine_category
            ]);
        }

        // Traditional redirect for non-AJAX requests
        return redirect()->route('machine_category.index')->with('success', 'Category updated successfully.');
    }

    public function destroy($id)
    {
        $machine_category = MachineCategory::findOrFail($id);
        $machine_category->delete();

        // Check if request is AJAX
        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Category berhasil dihapus'
            ]);
        }

        // Traditional redirect for non-AJAX requests
        return redirect()->route('machine_category.index')->with('success', 'Category deleted successfully.');
    }
}
