<?php

namespace ModelBehaviourUnresolved;

use App\Support\Model;
use App\Support\Registry;

class Credential extends Model
{
    public function revoke(): void
    {
        //
    }
}

class Setting extends Registry
{
    public function revoke(): void
    {
        //
    }
}

class AddressLink extends \App\Support\MorphPivot
{
    public function relink(): void
    {
        //
    }
}
