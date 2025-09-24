<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class MaintenanceSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'machine_id',
        'schedule_name',
        'period_days',
        'start_date',
        'user_id',
    ];

    protected $casts = [
        'start_date' => 'date',
    ];

    /**
     * Relasi ke Machine
     */
    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    /**
     * Relasi ke User (PIC)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get PIC name
     */
    public function getPicNameAttribute()
    {
        return $this->user ? $this->user->name : '-';
    }

    /**
     * Get item name (machine name)
     */
    public function getItemNameAttribute()
    {
        return $this->machine ? $this->machine->machine_name : '-';
    }

    /**
     * Get item code (machine code)
     */
    public function getItemCodeAttribute()
    {
        return $this->machine ? $this->machine->machine_code : '-';
    }

    /**
     * Get last checkup for this schedule
     */
    public function getLastCheckupAttribute()
    {
        return GeneralCheckup::where('machine_id', $this->machine_id)
            ->orderBy('checkup_date', 'desc')
            ->first();
    }

    /**
     * Get next check date
     */
    public function getNextCheckDateAttribute()
    {
        $lastCheckup = $this->last_checkup;
        
        if ($lastCheckup) {
            return Carbon::parse($lastCheckup->checkup_date)->addDays($this->period_days);
        } else {
            // Jika belum pernah check, return null
            return null;
        }
    }

    /**
     * Get days remaining until next check
     */
    public function getDaysRemainingAttribute()
    {
        if (!$this->next_check_date) {
            return null;
        }
        
        return now()->diffInDays($this->next_check_date, false);
    }

    /**
     * Get schedule status
     */
    public function getStatusAttribute()
    {
        $daysRemaining = $this->days_remaining;
        
        if ($daysRemaining === null) {
            return 'no_checkup';
        }
        
        if ($daysRemaining < 0) {
            return 'overdue';
        } elseif ($daysRemaining == 0) {
            return 'due_today';
        } elseif ($daysRemaining <= 3) {
            return 'due_soon';
        } else {
            return 'on_schedule';
        }
    }

    /**
     * Get status label with color
     */
    public function getStatusLabelAttribute()
    {
        switch ($this->status) {
            case 'no_checkup':
                return ['text' => 'No Checkup Yet', 'class' => 'badge-secondary'];
            case 'overdue':
                return ['text' => 'Overdue', 'class' => 'badge-danger'];
            case 'due_today':
                return ['text' => 'Due Today', 'class' => 'badge-warning'];
            case 'due_soon':
                return ['text' => 'Due Soon', 'class' => 'badge-info'];
            case 'on_schedule':
                return ['text' => 'On Schedule', 'class' => 'badge-success'];
            default:
                return ['text' => 'Unknown', 'class' => 'badge-secondary'];
        }
    }

    /**
     * Scope untuk filter berdasarkan PIC
     */
    public function scopeByPic($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Check if machine already has schedule
     */
    public static function machineHasSchedule($machineId)
    {
        return self::where('machine_id', $machineId)->exists();
    }

    /**
     * Get available machines (machines without schedule)
     */
    public static function getAvailableMachines()
    {
        $scheduledMachineIds = self::pluck('machine_id')->toArray();
        
        return Machine::whereNotIn('id', $scheduledMachineIds)
            ->select('id', 'machine_code', 'machine_name')
            ->get();
    }

    /**
     * Get all schedules with calculated data
     */
    public static function getScheduleData()
    {
        return self::with([
                'machine:id,machine_code,machine_name', 
                'user:id,name'
            ])
            ->get()
            ->map(function($schedule) {
                return [
                    'id' => $schedule->id,
                    'schedule_name' => $schedule->schedule_name,
                    'item_code' => $schedule->item_code,
                    'item_name' => $schedule->item_name,
                    'period_days' => $schedule->period_days,
                    'start_date' => $schedule->start_date->format('d/m/Y'),
                    'last_check' => $schedule->last_checkup ? $schedule->last_checkup->checkup_date->format('d/m/Y') : '-',
                    'next_check' => $schedule->next_check_date ? $schedule->next_check_date->format('d/m/Y') : '-',
                    'days_remaining' => $schedule->days_remaining,
                    'status' => $schedule->status,
                    'status_label' => $schedule->status_label,
                    'pic_name' => $schedule->pic_name,
                    'user_id' => $schedule->user_id
                ];
            });
    }
}