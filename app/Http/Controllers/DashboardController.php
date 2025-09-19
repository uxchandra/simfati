<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\Sparepart;
use App\Models\MaintenanceSchedule;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $machineCount = Machine::count();
        $partCount = Sparepart::count();
        $activeMachineCount = Machine::where('status', 'active')->count();
        $scheduleCount = MaintenanceSchedule::count();

        return view('dashboard', [
            'machineCount' => $machineCount,
            'partCount' => $partCount,
            'activeMachineCount' => $activeMachineCount,
            'scheduleCount' => $scheduleCount,
        ]);
    }
}
