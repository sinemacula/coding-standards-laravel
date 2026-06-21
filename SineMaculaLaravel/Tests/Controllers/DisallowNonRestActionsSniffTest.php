<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\Controllers;

use PHPUnit\Framework\Attributes\CoversClass;
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
final class DisallowNonRestActionsSniffTest extends AbstractSniffTestCase
{
    /**
     * Non-canonical public actions are flagged on a *Controller; REST actions,
     * __invoke, __construct, non-public helpers and non-controllers are not.
     *
     * @return void
     */
    public function testFlagsNonRestControllerActions(): void
    {
        $this->assertErrorsOnLines('DisallowNonRestActions.inc', [19]);
    }
}
