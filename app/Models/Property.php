<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use App\Models\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    use BelongsToOrganization, HasFactory, HasUlid, SoftDeletes;

    protected $fillable = [
        'owner_id',
        'name',
        'street',
        'postal_code',
        'city',
        'country',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }

    public function buildings(): HasMany
    {
        return $this->hasMany(Building::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class);
    }
}
