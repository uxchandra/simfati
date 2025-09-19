<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MaintenanceSchedule;
use App\Models\Machine;
use App\Models\Part;
use App\Models\User;

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

        // Filter berdasarkan PIC jika ada
        if ($request->has('pic_id') && $request->pic_id != 'all') {
            $schedules = $schedules->filter(function($schedule) use ($request) {
                return $schedule['user_id'] == $request->pic_id;
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
        $users = User::select('id', 'name')->orderBy('name')->get();
        
        return view('maintenance-schedule.create', compact('machines', 'parts', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:machine,part',
            'item_id' => 'required|integer',
            'user_id' => 'required|exists:users,id',
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

            $schedule = MaintenanceSchedule::create([
                $request->type . '_id' => $request->item_id,
                'schedule_name' => $scheduleName,
                'period_days' => $request->period_days,
                'start_date' => $request->start_date,
                'user_id' => $request->user_id,
            ]);

            // Check if request is AJAX
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Schedule berhasil dibuat!',
                    'success' => true,
                    'data' => $schedule
                ], 201);
            }

            return redirect()->route('maintenance-schedule.index')->with('success', 'Schedule berhasil dibuat!');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Gagal menyimpan data: ' . $e->getMessage(),
                    'success' => false
                ], 500);
            }

            return redirect()->back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $schedule = MaintenanceSchedule::with(['machine', 'part', 'user'])->findOrFail($id);
        return view('maintenance-schedule.show', compact('schedule'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $schedule = MaintenanceSchedule::with(['machine', 'part', 'user'])->findOrFail($id);
        
        // Check if request is AJAX
        if (request()->expectsJson()) {
            $type = $schedule->machine_id ? 'machine' : 'part';
            $item_id = $schedule->machine_id ?? $schedule->part_id;
            
            return response()->json([
                'type' => $type,
                'item_id' => $item_id,
                'period_days' => $schedule->period_days,
                'start_date' => $schedule->start_date,
                'user_id' => $schedule->user_id
            ]);
        }

        $machines = Machine::select('id', 'machine_code', 'machine_name')->get();
        $parts = Part::select('id', 'part_code', 'part_name')->get();
        $users = User::select('id', 'name')->orderBy('name')->get();
        
        return view('maintenance-schedule.edit', compact('schedule', 'machines', 'parts', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|in:machine,part',
            'item_id' => 'required|integer',
            'user_id' => 'required|exists:users,id',
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
                'user_id' => $request->user_id,
            ]);

            // Check if request is AJAX
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Schedule berhasil diupdate!',
                    'success' => true,
                    'data' => $schedule
                ]);
            }

            return redirect()->route('maintenance-schedule.index')->with('success', 'Schedule berhasil diupdate!');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Gagal mengupdate data: ' . $e->getMessage(),
                    'success' => false
                ], 500);
            }

            return redirect()->back()->with('error', 'Gagal mengupdate data: ' . $e->getMessage())->withInput();
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

            // Check if request is AJAX
            if (request()->expectsJson()) {
                return response()->json([
                    'message' => 'Schedule berhasil dihapus!',
                    'success' => true
                ]);
            }

            return redirect()->route('maintenance-schedule.index')->with('success', 'Schedule berhasil dihapus!');

        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'message' => 'Gagal menghapus data: ' . $e->getMessage(),
                    'success' => false
                ], 500);
            }

            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
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
     * Get users untuk dropdown PIC
     */
    public function getUsers()
    {
        $users = User::select('id', 'name')->orderBy('name')->get();
        
        return response()->json([
            'data' => $users
        ]);
    }
}