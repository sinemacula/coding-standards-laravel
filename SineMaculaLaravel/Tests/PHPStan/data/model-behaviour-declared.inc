<?php

namespace ModelBehaviourDeclared;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait CarriesBehaviour
{
    public function carry(): void
    {
        //
    }

    #[Scope]
    protected function live(Builder $query): void
    {
        //
    }

    public function casts(): array
    {
        return [];
    }
}

class Consignment extends Model
{
    use CarriesBehaviour;
}

class Depot
{
    public function make(): object
    {
        return new class extends Model
        {
            public function detonate(): void
            {
                //
            }
        };
    }
}
