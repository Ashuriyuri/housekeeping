<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Appointment extends Model
{
    public const DEFAULT_PRICE_PER_SQM = 55;

    protected $fillable = [
        'customer_name',
        'address',
        'area_sqm',
        'schedule_date',
        'status',
        'notes'
    ];

    protected $casts = [
        'schedule_date' => 'datetime',
    ];

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'appointment_service')
            ->withPivot('quantity', 'custom_price')
            ->withTimestamps();
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'appointment_employee')
            ->withPivot('task')
            ->withTimestamps();
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function getTotalSquareMeterAttribute()
    {
        $serviceArea = $this->services->sum(function ($service) {
            if (!$service->isPricedPerSquareMeter()) {
                return 0;
            }

            return (float) ($service->pivot->quantity ?? 0);
        });

        return $serviceArea > 0 ? $serviceArea : (float) ($this->area_sqm ?? 0);
    }

    public function getRatePerSquareMeterAttribute()
    {
        return self::DEFAULT_PRICE_PER_SQM;
    }

    public function getTotalPriceAttribute()
    {
        if ($this->services->isEmpty()) {
            return $this->total_square_meter * self::DEFAULT_PRICE_PER_SQM;
        }

        return $this->services->sum(function ($service) {
            $unitPrice = (float) ($service->pivot->custom_price ?? $service->base_price);
            $quantity = (float) ($service->pivot->quantity ?? 1);

            return $unitPrice * $quantity;
        });
    }

    public function getMinimumCleaningPriceAttribute()
    {
        if (!$this->area_sqm) {
            return 0;
        }

        return $this->area_sqm * self::DEFAULT_PRICE_PER_SQM;
    }

    public function getEstimatedPriceAttribute()
    {
        return $this->total_price;
    }
}
