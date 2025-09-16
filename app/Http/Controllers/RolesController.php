<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class RolesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('roles.index');
    }

    public function getDataRole()
    {
        $roles = Role::get();
        return response()->json([
            'success'   => true,
            'data'      => $roles
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('roles.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role'      => 'required',
            'description' => 'required'
        ], [
            'role.required'      => 'Form Role Wajib Di Isi !',
            'description.required' => 'Form description Wajib Di Isi !'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        }

        $role = Role::create([
            'role'      => $request->role,
            'description' => $request->description
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data Berhasil Tersimpan',
            'data'     => $role
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $role = Role::findOrFail($id);
        return response()->json([
            'success' => true,
            'message' => 'Edit Data Barang',
            'data'    => $role
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $role = Role::find($id);

        $validator = Validator::make($request->all(), [
            'role'      => 'required',
            'description' => 'required'
        ], [
            'role.required'         => 'Form ole Wajib Di Isi !',
            'description.required'    => 'Form description Wajib Di Isi !'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        }

        $role->update([
            'role'      => $request->role,
            'description' => $request->description
        ]);

        return response()->json([
            'success'   => true,
            'message'   => 'Data Berhasil Terupdate',
            'data'      => $role
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Role::find($id)->delete($id);
        return response()->json([
            'success' => true,
            'message' => 'Data Berhasil Dihapus!'
        ]);
    }
}
