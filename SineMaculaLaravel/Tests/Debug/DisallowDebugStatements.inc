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

    public function returnMode(mixed $value): string
    {
        $message = print_r($value, true);
        $upper   = print_r($value, TRUE);
        $named   = print_r($value, return: true);
        $flagged = print_r($value, $this->wantsString());
        $nested  = print_r([$value, max(1, 2)], true);

        print_r($value, false);
        print_r($value, null);
        print_r($value,);

        return $message . $upper . $named . $flagged . $nested;
    }

    private function wantsString(): bool
    {
        return true;
    }

    public function moreForms(mixed $value): string
    {
        dd($value, true);
        print_r([$value, $value]);

        return PRINT_R($value, true);
    }

    private function dump(mixed $value): void
    {
        //
    }
}
