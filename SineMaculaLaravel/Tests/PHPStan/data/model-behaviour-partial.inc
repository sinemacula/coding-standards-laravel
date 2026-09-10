<?php

namespace ModelBehaviourPartial;

use App\Support\Model as Framework;

abstract class Model extends Framework
{
    protected $fillable = ['name'];
}

class Ticket extends Model
{
    public function escalate(): void
    {
        //
    }
}

abstract class Ledger extends Framework
{
    protected $fillable = ['name'];
}

class Entry extends Ledger
{
    public function reconcile(): void
    {
        //
    }
}
