<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class Thing extends Model
{
    public $timestamps = false;

    public $incrementing = false;
}

class Account extends Authenticatable
{
    public $timestamps = false;
}

class RoleMember extends Pivot
{
    public $incrementing = false;
}

class PlainObject
{
    public $value;
}

class CreateThingsTable extends Migration
{
    public $withinTransaction = false;
}

class ThingResource extends JsonResource
{
    public $preserveKeys = true;
}

class ThingCollection extends ResourceCollection
{
    public $collects = ThingResource::class;
}
