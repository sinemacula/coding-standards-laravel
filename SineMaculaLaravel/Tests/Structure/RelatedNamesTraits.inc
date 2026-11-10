<?php

namespace App\Domain;

class FirstStatement
{
    use Marker;
    use Other;
}

class SecondInList
{
    use Other, Marker;
}

class ConflictBlock
{
    use Other {
        Marker::handle insteadof Another;
    }
}

class ClosureUse
{
    public function handle(): void
    {
        $run = function () use ($marker) {
            return $marker;
        };
    }
}
