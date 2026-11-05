<?php

namespace App\Http\Controllers;

/**
 * @utility
 */
#[Middleware('auth')]
class AttributedUtilityController
{
    public function rebuild(): void
    {
    }
}

class AttributedController
{
    /**
     * @non-rest-action
     */
    #[ResponseStatus(204)]
    public function destroyAll(): void
    {
    }

    /**
     * @non-rest-action
     */
    #[Throttle(10)]
    #[Middleware('signed')]
    public function stacked(): void
    {
    }

    /**
     * @utility
     */
    #[Throttle(10), Middleware(new Guard('api'))]
    public function grouped(): void
    {
    }

    /** @non-rest-action */
    #[Throttle(10)] #[Middleware('signed')] public function sameLine(): void
    {
    }

    #[ResponseStatus(204)]
    public function undocumented(): void
    {
    }

    /**
     * Archives the record.
     */
    #[ResponseStatus(204)]
    public function archive(): void
    {
    }

    /** @non-rest-action */#[Throttle(10)]#[Middleware('signed')]
    public function glued(): void
    {
    }
}
