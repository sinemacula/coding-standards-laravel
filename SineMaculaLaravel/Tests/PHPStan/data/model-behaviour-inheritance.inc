<?php

namespace ModelBehaviourInheritance;

use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Model;

interface Revocable
{
    public function revoke(): void;
}

interface Auditable extends Revocable
{
    public function audit(): void;
}

abstract class BaseModel extends Model
{
    protected $fillable = ['name'];
}

class Subscription extends BaseModel
{
    public function renew(): void
    {
        //
    }
}

class Ledger extends BaseModel
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

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function newUniqueId(): string
    {
        return 'identifier';
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        return $query;
    }

    public function getMorphClass(): string
    {
        return 'ledger';
    }

    public function prunable()
    {
        return null;
    }

    public function toSearchableArray(): array
    {
        return [];
    }
}

abstract class MandatingBase extends Model
{
    abstract public function purge(): void;
}

class Assignment extends MandatingBase
{
    public function purge(): void
    {
        //
    }
}

class Session extends Model implements HasLocalePreference
{
    public function preferredLocale(): ?string
    {
        return 'en';
    }
}

abstract class RevocableModel extends Model implements Auditable
{
    protected $fillable = ['revoked_at'];
}

class Grant extends RevocableModel
{
    public function revoke(): void
    {
        //
    }

    public function audit(): void
    {
        //
    }

    public function expire(): void
    {
        //
    }
}

class Statement extends Model
{
    public function toArray(): array
    {
        return [];
    }

    public function jsonSerialize(): mixed
    {
        return [];
    }

    public function offsetGet($offset): mixed
    {
        return null;
    }
}

class Registry
{
    protected $items = [];
}

class Attachment extends Registry
{
    public function revoke(): void
    {
        //
    }
}

class Account extends \Illuminate\Foundation\Auth\User
{
    public function getAuthIdentifier()
    {
        return 1;
    }
}
