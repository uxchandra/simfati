<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Machine;
use Illuminate\Http\Request;

class MachineUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            // Hanya tampilkan user yang punya mesin
            $users = User::with(['machines', 'department'])
                        ->whereHas('machines')
                        ->get();
            return response()->json(['data' => $users]);
        }
        
        $users = User::with('department')->get();
        $machines = Machine::all();
        return view('machine-users.index', compact('users', 'machines'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::all();
        $machines = Machine::all();
        return view('machine-users.create', compact('users', 'machines'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'machine_ids' => 'required|array',
            'machine_ids.*' => 'exists:machines,id',
        ]);

        $user = User::find($request->user_id);
        
        // Attach machines to user (akan otomatis skip yang sudah ada)
        $user->machines()->syncWithoutDetaching($request->machine_ids);

        return redirect()->route('machine-users.index')
                        ->with('success', 'PIC User berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = User::with('machines')->findOrFail($id);
        return view('machine-users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $user = User::with(['machines', 'department'])->findOrFail($id);
        
        // For AJAX request, return JSON
        if (request()->ajax()) {
            return response()->json(['data' => $user]);
        }
        
        $allMachines = Machine::all();
        return view('machine-users.edit', compact('user', 'allMachines'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'machine_ids' => 'nullable|array',
            'machine_ids.*' => 'exists:machines,id',
        ]);

        $user = User::findOrFail($id);
        
        // Sync machines (akan replace semua relasi yang ada)
        $user->machines()->sync($request->machine_ids ?? []);

        // PERBAIKAN: Cek apakah AJAX request
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'PIC Machine berhasil diupdate'
            ]);
        }

        return redirect()->route('machine-users.index')
                        ->with('success', 'PIC Machine berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Detach semua machines dari user ini
        $user->machines()->detach();

        // PERBAIKAN: Cek apakah AJAX request
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Semua mesin berhasil dihapus dari user'
            ]);
        }

        return redirect()->route('machine-users.index')
                        ->with('success', 'Semua mesin berhasil dihapus dari user');
    }

    /**
     * Remove specific machine from user
     */
    public function detachMachine(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'machine_id' => 'required|exists:machines,id',
        ]);

        $user = User::find($request->user_id);
        $user->machines()->detach($request->machine_id);

        return redirect()->back()
                        ->with('success', 'Mesin berhasil dihapus dari user');
    }

    /**
     * Get machines by user for AJAX
     */
    public function getMachinesByUser($userId)
    {
        $user = User::with('machines')->find($userId);
        
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        
        return response()->json($user->machines);
    }

    /**
     * Get users by machine for AJAX
     */
    public function getUsersByMachine($machineId)
    {
        $machine = Machine::with('users')->find($machineId);
        
        if (!$machine) {
            return response()->json(['error' => 'Machine not found'], 404);
        }
        
        return response()->json($machine->users);
    }

    /**
     * Assign multiple machines to user via AJAX
     */
    public function assignMachines(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'machine_ids' => 'required|array',
            'machine_ids.*' => 'exists:machines,id',
        ]);

        $user = User::find($request->user_id);
        $user->machines()->syncWithoutDetaching($request->machine_ids);

        return response()->json([
            'success' => true,
            'message' => 'Mesin berhasil ditambahkan ke user',
            'machines' => $user->fresh()->machines
        ]);
    }

    /**
     * Get available machines for a user (machines not yet assigned)
     */
    public function getAvailableMachines($userId)
    {
        $user = User::with('machines')->find($userId);
        
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $assignedMachineIds = $user->machines->pluck('id')->toArray();
        $availableMachines = Machine::whereNotIn('id', $assignedMachineIds)->get();

        return response()->json($availableMachines);
    }
}