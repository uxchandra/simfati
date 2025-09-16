<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Machine;
use App\Models\Part;
use App\Models\CheckItem;
use Illuminate\Support\Facades\DB;

class CheckItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('checkitem.index');
    }

    /**
     * Get data for DataTables based on type filter
     */
    public function getData(Request $request)
    {
        $type = $request->get('type', 'all'); // default machine
        
        if ($type === 'machine') {
            $items = Machine::whereHas('checkItems')
                ->select('id', 'machine_code', 'machine_name')
                ->get()
                ->map(function($item) {
                    return [
                        'id' => $item->id,
                        'code' => $item->machine_code,
                        'name' => $item->machine_name,
                        'type' => 'machine'
                    ];
                });
        } elseif ($type === 'part') {
            $items = Part::whereHas('checkItems')
                ->select('id', 'part_code', 'part_name') // sesuaikan dengan kolom di tabel Part
                ->get()
                ->map(function($item) {
                    return [
                        'id' => $item->id,
                        'code' => $item->part_code,
                        'name' => $item->part_name,
                        'type' => 'part'
                    ];
                });
        } else {
            // Jika mau gabungin machine dan part
            $machines = Machine::whereHas('checkItems')
                ->select('id', 'machine_code', 'machine_name')
                ->get()
                ->map(function($item) {
                    return [
                        'id' => $item->id,
                        'code' => $item->machine_code,
                        'name' => $item->machine_name,
                        'type' => 'machine'
                    ];
                });

            $parts = Part::whereHas('checkItems')
                ->select('id', 'part_code', 'part_name')
                ->get()
                ->map(function($item) {
                    return [
                        'id' => $item->id,
                        'code' => $item->part_code,
                        'name' => $item->part_name,
                        'type' => 'part'
                    ];
                });

            $items = $machines->concat($parts);
        }

        return response()->json([
            'data' => $items,
            'type' => $type
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $machines = Machine::select('id', 'machine_code', 'machine_name')->get();
        $parts = Part::select('id', 'part_code', 'part_name')->get();
        
        return view('checkitem.create', compact('machines', 'parts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:machine,part',
            'item_id' => 'required|integer',
            'check_items' => 'required|array|min:1',
            'check_items.*.item_name' => 'required|string|max:255',
            'check_items.*.standards' => 'nullable|array',
            'check_items.*.standards.*' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->check_items as $checkItemData) {
                // Create check item
                $checkItem = CheckItem::create([
                    $request->type . '_id' => $request->item_id,
                    'item_name' => $checkItemData['item_name'],
                ]);

                // Create standards if exists
                if (!empty($checkItemData['standards'])) {
                    foreach ($checkItemData['standards'] as $standardName) {
                        if (!empty(trim($standardName))) {
                            $checkItem->standards()->create([
                                'standard_name' => trim($standardName)
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Check Items berhasil ditambahkan!',
                'success' => true
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
     * Show the form for editing the specified resource.
     */
    public function edit($type, $id)
    {
        if ($type === 'machine') {
            $item = Machine::with(['checkItems.standards'])
                ->select('id', 'machine_code', 'machine_name')
                ->findOrFail($id);
            
            $data = [
                'id' => $item->id,
                'code' => $item->machine_code,
                'name' => $item->machine_name,
                'type' => 'machine',
                'check_items' => $item->checkItems->map(function($checkItem) {
                    return [
                        'id' => $checkItem->id,
                        'item_name' => $checkItem->item_name,
                        'standards' => $checkItem->standards->map(function($standard) {
                            return [
                                'id' => $standard->id,
                                'standard_name' => $standard->standard_name
                            ];
                        })
                    ];
                })
            ];
        } elseif ($type === 'part') {
            $item = Part::with(['checkItems.standards'])
                ->select('id', 'part_code', 'part_name')
                ->findOrFail($id);
            
            $data = [
                'id' => $item->id,
                'code' => $item->part_code,
                'name' => $item->part_name,
                'type' => 'part',
                'check_items' => $item->checkItems->map(function($checkItem) {
                    return [
                        'id' => $checkItem->id,
                        'item_name' => $checkItem->item_name,
                        'standards' => $checkItem->standards->map(function($standard) {
                            return [
                                'id' => $standard->id,
                                'standard_name' => $standard->standard_name
                            ];
                        })
                    ];
                })
            ];
        }

        $machines = Machine::select('id', 'machine_code', 'machine_name')->get();
        $parts = Part::select('id', 'part_code', 'part_name')->get();
        
        return view('checkitem.edit', compact('data', 'machines', 'parts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $type, $id)
    {
        $request->validate([
            'type' => 'required|in:machine,part',
            'item_id' => 'required|integer',
            'check_items' => 'required|array|min:1',
            'check_items.*.item_name' => 'required|string|max:255',
            'check_items.*.standards' => 'nullable|array',
            'check_items.*.standards.*' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Hapus semua check items lama untuk item ini
            if ($type === 'machine') {
                CheckItem::where('machine_id', $id)->delete();
            } else {
                CheckItem::where('part_id', $id)->delete();
            }

            // Buat check items baru
            foreach ($request->check_items as $checkItemData) {
                $checkItem = CheckItem::create([
                    $request->type . '_id' => $request->item_id,
                    'item_name' => $checkItemData['item_name'],
                ]);

                // Create standards if exists
                if (!empty($checkItemData['standards'])) {
                    foreach ($checkItemData['standards'] as $standardName) {
                        if (!empty(trim($standardName))) {
                            $checkItem->standards()->create([
                                'standard_name' => trim($standardName)
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Check Items berhasil diupdate!',
                'success' => true
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Gagal mengupdate data: ' . $e->getMessage(),
                'success' => false
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($type, $id)
    {
        try {
            DB::beginTransaction();

            // Hapus semua check items untuk machine/part ini
            if ($type === 'machine') {
                $checkItems = CheckItem::where('machine_id', $id)->get();
                $itemName = Machine::find($id)->machine_name ?? 'Item';
            } else {
                $checkItems = CheckItem::where('part_id', $id)->get();
                $itemName = Part::find($id)->part_name ?? 'Item';
            }

            foreach ($checkItems as $checkItem) {
                // Hapus standards dulu
                $checkItem->standards()->delete();
                // Hapus check item
                $checkItem->delete();
            }

            DB::commit();

            return response()->json([
                'message' => "Check Items untuk {$itemName} berhasil dihapus!",
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

    /**
     * Get detail data untuk modal
     */
    public function getDetail(Request $request, $type, $id)
    {
        if ($type === 'machine') {
            $item = Machine::with(['checkItems.standards'])
                ->select('id', 'machine_code', 'machine_name')
                ->findOrFail($id);
            
            $data = [
                'id' => $item->id,
                'code' => $item->machine_code,
                'name' => $item->machine_name,
                'type' => 'Machine',
                'check_items' => $item->checkItems->map(function($checkItem) {
                    return [
                        'id' => $checkItem->id,
                        'item_name' => $checkItem->item_name,
                        'standards' => $checkItem->standards->pluck('standard_name')
                    ];
                })
            ];
        } elseif ($type === 'part') {
            $item = Part::with(['checkItems.standards'])
                ->select('id', 'part_code', 'part_name')
                ->findOrFail($id);
            
            $data = [
                'id' => $item->id,
                'code' => $item->part_code,
                'name' => $item->part_name,
                'type' => 'Part',
                'check_items' => $item->checkItems->map(function($checkItem) {
                    return [
                        'id' => $checkItem->id,
                        'item_name' => $checkItem->item_name,
                        'standards' => $checkItem->standards->pluck('standard_name')
                    ];
                })
            ];
        }

        return response()->json([
            'data' => $data
        ]);
    }
}