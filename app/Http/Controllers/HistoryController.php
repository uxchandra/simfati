<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GeneralCheckup;
use App\Models\MaintenanceSchedule;
use Carbon\Carbon;

class HistoryController extends Controller
{
    public function index()
    {
        return view('history.index');
    }

    public function getData(Request $request)
    {
        $query = GeneralCheckup::with(['machine:id,machine_code,machine_name', 'part:id,part_code,part_name', 'inspector:id,name'])
            ->select('id', 'checkup_code', 'machine_id', 'part_id', 'checkup_date', 'user_id', 'shift', 'overall_status', 'notes')
            ->whereNotNull('completed_at'); // Hanya ambil yang sudah selesai

        // Filter berdasarkan tanggal jika ada
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('checkup_date', [$request->start_date, $request->end_date]);
        }

        // Filter berdasarkan status jika ada
        if ($request->has('status') && $request->status != 'all') {
            $query->where('overall_status', $request->status);
        }

        $histories = $query->orderBy('checkup_date', 'desc')->get();

        $data = $histories->map(function($history) {
            return [
                'id' => $history->id,
                'checkup_code' => $history->checkup_code,
                'item_type' => $history->machine_id ? 'machine' : 'part',
                'item_code' => $history->machine_id ? $history->machine->machine_code : $history->part->part_code,
                'item_name' => $history->machine_id ? $history->machine->machine_name : $history->part->part_name,
                'checkup_date' => $history->checkup_date->format('d/m/Y H:i'),
                'inspector' => $history->inspector->name,
                'shift' => ucfirst($history->shift),
                'overall_status' => $history->overall_status,
                'notes' => $history->notes ?: '-'
            ];
        });

        return response()->json([
            'data' => $data
        ]);
    }

    public function getDetail($id)
    {
        $history = GeneralCheckup::with([
            'machine:id,machine_code,machine_name',
            'part:id,part_code,part_name',
            'inspector:id,name',
            'creator:id,name',
            'details.checkItem:id,item_name',
            'details.standards.checkStandard:id,standard_name',
            'photos'
        ])->findOrFail($id);

        return response()->json([
            'data' => $history
        ]);
    }
}