<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait GeneratesStringId
{
    public static function bootGeneratesStringId(): void
    {
        static::creating(function (self $model) {
            if ($model->getKey()) {
                return;
            }

            $model->{$model->getKeyName()} = static::newUniqueStringId();
        });
    }

    protected static function newUniqueStringId(): string
    {
        do {
            $id = static::stringIdPrefix().Str::upper(Str::random(6));
        } while (static::query()->whereKey($id)->exists());

        return $id;
    }

    protected static function stringIdPrefix(): string
    {
        return property_exists(static::class, 'stringIdPrefix')
            ? static::$stringIdPrefix
            : '';
    }
}
