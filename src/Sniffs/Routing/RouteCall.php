<?php

declare(strict_types = 1);

namespace SineMacula\CodingStandardsLaravel\Sniffs\Routing;

/**
 * A single parsed `Route::verb(...)` registration.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 */
final readonly class RouteCall
{
    /**
     * @param  int  $scope
     * @param  string  $controller
     * @param  string  $base
     * @param  string|null  $action
     * @param  string|null  $param
     * @param  int  $pointer
     * @return void
     */
    public function __construct(

        /** @var int Token pointer of the enclosing group closure, or 0 at the file root. */
        public int $scope,

        /** @var string Controller short name the route dispatches to. */
        public string $controller,

        /** @var string Resource base URI, the first path segment. */
        public string $base,

        /** @var string|null Canonical resource action, or null when the route is not one. */
        public ?string $action,

        /** @var string|null Member route parameter name, or null for a collection route. */
        public ?string $param,

        /** @var int Token pointer of the Route facade, for error placement. */
        public int $pointer,
    ) {}
}
