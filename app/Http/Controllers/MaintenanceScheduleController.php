<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MaintenanceSchedule;
use App\Models\Machine;
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

        // Filter berdasarkan PIC jika ada
        if ($request->has('pic_id') && $request->pic_id != 'all') {
            $schedules = $schedules->filter(function($schedule) use ($request) {
                return $schedule['user_id'] == $request->pic_id;
            });
        }

        // Sort berdasarkan days_remaining (overdue dulu, lalu yang paling dekat due date)
        // Jika days_remaining null, taruh di akhir
        $schedules = $schedules->sort(function($a, $b) {
            if ($a['days_remaining'] === null && $b['days_remaining'] === null) {
                return 0;
            }
            if ($a['days_remaining'] === null) {
                return 1; // a di akhir
            }
            if ($b['days_remaining'] === null) {
                return -1; // b di akhir
            }
            return $a['days_remaining'] - $b['days_remaining'];
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
        $machines = MaintenanceSchedule::getAvailableMachines();
        $users = User::select('id', 'name')->orderBy('name')->get();
        
        return view('maintenance-schedule.create', compact('machines', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'machine_id' => 'required|exists:machines,id',
            'user_id' => 'required|exists:users,id',
            'period_days' => 'required|integer|min:1',
            'start_date' => 'required|date',
        ]);

        try {
            // Check if machine already has schedule
            if (MaintenanceSchedule::machineHasSchedule($request->machine_id)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Mesin ini sudah memiliki schedule!',
                        'success' => false
                    ], 400);
                }
                
                return redirect()->back()->with('error', 'Mesin ini sudah memiliki schedule!')->withInput();
            }

            // Get machine data
            $machine = Machine::find($request->machine_id);
            $scheduleName = "Schedule {$machine->machine_name} ({$request->period_days} hari)";

            $schedule = MaintenanceSchedule::create([
                'machine_id' => $request->machine_id,
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
        $schedule = MaintenanceSchedule::with(['machine', 'user'])->findOrFail($id);
        return view('maintenance-schedule.show', compact('schedule'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $schedule = MaintenanceSchedule::with(['machine', 'user'])->findOrFail($id);
        
        // Check if request is AJAX
        if (request()->expectsJson()) {
            return response()->json([
                'machine_id' => $schedule->machine_id,
                'period_days' => $schedule->period_days,
                'start_date' => $schedule->start_date,
                'user_id' => $schedule->user_id
            ]);
        }

        // Get available machines plus current machine
        $machines = MaintenanceSchedule::getAvailableMachines();
        $machines->push($schedule->machine); // Add current machine to list
        
        $users = User::select('id', 'name')->orderBy('name')->get();
        
        return view('maintenance-schedule.edit', compact('schedule', 'machines', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'machine_id' => 'required|exists:machines,id',
            'user_id' => 'required|exists:users,id',
            'period_days' => 'required|integer|min:1',
            'start_date' => 'required|date',
        ]);

        try {
            $schedule = MaintenanceSchedule::findOrFail($id);
            
            // Check if machine already has schedule (exclude current schedule)
            if ($request->machine_id != $schedule->machine_id && 
                MaintenanceSchedule::machineHasSchedule($request->machine_id)) {
                
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Mesin ini sudah memiliki schedule!',
                        'success' => false
                    ], 400);
                }
                
                return redirect()->back()->with('error', 'Mesin ini sudah memiliki schedule!')->withInput();
            }
            
            // Generate schedule name otomatis berdasarkan mesin dan periode
            $machine = Machine::find($request->machine_id);
            $scheduleName = "Schedule {$machine->machine_name} ({$request->period_days} hari)";
            
            $schedule->update([
                'machine_id' => $request->machine_id,
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
     * Get available machines untuk dropdown
     */
    public function getAvailableMachines()
    {
        $machines = MaintenanceSchedule::getAvailableMachines();
        
        return response()->json([
            'data' => $machines
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

    /**
     * Check if machine has schedule (for AJAX validation)
     */
    public function checkMachine(Request $request)
    {
        $machineId = $request->get('machine_id');
        $excludeId = $request->get('exclude_id'); // for edit case
        
        $query = MaintenanceSchedule::where('machine_id', $machineId);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        $hasSchedule = $query->exists();
        
        return response()->json([
            'has_schedule' => $hasSchedule
        ]);
    }
}