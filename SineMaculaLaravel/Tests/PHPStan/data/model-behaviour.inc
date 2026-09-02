<?php

namespace ModelBehaviour;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Credential extends Model
{
    public const string STATUS_LIVE = 'live';

    protected $fillable = ['token'];

    public function tokens(): HasMany
    {
        return $this->hasMany(Token::class);
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class)->withTrashed();
    }

    protected function casts(): array
    {
        return ['revoked_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        //
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected function digest(): Attribute
    {
        return Attribute::make(get: static fn (string $value): string => $value);
    }

    public function label(): Attribute
    {
        return Attribute::make(get: static fn (string $value): string => $value);
    }

    protected function helper(): string
    {
        return 'detail';
    }

    #[Scope]
    protected function live(Builder $query): void
    {
        $query->whereNull('revoked_at');
    }

    public function revoke(): void
    {
        $this->forceFill(['revoked_at' => now()])->save();
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereNotNull('expires_at');
    }
}

class Token extends Model
{
    public function casts(): array
    {
        return ['expires_at' => 'datetime'];
    }
}

class NotAModel
{
    public function revoke(): void
    {
        //
    }

    #[Scope]
    protected function live(Builder $query): void
    {
        //
    }
}
