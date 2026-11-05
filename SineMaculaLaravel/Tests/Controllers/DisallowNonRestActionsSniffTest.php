<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\Controllers;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\ReadsDeclarationNames;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\ReadsDocblockTags;
use SineMaculaLaravel\Sniffs\Controllers\DisallowNonRestActionsSniff;
use SineMaculaLaravel\Tests\AbstractSniffTestCase;

/**
 * Tests for the non-REST controller action sniff.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @internal
 */
#[CoversClass(DisallowNonRestActionsSniff::class)]
#[CoversTrait(ReadsDeclarationNames::class)]
#[CoversTrait(ReadsDocblockTags::class)]
final class DisallowNonRestActionsSniffTest extends AbstractSniffTestCase
{
    /**
     * Only a genuine non-canonical action is flagged: the REST verbs, __invoke,
     * the constructor, framework overrides, static and non-public helpers,
     * abstract bases, non-controllers, and methods marked utility or
     * non-rest-action.
     *
     * @return void
     */
    public function testFlagsOnlyGenuineNonRestActions(): void
    {
        $this->assertErrorsOnLines('DisallowNonRestActions.inc', [65]);
    }

    /**
     * The error names the offending method and spells out both escape hatches.
     *
     * @return void
     */
    public function testRendersExactErrorMessage(): void
    {
        $this->assertErrorMessagesOnLines('DisallowNonRestActions.inc', [
            65 => [
                'Controller action "processPayment()" is not a canonical REST action; move it to a service, '
                . 'or mark it @non-rest-action (a deliberate route action) or @utility (not an action).',
            ],
        ]);
    }

    /**
     * A method is judged against its innermost enclosing class, so an action on
     * a controller nested inside a non-controller wrapper is still flagged.
     *
     * @return void
     */
    public function testUsesTheInnermostEnclosingClass(): void
    {
        $this->assertErrorsOnLines('DisallowNonRestActionsNested.inc', [9]);
    }
}
