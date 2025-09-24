<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GeneralCheckup;
use App\Models\Machine;
use App\Models\Part;
use App\Models\MaintenanceSchedule;
use Illuminate\Support\Facades\DB;    
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class GeneralCheckupController extends Controller
{
    public function __construct()
    {
        Carbon::setLocale('id');
        date_default_timezone_set('Asia/Jakarta');
    }

    private function getCurrentShift()
    {
        $hour = Carbon::now()->hour;
        
        if ($hour >= 6 && $hour < 14) {
            return 'morning';
        } elseif ($hour >= 14 && $hour < 22) {
            return 'afternoon';
        } else {
            return 'night';
        }
    }

    public function index()
    {
        return view('general-checkup.index');
    }

    public function getData(Request $request)
    {
        $today = Carbon::today();
        
        $scheduledItems = MaintenanceSchedule::get();
        
        $scheduledItems = $scheduledItems->filter(function($schedule) use ($today) {
            $startDate = Carbon::parse($schedule->start_date)->startOfDay();
            $daysSinceStart = $today->diffInDays($startDate);
            
            return $daysSinceStart === 0 || ($daysSinceStart > 0 && $daysSinceStart % $schedule->period_days === 0);
        });

        if ($scheduledItems->isEmpty()) {
            return response()->json(['data' => []]);
        }

        $existingCheckups = GeneralCheckup::whereDate('checkup_date', $today)
            ->whereNull('completed_at')
            ->get();

        $data = collect();

        foreach ($scheduledItems as $schedule) {
            if ($schedule->machine_id) {
                if ($existingCheckups->where('machine_id', $schedule->machine_id)->isNotEmpty()) {
                    continue;
                }

                $machine = Machine::find($schedule->machine_id);
                if ($machine) {
                    $data->push([
                        'id' => null,
                        'checkup_code' => '(Pending)',
                        'item_type' => 'machine',
                        'item_id' => $machine->id,
                        'item_code' => $machine->machine_code,
                        'item_name' => $machine->machine_name,
                        'schedule_id' => $schedule->id,
                        'checkup_date' => $today->format('d/m/Y'),
                        'inspector' => Auth::user()->name ?? 'Unassigned',
                        'shift' => $this->getCurrentShift(),
                        'overall_status' => 'pending',
                        'notes' => 'Scheduled maintenance'
                    ]);
                }
            }

            if ($schedule->part_id) {
                if ($existingCheckups->where('part_id', $schedule->part_id)->isNotEmpty()) {
                    continue;
                }

                $part = Part::find($schedule->part_id);
                if ($part) {
                    $data->push([
                        'id' => null,
                        'checkup_code' => '(Pending)',
                        'item_type' => 'part',
                        'item_id' => $part->id,
                        'item_code' => $part->part_code,
                        'item_name' => $part->part_name,
                        'schedule_id' => $schedule->id,
                        'checkup_date' => $today->format('d/m/Y'),
                        'inspector' => Auth::user()->name ?? 'Unassigned',
                        'shift' => $this->getCurrentShift(),
                        'overall_status' => 'pending',
                        'notes' => 'Scheduled maintenance'
                    ]);
                }
            }
        }

        return response()->json([
            'data' => $data
        ]);
    }

    public function create()
    {
        $machines = Machine::select('id', 'machine_code', 'machine_name')->get();
        $parts = Part::select('id', 'part_code', 'part_name')->get();
        
        return view('general-checkup.create', compact('machines', 'parts'));
    }

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

            $checkupCode = GeneralCheckup::generateCheckupCode();

            $generalCheckup = GeneralCheckup::create([
                'checkup_code' => $checkupCode,
                $request->type . '_id' => $request->item_id,
                'checkup_date' => now(),
                'user_id' => Auth::user()->id ?? 1,
                'shift' => $request->shift,
                'overall_status' => $request->overall_status,
                'notes' => $request->notes,
                'created_by' => Auth::user()->id ?? 1,
                'completed_at' => now(),
            ]);

            foreach ($request->checkup_details as $detailData) {
                $checkupDetail = $generalCheckup->details()->create([
                    'check_item_id' => $detailData['check_item_id'],
                    'item_status' => $detailData['item_status'],
                    'maintenance_notes' => $detailData['maintenance_notes'] ?? '',
                ]);

                if (!empty($detailData['standards'])) {
                    foreach ($detailData['standards'] as $standardData) {
                        $checkupDetail->standards()->create([
                            'check_standard_id' => $standardData['check_standard_id'],
                            'result' => $standardData['result'],
                            'notes' => $standardData['notes'] ?? null,
                        ]);
                    }
                }
            }

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

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $checkup = GeneralCheckup::with('photos')->findOrFail($id);

            foreach ($checkup->photos as $photo) {
                if (Storage::disk('public')->exists($photo->photo_path)) {
                    Storage::disk('public')->delete($photo->photo_path);
                }
            }

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