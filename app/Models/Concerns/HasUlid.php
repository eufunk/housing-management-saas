<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Adds a publicly exposable ULID identifier alongside the internal
 * auto-increment primary key, so external IDs (URLs, APIs) never leak
 * sequential database IDs across tenants.
 */
trait HasUlid
{
    protected static function bootHasUlid(): void
    {
        static::creating(function ($model) {
            if (empty($model->ulid)) {
                $model->ulid = (string) Str::ulid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }
}
