<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeAvailability extends Model
{
    protected $table = 'employee_availability';

    protected $fillable = [
        'employee_id',
        'appointment_id',
        'available_from',
        'available_to',
        'is_available',
        'reason'
    ];

    protected $casts = [
        'available_from' => 'datetime',
        'available_to' => 'datetime',
        'is_available' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}
