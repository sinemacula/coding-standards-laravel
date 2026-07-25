<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Sniffs\Routing;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SineMacula\CodingStandardsLaravel\Sniffs\Routing\RouteParser;

/**
 * Flag individual routes that could collapse into an apiResource registration.
 *
 * When a controller's routes for one resource base are all canonical
 * apiResource actions - `GET base` (index), `POST base` (store), `GET base/{p}`
 * (show), `PUT`/`PATCH base/{p}` (update), `DELETE base/{p}` (destroy),
 * dispatched to the matching action method - the group is flagged to collapse
 * into a single `Route::apiResource('base', Controller::class)->only([...])`.
 *
 * The sniff is deliberately conservative and stays silent whenever a group is
 * not a clean, whole resource: any extra or nested route for the same base, a
 * verb/URI that maps to no resource action, an action method renamed off the
 * canonical name, an inconsistent member parameter, or any per-route modifier
 * beyond the URI-constraint family (`whereUuid` and friends) - each a reason
 * the routes were split on purpose. Routes are grouped within their enclosing
 * route-group closure, so definitions in different `group()` blocks are never
 * merged. Existing `apiResource`/`resource` calls are ignored.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 */
final class CollapseResourceRoutesSniff implements Sniff
{
    /** @var array<int, string> Resource actions in their canonical registration order. */
    private const array ACTION_ORDER = ['index', 'store', 'show', 'update', 'destroy'];

    /** @var array<int, string> Chained modifiers a resource registration can still express, so they do not block a collapse. */
    public array $collapsibleModifiers = [
        'where', 'whereUuid', 'whereUlid', 'whereNumber', 'whereAlpha', 'whereAlphaNumeric', 'whereIn',
    ];

    /**
     * Register the tokens this sniff listens for.
     *
     * @return array<int, int|string>
     */
    #[\Override]
    public function register(): array
    {
        return [T_OPEN_TAG];
    }

    /**
     * Process a routes file once, from its opening tag.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $stackPtr
     * @return void
     */
    #[\Override]
    public function process(File $phpcsFile, $stackPtr): void
    {
        if ($phpcsFile->findPrevious(T_OPEN_TAG, $stackPtr - 1) !== false) {
            return;
        }

        $routes = (new RouteParser($this->collapsibleModifiers))->parse($phpcsFile);

        foreach ($this->groupRoutes($routes) as $group) {
            $this->reportGroup($phpcsFile, $group);
        }
    }

    /**
     * Group parsed routes by their enclosing scope, controller and base.
     *
     * @param  array<int, \SineMacula\CodingStandardsLaravel\Sniffs\Routing\RouteCall>  $routes
     * @return array<int, array<int, \SineMacula\CodingStandardsLaravel\Sniffs\Routing\RouteCall>>
     */
    private function groupRoutes(array $routes): array
    {
        $nested = [];

        foreach ($routes as $route) {
            $nested[$route->scope][$route->controller][$route->base][] = $route;
        }

        $groups = [];

        foreach ($nested as $byController) {
            foreach ($byController as $byBase) {
                foreach ($byBase as $group) {
                    $groups[] = $group;
                }
            }
        }

        return $groups;
    }

    /**
     * Flag a group of routes that forms a whole, collapsible resource.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  array<int, \SineMacula\CodingStandardsLaravel\Sniffs\Routing\RouteCall>  $routes
     * @return void
     */
    private function reportGroup(File $phpcsFile, array $routes): void
    {
        $actions = $this->collapsibleActions($routes);

        if ($actions === null) {
            return;
        }

        // Routes are grouped in file order, so the first is the earliest.
        $first = $routes[0];

        $phpcsFile->addError(
            'Routes for "%s" (%s) should collapse into a single %s.',
            $first->pointer,
            'Collapsible',
            [$first->base, $first->controller, $this->suggestion($first->base, $first->controller, $actions)],
        );
    }

    /**
     * The distinct resource actions a group collapses to, or null when it does
     * not form a clean, whole resource.
     *
     * @param  array<int, \SineMacula\CodingStandardsLaravel\Sniffs\Routing\RouteCall>  $routes
     * @return array<int, string>|null
     */
    private function collapsibleActions(array $routes): ?array
    {
        $actions = [];
        $params  = [];

        foreach ($routes as $route) {
            if ($route->action === null) {
                return null;
            }

            $actions[] = $route->action;

            if ($route->param === null) {
                continue;
            }

            $params[$route->param] = true;
        }

        if (count($params) > 1 || !$this->actionsConsistent($actions)) {
            return null;
        }

        $distinct = $this->orderActions(array_unique($actions));

        return count($distinct) >= 2 ? $distinct : null;
    }

    /**
     * Whether a group's actions carry no illegitimate duplicate (only update
     * may appear twice, from PUT and PATCH).
     *
     * @param  array<int, string>  $actions
     * @return bool
     */
    private function actionsConsistent(array $actions): bool
    {
        $nonUpdate = array_filter($actions, static fn (string $action): bool => $action !== 'update');

        return count($nonUpdate) === count(array_unique($nonUpdate))
            && count(array_filter($actions, static fn (string $action): bool => $action === 'update')) <= 2;
    }

    /**
     * Sort actions into their canonical resource order.
     *
     * @param  array<int, string>  $actions
     * @return array<int, string>
     */
    private function orderActions(array $actions): array
    {
        return array_values(array_filter(self::ACTION_ORDER, static fn (string $action): bool => in_array($action, $actions, true)));
    }

    /**
     * Render the apiResource call a group should collapse into.
     *
     * @param  string  $base
     * @param  string  $controller
     * @param  array<int, string>  $actions
     * @return string
     */
    private function suggestion(string $base, string $controller, array $actions): string
    {
        $call = sprintf('Route::apiResource(\'%s\', %s::class)', $base, $controller);

        if (count($actions) === count(self::ACTION_ORDER)) {
            return $call;
        }

        return sprintf('%s->only([\'%s\'])', $call, implode('\', \'', $actions));
    }
}
