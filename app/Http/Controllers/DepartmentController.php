<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DepartmentController extends Controller
{
    public function index()
    {
        return view('department.index', [
            'departments' => Department::all()
        ]);
    }

    public function getDataDepartment()
    {
        return response()->json([
            'success' => true,
            'data'    => Department::all()
        ]);
    }

    public function getDepartments()
    {
        $departments = Department::orderBy('nama', 'asc')->get();
        return response()->json($departments);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('department.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required',
            'kode' => 'required|unique:departments,kode',
            'gm' => 'nullable|string|max:255',
            'role' => 'nullable|string|max:255' // Validasi untuk role: boleh null, bertipe string, maks 255 karakter
        ], [
            'nama.required' => 'Form Nama Departemen Wajib Diisi!',
            'kode.required' => 'Form Kode Departemen Wajib Diisi!',
            'kode.unique' => 'Kode Departemen Sudah Digunakan!',
            'gm.string' => 'Form GM Harus Berupa Teks!',
            'gm.max' => 'Form GM Tidak Boleh Melebihi 255 Karakter!',
            'role.string' => 'Form Role Harus Berupa Teks!',
            'role.max' => 'Form Role Tidak Boleh Melebihi 255 Karakter!'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $department = Department::create([
            'nama' => $request->nama,
            'kode' => $request->kode,
            'gm' => $request->gm,
            'role' => $request->role, // Menambahkan role ke data yang disimpan
            'user_id' => Auth::user()->id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data Berhasil Disimpan!',
            'data' => $department
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $department = Department::findOrFail($id);
        return response()->json([
            'success' => true,
            'message' => 'Edit Data Department',
            'data' => $department
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $department = Department::find($id);

        $validator = Validator::make($request->all(), [
            'nama' => 'required',
            'kode' => 'required|unique:departments,kode,' . $id,
            'gm' => 'nullable|string|max:255',
            'role' => 'nullable|string|max:255' // Validasi untuk role: boleh null, bertipe string, maks 255 karakter
        ], [
            'nama.required' => 'Form Nama Departemen Tidak Boleh Kosong!',
            'kode.required' => 'Form Kode Departemen Tidak Boleh Kosong!',
            'kode.unique' => 'Kode Departemen Sudah Digunakan!',
            'gm.string' => 'Form GM Harus Berupa Teks!',
            'gm.max' => 'Form GM Tidak Boleh Melebihi 255 Karakter!',
            'role.string' => 'Form Role Harus Berupa Teks!',
            'role.max' => 'Form Role Tidak Boleh Melebihi 255 Karakter!'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $department->update([
            'nama' => $request->nama,
            'kode' => $request->kode,
            'gm' => $request->gm,
            'role' => $request->role, // Menambahkan role ke data yang diperbarui
            'user_id' => Auth::user()->id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data Berhasil Terupdate',
            'data' => $department
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Department::find($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data Berhasil Dihapus!'
        ]);
    }
}