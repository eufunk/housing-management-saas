<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use App\Models\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use BelongsToOrganization, HasFactory, HasUlid, SoftDeletes;

    protected $fillable = [
        'document_category_id',
        'uploaded_by',
        'name',
        'file_path',
        'mime_type',
        'size',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class, 'document_category_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }
}
