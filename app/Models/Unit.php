<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use App\Models\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use BelongsToOrganization, HasFactory, HasUlid, SoftDeletes;

    protected $fillable = [
        'building_id',
        'unit_number',
        'floor',
        'size_sqm',
        'rooms',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'size_sqm' => 'decimal:2',
        ];
    }

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function leases(): HasMany
    {
        return $this->hasMany(Lease::class);
    }

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class);
    }
}
