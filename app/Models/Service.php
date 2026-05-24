<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Service extends Model
{
    public const PRICING_PER_SQM = 'per_sqm';
    public const PRICING_FIXED = 'fixed';

    protected $fillable = [
        'service_name',
        'description',
        'pricing_type',
        'base_price'
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
    ];

    public function appointments(): BelongsToMany
    {
        return $this->belongsToMany(Appointment::class, 'appointment_service')
            ->withPivot('quantity', 'custom_price')
            ->withTimestamps();
    }

    public function isPricedPerSquareMeter(): bool
    {
        return $this->pricing_type === self::PRICING_PER_SQM;
    }

    public function getPriceLabelAttribute(): string
    {
        $price = 'PHP ' . number_format((float) $this->base_price, 2);

        return $this->isPricedPerSquareMeter() ? "{$price} / sqm" : "{$price} fixed";
    }

    public function getQuantityLabelAttribute(): string
    {
        return $this->isPricedPerSquareMeter() ? 'Area (sqm)' : 'Quantity';
    }

    public function getUnitLabelAttribute(): string
    {
        return $this->isPricedPerSquareMeter() ? 'sqm' : 'qty';
    }
}
