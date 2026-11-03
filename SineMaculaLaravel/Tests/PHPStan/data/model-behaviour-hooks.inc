<?php

namespace ModelBehaviourHooks;

use Illuminate\Database\Eloquent\Model;

class Archive extends Model
{
    public function casts(): array
    {
        return [];
    }

    public static function boot(): void
    {
        //
    }

    public static function booting(): void
    {
        //
    }

    public static function booted(): void
    {
        //
    }

    public static function newFactory()
    {
        return null;
    }

    public function newCollection(array $models = [])
    {
        return $models;
    }

    public function newEloquentBuilder($query)
    {
        return $query;
    }

    public function toSearchableArray(): array
    {
        return [];
    }
}

class Ticket extends Model
{
    public function resolveRouteBinding($value, $field = null)
    {
        return null;
    }

    public function resolveSoftDeletableRouteBinding($value, $field = null)
    {
        return null;
    }

    public function resolveChildRouteBinding($childType, $value, $field)
    {
        return null;
    }

    public function resolveSoftDeletableChildRouteBinding($childType, $value, $field)
    {
        return null;
    }
}
