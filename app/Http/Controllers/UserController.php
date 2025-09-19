<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Auth;
use App\Models\Department;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Pass roles dan departments data ke view untuk dropdown
        $roles = Role::all();
        $departments = Department::all();
        return view('user.index', compact('roles', 'departments'));
    }

    /**
     * Get all users data for AJAX DataTables
     */
    public function getData()
    {
        $users = User::with(['role', 'department'])->get(); // Include department relationship
        
        return response()->json([
            'data' => $users
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::all();
        $departments = Department::all();
        return view('user.create', compact('roles', 'departments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role_id' => 'required|exists:roles,id',
            'department_id' => 'required|exists:departments,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'role_id' => $validated['role_id'],
            'department_id' => $validated['department_id'],
        ]);

        // Check if request is AJAX
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'User berhasil ditambahkan',
                'data' => $user->load(['role', 'department'])
            ], 201);
        }

        return redirect()->route('user.index')->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = User::with(['role', 'department'])->findOrFail($id);
        return view('user.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $user = User::with(['role', 'department'])->findOrFail($id);

        // Check if request is AJAX
        if (request()->expectsJson()) {
            return response()->json([
                'data' => $user,
                'roles' => Role::all(),
                'departments' => Department::all()
            ]);
        }

        $roles = Role::all();
        $departments = Department::all();
        return view('user.edit', compact('user', 'roles', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validationRules = [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $id,
            'role_id' => 'required|exists:roles,id',
            'department_id' => 'required|exists:departments,id',
        ];

        // Only validate password if it's provided
        if ($request->filled('password')) {
            $validationRules['password'] = ['required', 'confirmed', Rules\Password::defaults()];
        }

        $validated = $request->validate($validationRules);

        $updateData = [
            'name' => $validated['name'],
            'username' => $validated['username'],
            'role_id' => $validated['role_id'],
            'department_id' => $validated['department_id'],
        ];

        // Only update password if it's provided
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        // Check if request is AJAX
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'User berhasil diupdate',
                'data' => $user->load(['role', 'department'])
            ]);
        }

        return redirect()->route('user.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Prevent self-deletion
        if ((Auth::user())->id == $user->id) {
            if (request()->expectsJson()) {
                return response()->json([
                    'message' => 'Anda tidak dapat menghapus akun sendiri'
                ], 422);
            }
            return redirect()->route('user.index')->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        // Check if request is AJAX
        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'User berhasil dihapus'
            ]);
        }

        return redirect()->route('user.index')->with('success', 'User deleted successfully.');
    }
}