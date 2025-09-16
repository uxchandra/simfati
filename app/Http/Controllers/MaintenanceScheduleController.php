<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MaintenanceSchedule;
use App\Models\Machine;
use App\Models\Part;

class MaintenanceScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('maintenance-schedule.index');
    }

    /**
     * Get data for DataTables
     */
    public function getData(Request $request)
    {
        $schedules = MaintenanceSchedule::getScheduleData();

        // Filter berdasarkan type jika ada
        if ($request->has('type') && $request->type != 'all') {
            $schedules = $schedules->filter(function($schedule) use ($request) {
                return $schedule['item_type'] == $request->type;
            });
        }

        // Sort berdasarkan days_remaining (overdue dulu, lalu yang paling dekat due date)
        $schedules = $schedules->sortBy(function($schedule) {
            return $schedule['days_remaining'];
        });

        return response()->json([
            'data' => $schedules->values() // Reset array keys
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $machines = Machine::select('id', 'machine_code', 'machine_name')->get();
        $parts = Part::select('id', 'part_code', 'part_name')->get();
        
        return view('maintenance-schedule.create', compact('machines', 'parts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:machine,part',
            'item_id' => 'required|integer',
            'period_days' => 'required|integer|min:1',
            'start_date' => 'required|date',
        ]);

        try {
            // Generate schedule name otomatis berdasarkan item dan periode
            $item = null;
            if ($request->type === 'machine') {
                $item = Machine::find($request->item_id);
                $itemName = $item->machine_name;
            } else {
                $item = Part::find($request->item_id);
                $itemName = $item->part_name;
            }
            
            $scheduleName = "Schedule {$itemName} ({$request->period_days} hari)";

            MaintenanceSchedule::create([
                $request->type . '_id' => $request->item_id,
                'schedule_name' => $scheduleName,
                'period_days' => $request->period_days,
                'start_date' => $request->start_date,
            ]);

            return response()->json([
                'message' => 'Schedule berhasil dibuat!',
                'success' => true
            ]);

        } catch (\Exception $e) {
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
        $schedule = MaintenanceSchedule::with(['machine', 'part'])->findOrFail($id);
        return view('maintenance-schedule.show', compact('schedule'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $schedule = MaintenanceSchedule::findOrFail($id);
        $machines = Machine::select('id', 'machine_code', 'machine_name')->get();
        $parts = Part::select('id', 'part_code', 'part_name')->get();
        
        return view('maintenance-schedule.edit', compact('schedule', 'machines', 'parts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|in:machine,part',
            'item_id' => 'required|integer',
            'period_days' => 'required|integer|min:1',
            'start_date' => 'required|date',
        ]);

        try {
            $schedule = MaintenanceSchedule::findOrFail($id);
            
            // Generate schedule name otomatis berdasarkan item dan periode
            $item = null;
            if ($request->type === 'machine') {
                $item = Machine::find($request->item_id);
                $itemName = $item->machine_name;
            } else {
                $item = Part::find($request->item_id);
                $itemName = $item->part_name;
            }
            
            $scheduleName = "Schedule {$itemName} ({$request->period_days} hari)";
            
            $schedule->update([
                'machine_id' => $request->type == 'machine' ? $request->item_id : null,
                'part_id' => $request->type == 'part' ? $request->item_id : null,
                'schedule_name' => $scheduleName,
                'period_days' => $request->period_days,
                'start_date' => $request->start_date,
            ]);

            return response()->json([
                'message' => 'Schedule berhasil diupdate!',
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
            $schedule = MaintenanceSchedule::findOrFail($id);
            $schedule->delete();

            return response()->json([
                'message' => 'Schedule berhasil dihapus!',
                'success' => true
            ]);

        } catch (\Exception $e) {
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
}