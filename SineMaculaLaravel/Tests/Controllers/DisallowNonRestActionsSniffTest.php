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
     * Flags a public non-REST action and none of the exempt methods.
     *
     * @return void
     */
    public function testFlagsOnlyGenuineNonRestActions(): void
    {
        $this->assertErrorsOnLines('DisallowNonRestActions.inc', [65]);
    }

    /**
     * Renders the error naming the method and both escape hatches.
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
     * Judges a method against its innermost enclosing class.
     *
     * @return void
     */
    public function testUsesTheInnermostEnclosingClass(): void
    {
        $this->assertErrorsOnLines('DisallowNonRestActionsNested.inc', [9]);
    }
}
