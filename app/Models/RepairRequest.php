<?php

// Model RepairRequest.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class RepairRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_code',
        'machine_id',
        'part_id',
        'problem_description',
        'status',
        'requested_by',
        'requested_at',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
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
     * Relasi ke User (Requester)
     */
    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Relasi ke RepairPhotos
     */
    public function photos()
    {
        return $this->hasMany(RepairPhoto::class);
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
     * Get status label with color
     */
    public function getStatusLabelAttribute()
    {
        switch ($this->status) {
            case 'pending':
                return ['text' => 'Pending', 'class' => 'badge-warning'];
            case 'in_progress':
                return ['text' => 'In Progress', 'class' => 'badge-info'];
            case 'completed':
                return ['text' => 'Completed', 'class' => 'badge-success'];
            case 'cancelled':
                return ['text' => 'Cancelled', 'class' => 'badge-danger'];
            default:
                return ['text' => 'Unknown', 'class' => 'badge-secondary'];
        }
    }

    /**
     * Generate request code automatically
     */
    public static function generateRequestCode()
    {
        // Ambil request code terakhir yang formatnya REQ-angka (tanpa dash)
        $lastRequest = self::where('request_code', 'like', 'REQ-%')
                        ->where('request_code', 'not like', 'REQ-%-%') // Skip format lama
                        ->orderBy('id', 'desc')
                        ->first();

        if ($lastRequest) {
            $lastNumber = (int) str_replace('REQ-', '', $lastRequest->request_code);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return "REQ-" . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Scope untuk filter berdasarkan status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk filter berdasarkan tanggal
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('requested_at', [$startDate, $endDate]);
    }

    /**
     * Get all repair requests dengan data lengkap untuk DataTables
     */
    public static function getRequestData()
    {
        return self::with(['machine:id,machine_code,machine_name', 'part:id,part_code,part_name', 'requester:id,name', 'photos'])
            ->orderBy('requested_at', 'desc')
            ->get()
            ->map(function($request) {
                return [
                    'id' => $request->id,
                    'request_code' => $request->request_code,
                    'item_type' => $request->item_type,
                    'item_code' => $request->item_code,
                    'item_name' => $request->item_name,
                    'problem_description' => strlen($request->problem_description) > 50 
                        ? substr($request->problem_description, 0, 50) . '...' 
                        : $request->problem_description,
                    'full_problem_description' => $request->problem_description,
                    'status' => $request->status,
                    'status_label' => $request->status_label,
                    'requested_by' => $request->requester->name,
                    'requested_at' => $request->requested_at->format('d/m/Y H:i'),
                    'photos_count' => $request->photos->count(),
                    'has_photos' => $request->photos->count() > 0
                ];
            });
    }
}
