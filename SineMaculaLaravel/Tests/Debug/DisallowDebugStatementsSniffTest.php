<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\Debug;

use PHPUnit\Framework\Attributes\CoversClass;
use SineMaculaLaravel\Sniffs\Debug\DisallowDebugStatementsSniff;
use SineMaculaLaravel\Tests\AbstractSniffTestCase;

/**
 * Tests for the debug statements sniff.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @internal
 */
#[CoversClass(DisallowDebugStatementsSniff::class)]
final class DisallowDebugStatementsSniffTest extends AbstractSniffTestCase
{
    /**
     * Debug helper calls are flagged; method/static calls and declarations of
     * the same name are not.
     *
     * @return void
     */
    public function testFlagsDebugStatements(): void
    {
        $this->assertErrorsOnLines('DisallowDebugStatements.inc', [11, 12, 13, 14, 15]);
    }
}
