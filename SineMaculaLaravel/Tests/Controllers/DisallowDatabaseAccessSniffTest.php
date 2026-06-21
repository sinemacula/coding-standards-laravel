<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\Controllers;

use PHPUnit\Framework\Attributes\CoversNothing;
use SineMaculaLaravel\Tests\AbstractSniffTestCase;

/**
 * Tests for the controller database-access sniff.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @internal
 */
#[CoversNothing]
final class DisallowDatabaseAccessSniffTest extends AbstractSniffTestCase
{
    /**
     * DB:: facade calls and static calls on imported models are flagged in a
     * controller; other facades, variable/instance calls, constants and
     * non-controllers are not.
     *
     * @return void
     */
    public function testFlagsDatabaseAccessInControllers(): void
    {
        $this->assertErrorsOnLines('DisallowDatabaseAccess.inc', [13, 14, 15]);
    }
}
