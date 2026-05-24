<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Employee extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'position',
        'status'
    ];

    public function appointments(): BelongsToMany
    {
        return $this->belongsToMany(Appointment::class, 'appointment_employee')
            ->withPivot('task', 'is_available', 'start_time', 'end_time')
            ->withTimestamps();
    }

    public function availability(): HasMany
    {
        return $this->hasMany(EmployeeAvailability::class);
    }

    /**
     * Check if employee is available on specific date and time
     */
    public function isAvailableAtDateTime($dateTime): bool
    {
        $appointment = $this->appointments()
            ->whereDate('appointment_employee.start_time', $dateTime->toDateString())
            ->whereTime('appointment_employee.start_time', $dateTime->toTimeString())
            ->exists();

        return !$appointment;
    }

    /**
     * Get all booked slots for a specific date
     */
    public function getBookedSlots($date)
    {
        return $this->appointments()
            ->whereDate('appointment_employee.start_time', $date)
            ->select('appointment_employee.start_time', 'appointment_employee.end_time')
            ->get();
    }

    /**
     * Get available time slots for a date
     */
    public function getAvailableSlots($date, $slotDuration = 60)
    {
        $bookedSlots = $this->getBookedSlots($date);
        
        $allSlots = $this->generateTimeSlots($date, $slotDuration);
        $availableSlots = [];

        foreach ($allSlots as $slot) {
            $isBooked = false;
            
            foreach ($bookedSlots as $booking) {
                if ($slot['start'] >= $booking->start_time && 
                    $slot['start'] < $booking->end_time) {
                    $isBooked = true;
                    break;
                }
            }
            
            if (!$isBooked) {
                $availableSlots[] = $slot;
            }
        }

        return $availableSlots;
    }

    /**
     * Generate time slots for a day
     */
    private function generateTimeSlots($date, $duration = 60)
    {
        $slots = [];
        $startTime = $date->copy()->setHour(6)->setMinute(0);
        $endTime = $date->copy()->setHour(22)->setMinute(0);

        while ($startTime < $endTime) {
            $slots[] = [
                'start' => $startTime->toDateTimeString(),
                'start_display' => $startTime->format('h:i A'),
                'end' => $startTime->copy()->addMinutes($duration)->toDateTimeString(),
            ];
            $startTime->addMinutes($duration);
        }

        return $slots;
    }

    /**
     * Scope: Filter by active status
     */
    public function scopeActive(Builder $query)
    {
        return $query->where('status', 'Active');
    }

    /**
     * Scope: Filter available on specific date
     */
    public function scopeAvailableOn(Builder $query, $date)
    {
        return $query->whereDoesntHave('appointments', function (Builder $q) use ($date) {
            $q->whereDate('appointment_employee.start_time', $date);
        });
    }
}
