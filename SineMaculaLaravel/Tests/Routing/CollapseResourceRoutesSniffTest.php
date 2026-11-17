<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\Routing;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
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
     * Collapses a partial resource, listing its actions in canonical order.
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
     * Collapses a whole resource to a bare apiResource call.
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
     * Fixtures that collapse, with the line the group is reported on.
     *
     * @return iterable<string, array{string, int}>
     */
    public static function collapsibleFixtures(): iterable
    {
        yield 'within a group closure' => ['CollapseInGroups.inc', 4];
        yield 'uppercase verb' => ['CollapseUppercaseVerb.inc', 3];
        yield 'leading slash' => ['CollapseLeadingSlash.inc', 3];
        yield 'beside a closure route' => ['CollapseClosureIndex.inc', 4];
        yield 'beside a sub-resource controller' => ['CollapseMultipleControllers.inc', 3];
    }

    /**
     * Collapses a resource whatever the grouping, verb case or route mix.
     *
     * @param  string  $fixture
     * @param  int  $line
     * @return void
     */
    #[DataProvider('collapsibleFixtures')]
    public function testCollapsesAResource(string $fixture, int $line): void
    {
        $this->assertErrorsOnLines($fixture, [$line]);
    }

    /**
     * Fixtures that must not collapse.
     *
     * @return iterable<string, array{string}>
     */
    public static function nonCollapsibleFixtures(): iterable
    {
        yield 'existing registrations and lone routes' => ['Clean.inc'];
        yield 'single routes across group closures' => ['ScopeIsolationSingles.inc'];
        yield 'extra route' => ['SuppressExtraRoute.inc'];
        yield 'renamed method' => ['SuppressRenamedMethod.inc'];
        yield 'non-constraint modifier' => ['SuppressModifier.inc'];
        yield 'inconsistent parameters' => ['SuppressInconsistentParam.inc'];
        yield 'duplicate action' => ['SuppressDuplicateAction.inc'];
    }

    /**
     * Leaves routes alone that do not form a clean, whole resource.
     *
     * @param  string  $fixture
     * @return void
     */
    #[DataProvider('nonCollapsibleFixtures')]
    public function testLeavesNonCollapsibleRoutesAlone(string $fixture): void
    {
        $this->assertErrorsOnLines($fixture, []);
    }
}
