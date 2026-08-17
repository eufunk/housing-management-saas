<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use App\Models\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentCategory extends Model
{
    use BelongsToOrganization, HasFactory, HasUlid;

    protected $fillable = [
        'name',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
