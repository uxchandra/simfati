<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RepairRequest;
use App\Models\Machine;
use App\Models\Part;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB; 

class RepairRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('repair-request.index');
    }

    /**
     * Get data for DataTables
     */
    public function getData(Request $request)
    {
        $requests = RepairRequest::getRequestData();

        // Filter berdasarkan status jika ada
        if ($request->has('status') && $request->status != 'all') {
            $requests = $requests->filter(function($repairRequest) use ($request) {
                return $repairRequest['status'] == $request->status;
            });
        }

        // Filter berdasarkan type jika ada
        if ($request->has('type') && $request->type != 'all') {
            $requests = $requests->filter(function($repairRequest) use ($request) {
                return $repairRequest['item_type'] == $request->type;
            });
        }

        return response()->json([
            'data' => $requests->values() // Reset array keys
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $machines = Machine::select('id', 'machine_code', 'machine_name')->get();
        $parts = Part::select('id', 'part_code', 'part_name')->get();
        
        return view('repair-request.create', compact('machines', 'parts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:machine,part',
            'item_id' => 'required|integer',
            'problem_description' => 'required|string',
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            DB::beginTransaction();

            // Generate request code
            $requestCode = RepairRequest::generateRequestCode();

            // Create repair request
            $repairRequest = RepairRequest::create([
                'request_code' => $requestCode,
                $request->type . '_id' => $request->item_id,
                'problem_description' => $request->problem_description,
                'status' => 'pending',
                'requested_by' => Auth::id(),
                'requested_at' => now(),
            ]);

            // Handle photo uploads
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $path = $photo->store('repair-photos', 'public');
                    
                    $repairRequest->photos()->create([
                        'photo_path' => $path,
                        'photo_description' => 'Problem documentation',
                        'uploaded_at' => now(),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Request perbaikan berhasil dibuat!',
                'success' => true,
                'request_code' => $requestCode
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Gagal menyimpan data: ' . $e->getMessage(),
                'success' => false
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $repairRequest = RepairRequest::with(['machine', 'part', 'requester', 'photos'])->findOrFail($id);
        return view('repair-request.show', compact('repairRequest'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $repairRequest = RepairRequest::findOrFail($id);
        $machines = Machine::select('id', 'machine_code', 'machine_name')->get();
        $parts = Part::select('id', 'part_code', 'part_name')->get();
        
        return view('repair-request.edit', compact('repairRequest', 'machines', 'parts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'problem_description' => 'required|string',
        ]);

        try {
            $repairRequest = RepairRequest::findOrFail($id);
            
            $repairRequest->update([
                'status' => $request->status,
                'problem_description' => $request->problem_description,
            ]);

            return response()->json([
                'message' => 'Request perbaikan berhasil diupdate!',
                'success' => true
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengupdate data: ' . $e->getMessage(),
                'success' => false
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $repairRequest = RepairRequest::with('photos')->findOrFail($id);

            // Delete physical photos
            foreach ($repairRequest->photos as $photo) {
                if (Storage::disk('public')->exists($photo->photo_path)) {
                    Storage::disk('public')->delete($photo->photo_path);
                }
            }

            // Delete repair request (cascade akan handle photos)
            $repairRequest->delete();

            DB::commit();

            return response()->json([
                'message' => 'Request perbaikan berhasil dihapus!',
                'success' => true
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Gagal menghapus data: ' . $e->getMessage(),
                'success' => false
            ], 500);
        }
    }

    /**
     * Get detail data untuk modal
     */
    public function getDetail($id)
    {
        $repairRequest = RepairRequest::with([
            'machine:id,machine_code,machine_name',
            'part:id,part_code,part_name',
            'requester:id,name',
            'photos'
        ])->findOrFail($id);

        $data = [
            'id' => $repairRequest->id,
            'request_code' => $repairRequest->request_code,
            'item_type' => $repairRequest->item_type,
            'item_code' => $repairRequest->item_code,
            'item_name' => $repairRequest->item_name,
            'problem_description' => $repairRequest->problem_description,
            'status' => $repairRequest->status,
            'status_label' => $repairRequest->status_label,
            'requested_by' => $repairRequest->requester->name,
            'requested_at' => $repairRequest->requested_at->format('d/m/Y H:i'),
            'photos' => $repairRequest->photos->map(function($photo) {
                return [
                    'id' => $photo->id,
                    'photo_url' => $photo->photo_url,
                    'photo_description' => $photo->photo_description,
                    'uploaded_at' => $photo->uploaded_at->format('d/m/Y H:i')
                ];
            })
        ];

        return response()->json([
            'data' => $data
        ]);
    }

    /**
     * Get options untuk dropdown berdasarkan type
     */
    public function getOptions(Request $request)
    {
        $type = $request->get('type');
        
        if ($type === 'machine') {
            $options = Machine::select('id', 'machine_code', 'machine_name')->get();
        } elseif ($type === 'part') {
            $options = Part::select('id', 'part_code', 'part_name')->get();
        } else {
            $options = collect();
        }

        return response()->json([
            'data' => $options
        ]);
    }
}