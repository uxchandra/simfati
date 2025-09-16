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
        'part_id',
        'schedule_name',
        'period_days',
        'start_date',
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
     * Relasi ke Part
     */
    public function part()
    {
        return $this->belongsTo(Part::class);
    }

    /**
     * Get item name (machine or part name)
     */
    public function getItemNameAttribute()
    {
        return $this->machine ? $this->machine->machine_name : $this->part->part_name;
    }

    /**
     * Get item code (machine or part code)
     */
    public function getItemCodeAttribute()
    {
        return $this->machine ? $this->machine->machine_code : $this->part->part_code;
    }

    /**
     * Get item type (machine or part)
     */
    public function getItemTypeAttribute()
    {
        return $this->machine ? 'machine' : 'part';
    }

    /**
     * Get last checkup for this schedule
     */
    public function getLastCheckupAttribute()
    {
        if ($this->machine_id) {
            return GeneralCheckup::where('machine_id', $this->machine_id)
                ->orderBy('checkup_date', 'desc')
                ->first();
        } else {
            return GeneralCheckup::where('part_id', $this->part_id)
                ->orderBy('checkup_date', 'desc')
                ->first();
        }
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
            // Jika belum pernah check, next check = start_date + period
            return Carbon::parse($this->start_date)->addDays($this->period_days);
        }
    }

    /**
     * Get days remaining until next check
     */
    public function getDaysRemainingAttribute()
    {
        return now()->diffInDays($this->next_check_date, false);
    }

    /**
     * Get schedule status
     */
    public function getStatusAttribute()
    {
        $daysRemaining = $this->days_remaining;
        
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
     * Scope untuk filter berdasarkan status
     */
    public function scopeOverdue($query)
    {
        // This needs to be implemented with raw SQL for performance
        // For now, we'll filter in the collection
        return $query;
    }

    /**
     * Scope untuk filter berdasarkan tanggal
     */
    public function scopeDueDate($query, $date)
    {
        // This would need complex calculation, better to filter after collection
        return $query;
    }

    /**
     * Get all schedules with calculated data
     */
    public static function getScheduleData()
    {
        return self::with(['machine:id,machine_code,machine_name', 'part:id,part_code,part_name'])
            ->get()
            ->map(function($schedule) {
                return [
                    'id' => $schedule->id,
                    'schedule_name' => $schedule->schedule_name,
                    'item_type' => $schedule->item_type,
                    'item_code' => $schedule->item_code,
                    'item_name' => $schedule->item_name,
                    'period_days' => $schedule->period_days,
                    'start_date' => $schedule->start_date->format('d/m/Y'),
                    'last_check' => $schedule->last_checkup ? $schedule->last_checkup->checkup_date->format('d/m/Y') : 'Belum pernah',
                    'next_check' => $schedule->next_check_date->format('d/m/Y'),
                    'days_remaining' => $schedule->days_remaining,
                    'status' => $schedule->status,
                    'status_label' => $schedule->status_label
                ];
            });
    }
}