<?php

namespace App\Services;

class AttributeService
{
    #[Override]
    public function unqualified()
    {
    }

    #[Scheduled('daily')]
    #[\Override]
    public function stacked()
    {
    }

    #[Scheduled('daily')]#[\Override]
    public function adjacent()
    {
    }

    #[Scheduled('daily'), \Override]
    public function grouped()
    {
    }

    #[Cached(Override)]
    public function decorated()
    {
    }
}

#[\Override]function orphan()
{
}

takesCallback(#[\Override] function () {
});
