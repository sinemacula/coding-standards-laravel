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
}
