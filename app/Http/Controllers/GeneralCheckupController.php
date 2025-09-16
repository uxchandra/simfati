<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GeneralCheckup;
use App\Models\Machine;
use App\Models\Part;
use Illuminate\Support\Facades\DB;    
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class GeneralCheckupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('general-checkup.index');
    }

    /**
     * Get data for DataTables
     */
    public function getData(Request $request)
    {
        $query = GeneralCheckup::with(['machine:id,machine_code,machine_name', 'part:id,part_code,part_name', 'inspector:id,name'])
            ->select('id', 'checkup_code', 'machine_id', 'part_id', 'checkup_date', 'user_id', 'shift', 'overall_status', 'notes');

        // Filter berdasarkan tanggal jika ada
        if ($request->has('start_date') && $request->has('end_date') && $request->start_date && $request->end_date) {
            $query->whereBetween('checkup_date', [$request->start_date, $request->end_date]);
        }

        // Filter berdasarkan status jika ada
        if ($request->has('status') && $request->status != 'all') {
            $query->where('overall_status', $request->status);
        }

        // Filter berdasarkan shift jika ada
        if ($request->has('shift') && $request->shift != 'all') {
            $query->where('shift', $request->shift);
        }

        $checkups = $query->orderBy('checkup_date', 'desc')->get();

        $data = $checkups->map(function($checkup) {
            return [
                'id' => $checkup->id,
                'checkup_code' => $checkup->checkup_code,
                'item_type' => $checkup->machine_id ? 'machine' : 'part',
                'item_code' => $checkup->machine_id ? $checkup->machine->machine_code : $checkup->part->part_code,
                'item_name' => $checkup->machine_id ? $checkup->machine->machine_name : $checkup->part->part_name,
                'checkup_date' => $checkup->checkup_date->format('d/m/Y H:i'),
                'inspector' => $checkup->inspector->name,
                'shift' => ucfirst($checkup->shift),
                'overall_status' => $checkup->overall_status,
                'notes' => $checkup->notes ? (strlen($checkup->notes) > 50 ? substr($checkup->notes, 0, 50) . '...' : $checkup->notes) : '-'
            ];
        });

        return response()->json([
            'data' => $data
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $machines = Machine::select('id', 'machine_code', 'machine_name')->get();
        $parts = Part::select('id', 'part_code', 'part_name')->get();
        
        return view('general-checkup.create', compact('machines', 'parts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:machine,part',
            'item_id' => 'required|integer',
            'shift' => 'required|in:morning,afternoon,night',
            'overall_status' => 'required|in:good,problem,critical',
            'notes' => 'nullable|string',
            'checkup_details' => 'required|array|min:1',
            'checkup_details.*.check_item_id' => 'required|exists:check_items,id',
            'checkup_details.*.item_status' => 'required|in:good,problem,critical,maintenance_needed',
            'checkup_details.*.maintenance_notes' => 'nullable|string',
            'checkup_details.*.standards' => 'nullable|array',
            'checkup_details.*.standards.*.check_standard_id' => 'required|exists:check_standards,id',
            'checkup_details.*.standards.*.result' => 'required|in:OK,NG',
            'checkup_details.*.standards.*.notes' => 'nullable|string',
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        try {
            DB::beginTransaction();

            // Generate checkup code
            $checkupCode = GeneralCheckup::generateCheckupCode();

            // Create general checkup
            $generalCheckup = GeneralCheckup::create([
                'checkup_code' => $checkupCode,
                $request->type . '_id' => $request->item_id,
                'checkup_date' => now(), // Otomatis menggunakan waktu sekarang
                'user_id' => Auth::user()->id ?? 1, // Otomatis menggunakan user yang login
                'shift' => $request->shift,
                'overall_status' => $request->overall_status,
                'notes' => $request->notes,
                'created_by' => Auth::user()->id ?? 1, // fallback jika belum ada auth
            ]);

            // Create checkup details
            foreach ($request->checkup_details as $detailData) {
                $checkupDetail = $generalCheckup->details()->create([
                    'check_item_id' => $detailData['check_item_id'],
                    'item_status' => 'good', // Default status
                    'maintenance_notes' => '', // Default empty notes
                ]);

                // Create checkup standards if exists
                if (!empty($detailData['standards'])) {
                    foreach ($detailData['standards'] as $standardData) {
                        $checkupDetail->standards()->create([
                            'check_standard_id' => $standardData['check_standard_id'],
                            'result' => $standardData['result'],
                            'notes' => null, // Notes dihapus sesuai request
                        ]);
                    }
                }
            }

            // Handle photo uploads
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $path = $photo->store('checkup-photos', 'public');
                    
                    $generalCheckup->photos()->create([
                        'photo_path' => $path,
                        'photo_description' => 'Dokumentasi checkup',
                        'uploaded_at' => now(),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'General Checkup berhasil dibuat!',
                'success' => true,
                'checkup_code' => $checkupCode
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
        $checkup = GeneralCheckup::with([
            'machine:id,machine_code,machine_name',
            'part:id,part_code,part_name',
            'inspector:id,name',
            'creator:id,name',
            'details.checkItem:id,item_name',
            'details.standards.checkStandard:id,standard_name',
            'photos'
        ])->findOrFail($id);

        return view('general-checkup.show', compact('checkup'));
    }

    /**
     * Get detail data untuk modal
     */
    public function getDetail($id)
    {
        $checkup = GeneralCheckup::with([
            'machine:id,machine_code,machine_name',
            'part:id,part_code,part_name',
            'inspector:id,name',
            'creator:id,name',
            'details.checkItem:id,item_name',
            'details.standards.checkStandard:id,standard_name',
            'photos'
        ])->findOrFail($id);

        $data = [
            'id' => $checkup->id,
            'checkup_code' => $checkup->checkup_code,
            'item_type' => $checkup->machine_id ? 'Machine' : 'Part',
            'item_code' => $checkup->machine_id ? $checkup->machine->machine_code : $checkup->part->part_code,
            'item_name' => $checkup->machine_id ? $checkup->machine->machine_name : $checkup->part->part_name,
            'checkup_date' => $checkup->checkup_date->format('d/m/Y H:i'),
            'inspector' => $checkup->inspector->name,
            'shift' => ucfirst($checkup->shift),
            'overall_status' => ucfirst($checkup->overall_status),
            'notes' => $checkup->notes,
            'created_by' => $checkup->creator->name,
            'created_at' => $checkup->created_at->format('d/m/Y H:i'),
            'details' => $checkup->details->map(function($detail) {
                return [
                    'id' => $detail->id,
                    'check_item_name' => $detail->checkItem->item_name,
                    'item_status' => ucfirst(str_replace('_', ' ', $detail->item_status)),
                    'maintenance_notes' => $detail->maintenance_notes,
                    'standards' => $detail->standards->map(function($standard) {
                        return [
                            'id' => $standard->id,
                            'standard_name' => $standard->checkStandard->standard_name,
                            'result' => $standard->result,
                            'notes' => $standard->notes
                        ];
                    })
                ];
            }),
            'photos' => $checkup->photos->map(function($photo) {
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
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $checkup = GeneralCheckup::with('photos')->findOrFail($id);

            // Delete physical photos
            foreach ($checkup->photos as $photo) {
                if (Storage::disk('public')->exists($photo->photo_path)) {
                    Storage::disk('public')->delete($photo->photo_path);
                }
            }

            // Delete checkup (cascade akan handle details, standards, dan photos)
            $checkup->delete();

            DB::commit();

            return response()->json([
                'message' => 'General Checkup berhasil dihapus!',
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
     * Get check items berdasarkan machine/part
     */
    public function getCheckItems(Request $request)
    {
        $type = $request->get('type');
        $itemId = $request->get('item_id');
        
        if ($type === 'machine') {
            $checkItems = \App\Models\CheckItem::with('standards')
                ->where('machine_id', $itemId)
                ->get();
        } else {
            $checkItems = \App\Models\CheckItem::with('standards')
                ->where('part_id', $itemId)
                ->get();
        }

        return response()->json([
            'data' => $checkItems
        ]);
    }
}