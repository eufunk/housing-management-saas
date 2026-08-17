<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use App\Models\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contractor extends Model
{
    use BelongsToOrganization, HasFactory, HasUlid, SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_name',
        'contact_name',
        'email',
        'phone',
        'specialty',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class);
    }
}
