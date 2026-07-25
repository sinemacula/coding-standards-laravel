<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\Routing;

use PHPUnit\Framework\Attributes\CoversClass;
use SineMacula\CodingStandardsLaravel\Sniffs\Routing\RouteCall;
use SineMacula\CodingStandardsLaravel\Sniffs\Routing\RouteParser;
use SineMaculaLaravel\Sniffs\Routing\CollapseResourceRoutesSniff;
use SineMaculaLaravel\Tests\AbstractSniffTestCase;

/**
 * Tests for the resource route collapse sniff.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @internal
 */
#[CoversClass(CollapseResourceRoutesSniff::class)]
#[CoversClass(RouteCall::class)]
#[CoversClass(RouteParser::class)]
final class CollapseResourceRoutesSniffTest extends AbstractSniffTestCase
{
    /**
     * A partial set of resource routes for one controller collapses; the
     * suggestion lists the present actions in canonical order.
     *
     * @return void
     */
    public function testCollapsesAPartialResource(): void
    {
        $message = 'Routes for "roles" (RoleController) should collapse into a single '
            . 'Route::apiResource(\'roles\', RoleController::class)->only([\'index\', \'store\', \'destroy\']).';

        $this->assertErrorMessagesOnLines('CollapseBasic.inc', [3 => [$message]]);
    }

    /**
     * A whole resource - including update registered on both PUT and PATCH -
     * collapses to a bare apiResource call with no only() constraint.
     *
     * @return void
     */
    public function testCollapsesAWholeResource(): void
    {
        $message = 'Routes for "roles" (RoleController) should collapse into a single '
            . 'Route::apiResource(\'roles\', RoleController::class).';

        $this->assertErrorMessagesOnLines('CollapseFullResource.inc', [3 => [$message]]);
    }

    /**
     * Routes are grouped within their enclosing group closure: a resource split
     * across two group() blocks is not merged, so the two-route block collapses
     * while the lone route in the other block is left alone.
     *
     * @return void
     */
    public function testGroupsRoutesWithinTheirClosure(): void
    {
        $this->assertErrorsOnLines('CollapseInGroups.inc', [4]);
    }

    /**
     * The HTTP verb is matched case-insensitively, so upper-case verb calls
     * resolve to their resource action and still collapse.
     *
     * @return void
     */
    public function testCollapsesCaseInsensitiveVerbs(): void
    {
        $this->assertErrorsOnLines('CollapseUppercaseVerb.inc', [3]);
    }

    /**
     * A leading slash on the URI is trimmed before the base is resolved, so
     * `/roles` collapses exactly as `roles` does.
     *
     * @return void
     */
    public function testCollapsesUrisWithALeadingSlash(): void
    {
        $this->assertErrorsOnLines('CollapseLeadingSlash.inc', [3]);
    }

    /**
     * Single resource routes for one controller split across two group closures
     * stay in their own scope and are not merged into a collapsible set.
     *
     * @return void
     */
    public function testDoesNotMergeSingleRoutesAcrossScopes(): void
    {
        $this->assertErrorsOnLines('ScopeIsolationSingles.inc', []);
    }

    /**
     * A route dispatched to a closure carries no controller and neither blocks
     * nor joins a collapse; the controller-backed routes beside it still fold.
     *
     * @return void
     */
    public function testIgnoresClosureRoutesWhenCollapsing(): void
    {
        $this->assertErrorsOnLines('CollapseClosureIndex.inc', [4]);
    }

    /**
     * Routes are keyed by controller, so a nested sub-resource on another
     * controller does not disturb the base resource's collapse.
     *
     * @return void
     */
    public function testKeepsControllersDistinct(): void
    {
        $this->assertErrorsOnLines('CollapseMultipleControllers.inc', [3]);
    }

    /**
     * Existing apiResource/resource registrations, closure routes and a lone
     * resource route are all left alone.
     *
     * @return void
     */
    public function testLeavesNonCollapsibleRoutesAlone(): void
    {
        $this->assertErrorsOnLines('Clean.inc', []);
    }

    /**
     * An extra, non-resource route for the same base is a reason the routes
     * were split, so the group is not flagged.
     *
     * @return void
     */
    public function testSuppressesOnAnExtraRoute(): void
    {
        $this->assertErrorsOnLines('SuppressExtraRoute.inc', []);
    }

    /**
     * A resource-shaped route dispatched to a non-canonical method name is a
     * custom mapping apiResource cannot express, so the group is not flagged.
     *
     * @return void
     */
    public function testSuppressesOnARenamedMethod(): void
    {
        $this->assertErrorsOnLines('SuppressRenamedMethod.inc', []);
    }

    /**
     * A per-route modifier beyond the URI-constraint family (here name()) is
     * not expressible on the resource, so the group is not flagged.
     *
     * @return void
     */
    public function testSuppressesOnANonConstraintModifier(): void
    {
        $this->assertErrorsOnLines('SuppressModifier.inc', []);
    }

    /**
     * Member routes with different parameter names cannot share one resource
     * parameter, so the group is not flagged.
     *
     * @return void
     */
    public function testSuppressesOnInconsistentParameters(): void
    {
        $this->assertErrorsOnLines('SuppressInconsistentParam.inc', []);
    }

    /**
     * A duplicated action (two routes mapping to index) is a malformed set, so
     * the group is not flagged.
     *
     * @return void
     */
    public function testSuppressesOnADuplicateAction(): void
    {
        $this->assertErrorsOnLines('SuppressDuplicateAction.inc', []);
    }
}
