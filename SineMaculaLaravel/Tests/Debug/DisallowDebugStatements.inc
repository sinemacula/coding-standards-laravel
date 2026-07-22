<?php

namespace App\Example;

use Illuminate\Support\Collection;

class DebugExample
{
    public function leftovers(Collection $collection): void
    {
        dd($value);
        dump($value);
        ray($value);
        var_dump($value);
        print_r($value);

        $collection->dump();
        Collection::dump();
        $this->dump($value);

        DD($value);
        sprintf('%s', $value);
        $alias = dd;
    }

    private function dump(mixed $value): void
    {
        //
    }
}
